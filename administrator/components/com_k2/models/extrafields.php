<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2020 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelExtraFields extends K2Model
{
    public function getData()
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'groupname', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
        $filter_state = $app->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
        $search = $app->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));
        $filter_type = $app->getUserStateFromRequest($option.$view.'filter_type', 'filter_type', '', 'string');
        $filter_group = $app->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', 0, 'int');

        $query = "SELECT exf.*, exfg.name as groupname FROM #__k2_extra_fields AS exf LEFT JOIN #__k2_extra_fields_groups exfg ON exf.group=exfg.id  WHERE exf.id>0";

        if ($filter_state > -1) {
            $query .= " AND published={$filter_state}";
        }

        if ($search) {
            $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
            $query .= " AND LOWER( exf.name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
        }

        if ($filter_type) {
            $query .= " AND `type`=".$db->Quote($filter_type);
        }

        if ($filter_group) {
            $query .= " AND `group`={$filter_group}";
        }

        if (!$filter_order) {
            $filter_order = '`group`';
        }

        if ($filter_order == 'ordering') {
            $query .= " ORDER BY `group`, ordering {$filter_order_Dir}";
        } else {
            $query .= " ORDER BY {$filter_order} {$filter_order_Dir}, `group`, ordering";
        }

        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getTotal()
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
        $filter_state = $app->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
        $search = $app->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));
        $filter_type = $app->getUserStateFromRequest($option.$view.'filter_type', 'filter_type', '', 'string');
        $filter_group = $app->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', '', 'string');

        $query = "SELECT COUNT(*) FROM #__k2_extra_fields WHERE id>0";

        if ($filter_state > -1) {
            $query .= " AND published={$filter_state}";
        }

        if ($search) {
            $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
            $query .= " AND LOWER( name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
        }

        if ($filter_type) {
            $query .= " AND `type`=".$db->Quote($filter_type);
        }

        if ($filter_group) {
            $query .= " AND `group`=".$db->Quote($filter_group);
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
            $row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->published = 1;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->redirect('index.php?option=com_k2&view=extrafields');
    }

    public function unpublish()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->published = 0;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->redirect('index.php?option=com_k2&view=extrafields');
    }

    public function saveorder()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');
        $total = count($cid);
        $order = JRequest::getVar('order', array(0), 'post', 'array');
        JArrayHelper::toInteger($order, array(0));
        $groupings = array();
        for ($i = 0; $i < $total; $i++) {
            $row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load((int)$cid[$i]);
            $groupings[] = $row->group;
            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    JError::raiseError(500, $db->getErrorMsg());
                }
            }
        }
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering')) {
            $groupings = array_unique($groupings);
            foreach ($groupings as $group) {
                $row = JTable::getInstance('K2ExtraField', 'Table');
                $row->reorder("`group` = ".(int)$group);
            }
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        return true;
    }

    public function orderup()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($cid[0]);
        $row->move(-1, "`group` = '{$row->group}'");
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering')) {
            $row->reorder("`group` = ".(int)$row->group);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
        $app->enqueueMessage($msg);
        $app->redirect('index.php?option=com_k2&view=extrafields');
    }

    public function orderdown()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($cid[0]);
        $row->move(1, "`group` = '{$row->group}'");
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering')) {
            $row->reorder("`group` = ".(int)$row->group);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
        $app->enqueueMessage($msg);
        $app->redirect('index.php?option=com_k2&view=extrafields');
    }

    public function remove()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->delete($id);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $app->redirect('index.php?option=com_k2&view=extrafields');
    }

    public function getExtraFieldsGroup()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
        $row->load($cid);
        return $row;
    }

    public function getGroups($filter = false)
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_extra_fields_groups ORDER BY `name`";
        if ($filter) {
            $db->setQuery($query);
        } else {
            $db->setQuery($query, $limitstart, $limit);
        }

        $rows = $db->loadObjectList();
        for ($i = 0; $i < count($rows); $i++) {
            $query = "SELECT name FROM #__k2_categories WHERE extraFieldsGroup = ".(int)$rows[$i]->id;
            $db->setQuery($query);
            $categories = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
            if (is_array($categories)) {
                $rows[$i]->categories = implode(', ', $categories);
            } else {
                $rows[$i]->categories = '';
            }
        }
        return $rows;
    }

    public function getTotalGroups()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_extra_fields_groups";
        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }

    public function saveGroup()
    {
        $app = JFactory::getApplication();
        $id = JRequest::getInt('id');
        $row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
        if (!$row->bind(JRequest::get('post'))) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=extrafieldsgroups');
        }

        if (!$row->check()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=extrafieldsgroup&cid='.$row->id);
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=extrafieldsgroup');
        }

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=extrafieldsgroup&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=extrafieldsgroup';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=extrafieldsgroups';
                break;
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->enqueueMessage($msg);
        $app->redirect($link);
    }

    public function removeGroups()
    {
        $app = JFactory::getApplication();
        $db = &JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        JArrayHelper::toInteger($cid);
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
            $row->load($id);
            $query = "DELETE FROM #__k2_extra_fields WHERE `group`={$id}";
            $db->setQuery($query);
            $db->query();
            $row->delete($id);
        }
        $cache = &JFactory::getCache('com_k2');
        $cache->clean();
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $app->redirect('index.php?option=com_k2&view=extrafieldsgroups');
    }
}
