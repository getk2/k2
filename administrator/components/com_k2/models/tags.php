<?php
/**
 * @version		$Id: tags.php 1947 2013-03-11 11:46:13Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelTags extends K2Model
{

	function getData()
	{

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDBO();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);

		$query = "SELECT #__k2_tags.*, (SELECT COUNT(*) FROM #__k2_tags_xref WHERE #__k2_tags_xref.tagID = #__k2_tags.id) AS numOfItems FROM #__k2_tags";

		$conditions = array();

		if ($filter_state > -1)
		{
			$conditions[] = "published={$filter_state}";
		}
		if ($search)
		{
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$conditions[] = "LOWER( name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
		}

		if (count($conditions))
		{
			$query .= " WHERE ".implode(' AND ', $conditions);
		}

		if (!$filter_order)
		{
			$filter_order = "name";
		}

		$query .= " ORDER BY {$filter_order} {$filter_order_Dir}";

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

		$query = "SELECT COUNT(*) FROM #__k2_tags WHERE id>0";

		if ($filter_state > -1)
		{
			$query .= " AND published={$filter_state}";
		}

		if ($search)
		{
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$query .= " AND LOWER( name ) LIKE ".$db->Quote('%'.$escaped.'%', false);
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
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->publish($id, 1);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=tags');
	}

	function unpublish()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->publish($id, 0);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=tags');
	}

	function remove()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->delete($id);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=tags', JText::_('K2_DELETE_COMPLETED'));
	}

	function getFilter()
	{

		$db = JFactory::getDBO();
		$query = "SELECT name, id FROM #__k2_tags ORDER BY name";
		$db->setQuery($query, 0, 1000);
		$rows = $db->loadObjectList();
		return $rows;

	}

	function countTagItems($id)
	{
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__k2_tags_xref WHERE tagID = ".(int)$id;
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	function removeOrphans()
	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM #__k2_tags WHERE id NOT IN (SELECT DISTINCT tagID FROM #__k2_tags_xref)");
		$db->query();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_k2&view=tags', JText::_('K2_DELETE_COMPLETED'));
	}

}
