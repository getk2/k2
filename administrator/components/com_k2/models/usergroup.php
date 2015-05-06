<?php
/**
 * @version		2.7.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelUserGroup extends K2Model
{

    function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2UserGroup', 'Table');
        $row->load($cid);
		
		//JAW added - for multiple extended field groups
		$db = JFactory::getDBO();
		$query = "SELECT extraFieldsGroup FROM `#__k2_extra_fields_groups_xref` WHERE viewID=".(int)$cid." AND viewType='user_group'";
		$db->setQuery($query);
		$row->extraFieldsGroups = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();			
		
        return $row;
    }

    function save()
    {
        $mainframe = JFactory::getApplication();
        $row = JTable::getInstance('K2UserGroup', 'Table');

        if (!$row->bind(JRequest::get('post')))
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=usergroups');
        }

        if (!$row->check())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=usergroup&cid='.$row->id);
        }

        if (!$row->store())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=usergroups');
        }

		// JAW modified - allow multiple extra field groups
		$extraFieldsGroupsIds = JRequest::getVar('extraFieldsGroups', array(0), 'post', 'array');


		//JAW modified - save usergroup to multiple extrafields groups
		if ($row->id)
		{
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__k2_extra_fields_groups_xref WHERE viewID={$row->id}";
			$db->setQuery($query);
			$extraFieldsGroups = $db->loadObjectList();
			$filters = array('viewID='.$row->id);
			if (count($extraFieldsGroups))
			{
				$ids = array();
				foreach ($extraFieldsGroupsIds as $extraFieldsGroupId)
				{
					$ids[] = $extraFieldsGroupId;
				}
				if (!empty($ids))
				{
					$filters[] = 'extraFieldsGroup NOT IN ('.implode(',', $ids).')';
				}
			}
			$query = 'DELETE FROM #__k2_extra_fields_groups_xref WHERE '.implode(' AND ', $filters);
			$db->setQuery($query);
			$db->query();
		
			if (count($extraFieldsGroupsIds))
			{
				$i = 0;
				$insert = array();
				foreach ($extraFieldsGroupsIds as $extraFieldsGroupsId)
				{	
					if ($extraFieldsGroupsId === 0)
					{
						continue;
					}
					elseif ($extraFieldsGroupsId != $extraFieldsGroups[$i]->extraFieldsGroup)
					{
						$id = 'NULL';
					}
					else
					{
						$id = $extraFieldsGroups[$i]->id;
					}
					$insert[] = '('.$id.','.(int)$row->id.',"user_group",'.$extraFieldsGroupsId.')';
					$i++;
				}			
				if (!empty($insert))
				{
					$select = 'id,viewID,viewType,extraFieldsGroup';
					$query = 'REPLACE #__k2_extra_fields_groups_xref ('.$select.') VALUES '.implode(',', $insert).';';
					$db->setQuery($query);
					$db->query();
				}
			}
		}
		
		$cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch(JRequest::getCmd('task'))
        {
            case 'apply' :
                $msg = JText::_('K2_CHANGES_TO_USER_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=usergroup&cid='.$row->id;
                break;
            case 'save' :
            default :
                $msg = JText::_('K2_USER_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=usergroups';
                break;
        }
		$mainframe->enqueueMessage($msg);
        $mainframe->redirect($link);
    }

}
