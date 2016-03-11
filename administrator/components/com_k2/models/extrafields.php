<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelExtraFields extends K2Model
{

    function getData()
    {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDBO();
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'groupname', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
        $filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
        $search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $filter_type = $mainframe->getUserStateFromRequest($option.$view.'filter_type', 'filter_type', '', 'string');
        $filter_group = $mainframe->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', 0, 'int');

        $query = "SELECT exf.*, exfg.name as groupname FROM #__k2_extra_fields AS exf LEFT JOIN #__k2_extra_fields_groups exfg ON exf.group=exfg.id  WHERE exf.id>0";

        if ($filter_state > -1)
        {
            $query .= " AND published={$filter_state}";
        }

        if ($search)
        {
            $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
            $query .= " AND LOWER( exf.name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
        }

        if ($filter_type)
        {
            $query .= " AND `type`=".$db->Quote($filter_type);
        }

        if ($filter_group)
        {
            $query .= " AND `group`={$filter_group}";
        }

        if (!$filter_order)
        {
            $filter_order = '`group`';
        }

        if ($filter_order == 'ordering')
        {
            $query .= " ORDER BY `group`, ordering {$filter_order_Dir}";
        }
        else
        {
            $query .= " ORDER BY {$filter_order} {$filter_order_Dir}, `group`, ordering";
        }

        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        return $rows;
    }

    function getTotal()
    {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDBO();
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
        $filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
        $search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $filter_type = $mainframe->getUserStateFromRequest($option.$view.'filter_type', 'filter_type', '', 'string');
        $filter_group = $mainframe->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', '', 'string');

        $query = "SELECT COUNT(*) FROM #__k2_extra_fields WHERE id>0";

        if ($filter_state > -1)
        {
            $query .= " AND published={$filter_state}";
        }

        if ($search)
        {
            $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
            $query .= " AND LOWER( name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
        }

        if ($filter_type)
        {
            $query .= " AND `type`=".$db->Quote($filter_type);
        }

        if ($filter_group)
        {
            $query .= " AND `group`=".$db->Quote($filter_group);
        }

        $db->setQuery($query);
        $total = $db->loadresult();
        return $total;
    }

    function publish()
    {

        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->published = 1;
			$row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $mainframe->redirect('index.php?option=com_k2&view=extrafields');
    }

    function unpublish()
    {

        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->published = 0;
			$row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $mainframe->redirect('index.php?option=com_k2&view=extrafields');
    }

    function saveorder()
    {

        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');
        $total = count($cid);
        $order = JRequest::getVar('order', array(0), 'post', 'array');
        JArrayHelper::toInteger($order, array(0));
        $groupings = array();
        for ($i = 0; $i < $total; $i++)
        {
        	$row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load((int)$cid[$i]);
            $groupings[] = $row->group;
            if ($row->ordering != $order[$i])
            {
                $row->ordering = $order[$i];
                if (!$row->store())
                {
                    JError::raiseError(500, $db->getErrorMsg());
                }
            }
        }
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering'))
        {
            $groupings = array_unique($groupings);
            foreach ($groupings as $group)
            {
            	$row = JTable::getInstance('K2ExtraField', 'Table');
                $row->reorder("`group` = ".(int)$group);
            }
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        return true;
    }

    function orderup()
    {

        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($cid[0]);
        $row->move(-1, "`group` = '{$row->group}'");
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering'))
            $row->reorder("`group` = ".(int)$row->group);
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
        $mainframe->redirect('index.php?option=com_k2&view=extrafields');
    }

    function orderdown()
    {

        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($cid[0]);
        $row->move(1, "`group` = '{$row->group}'");
        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering'))
            $row->reorder("`group` = ".(int)$row->group);
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
        $mainframe->redirect('index.php?option=com_k2&view=extrafields');
    }

    function remove()
    {

        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2ExtraField', 'Table');
            $row->load($id);
            $row->delete($id);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $mainframe->redirect('index.php?option=com_k2&view=extrafields');
    }

    function getExtraFieldsGroup()
    {

        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
        $row->load($cid);
        return $row;
    }

    function getGroups($filter = false)
    {

        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_extra_fields_groups ORDER BY `name`";
        if ($filter)
        {
            $db->setQuery($query);
        }
        else
        {
            $db->setQuery($query, $limitstart, $limit);
        }

        $rows = $db->loadObjectList();
        for ($i = 0; $i < sizeof($rows); $i++)
        {
            $query = "SELECT name FROM #__k2_categories WHERE extraFieldsGroup=".(int)$rows[$i]->id;
            $db->setQuery($query);
            $categories = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
            if (is_array($categories))
            {
                $rows[$i]->categories = implode(', ', $categories);
            }
            else
            {
                $rows[$i]->categories = '';
            }

        }
        return $rows;
    }

    function getTotalGroups()
    {

        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) FROM #__k2_extra_fields_groups";
        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }

    function saveGroup()
    {

        $mainframe = JFactory::getApplication();
        $id = JRequest::getInt('id');
        $row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
        if (!$row->bind(JRequest::get('post')))
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=extrafieldsgroups');
        }

        if (!$row->check())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=extrafieldsgroup&cid='.$row->id);
        }

        if (!$row->store())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=extrafieldsgroup');
        }

        switch(JRequest::getCmd('task'))
        {
            case 'apply' :
                $msg = JText::_('K2_CHANGES_TO_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=extrafieldsgroup&cid='.$row->id;
                break;
            case 'save' :
            default :
                $msg = JText::_('K2_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=extrafieldsgroups';
                break;
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		$mainframe->enqueueMessage($msg);
        $mainframe->redirect($link);
    }

    function removeGroups()
    {

        $mainframe = JFactory::getApplication();
        $db = &JFactory::getDBO();
        $cid = JRequest::getVar('cid');
        JArrayHelper::toInteger($cid);
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
            $row->load($id);
            $query = "DELETE FROM #__k2_extra_fields WHERE `group`={$id}";
            $db->setQuery($query);
            $db->query();
            $row->delete($id);
        }
        $cache = &JFactory::getCache('com_k2');
        $cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $mainframe->redirect('index.php?option=com_k2&view=extrafieldsgroups');
    }

}
