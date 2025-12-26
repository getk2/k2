<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

class K2ModelTags extends K2Model
{
    public function getData()
    {
        $app              = JFactory::getApplication();
        $option           = JRequest::getCmd('option');
        $view             = JRequest::getCmd('view');
        $db               = JFactory::getDbo();
        $limit            = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart       = $app->getUserStateFromRequest($option . $view . '.limitstart', 'limitstart', 0, 'int');
        $filter_order     = $app->getUserStateFromRequest($option . $view . 'filter_order', 'filter_order', 'id', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option . $view . 'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
        $filter_state     = $app->getUserStateFromRequest($option . $view . 'filter_state', 'filter_state', -1, 'int');
        $search           = $app->getUserStateFromRequest($option . $view . 'search', 'search', '', 'string');
        $search           = JString::strtolower($search);
        $search           = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));

        $query = "SELECT #__k2_tags.*, (SELECT COUNT(*) FROM #__k2_tags_xref WHERE #__k2_tags_xref.tagID = #__k2_tags.id) AS numOfItems FROM #__k2_tags WHERE 1=1";

        if ($filter_state > -1) {
            $query .= " AND published={$filter_state}";
        }

        if ($search) {
            $query .= K2GlobalHelper::search($search, [
                'name',
            ]);
        }

        if (! $filter_order) {
            $filter_order = "name";
        }

        $query .= " ORDER BY {$filter_order} {$filter_order_Dir}";

        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getTotal()
    {
        $app        = JFactory::getApplication();
        $option     = JRequest::getCmd('option');
        $view       = JRequest::getCmd('view');
        $db         = JFactory::getDbo();
        $limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        $filter_state = $app->getUserStateFromRequest($option . $view . 'filter_state', 'filter_state', 1, 'int');

        $search = $app->getUserStateFromRequest($option . $view . 'search', 'search', '', 'string');

        $query = "SELECT COUNT(*) FROM #__k2_tags WHERE id > 0";

        if ($filter_state > -1) {
            $query .= " AND published={$filter_state}";
        }

        if ($search) {
            $query .= K2GlobalHelper::search($search, [
                'name',
            ]);
        }

        $db->setQuery($query);
        $total = $db->loadresult();
        return $total;
    }

    public function publish()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Tag', 'Table');
            $row->load($id);
            $row->published = 1;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=tags&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=tags');
        }
    }

    public function unpublish()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Tag', 'Table');
            $row->load($id);
            $row->published = 0;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=tags&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=tags');
        }
    }

    public function remove()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Tag', 'Table');
            $row->load($id);
            $row->delete($id);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $app->redirect('index.php?option=com_k2&view=tags');
    }

    public function getFilter()
    {
        $db    = JFactory::getDbo();
        $query = "SELECT name, id FROM #__k2_tags ORDER BY name";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function countTagItems($id)
    {
        $db    = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_tags_xref WHERE tagID = " . (int) $id;
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public function removeOrphans()
    {
        $db = JFactory::getDbo();
        $db->setQuery("DELETE FROM #__k2_tags WHERE id NOT IN (SELECT tagID FROM #__k2_tags_xref GROUP BY tagID)");
        $db->query();
        $app = JFactory::getApplication();
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $app->redirect('index.php?option=com_k2&view=tags');
    }
}
