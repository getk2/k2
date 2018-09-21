<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelTags extends K2Model
{
	function getData()
	{
		$application = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDbo();
		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'id', 'cmd');
		$filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));

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
		$application = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDbo();
		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));

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
		$application = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->published = 1;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=tags&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=tags');
		}
	}

	function unpublish()
	{
		$application = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->published = 0;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=tags&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=tags');
		}
	}

	function remove()
	{
		$application = JFactory::getApplication();
		$db = JFactory::getDbo();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Tag', 'Table');
			$row->load($id);
			$row->delete($id);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		$application->redirect('index.php?option=com_k2&view=tags');
	}

	function getFilter()
	{
		$db = JFactory::getDbo();
		$query = "SELECT name, id FROM #__k2_tags ORDER BY name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function countTagItems($id)
	{
		$db = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM #__k2_tags_xref WHERE tagID = ".(int)$id;
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	function removeOrphans()
	{
		$db = JFactory::getDbo();
		$db->setQuery("DELETE FROM #__k2_tags WHERE id NOT IN (SELECT tagID FROM #__k2_tags_xref GROUP BY tagID)");
		$db->query();
		$application = JFactory::getApplication();
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		$application->redirect('index.php?option=com_k2&view=tags');
	}
}
