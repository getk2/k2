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

class K2ModelCategory extends K2Model
{

    function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Category', 'Table');
        $row->load($cid);
		
		//JAW modified - for multiple extended field groups
		$db = JFactory::getDBO();
		$query = "SELECT extraFieldsGroup FROM `#__k2_extra_fields_groups_xref` WHERE viewID=".(int)$cid." AND viewType='category'";
		$db->setQuery($query);
		$row->extraFieldsGroups = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();

        return $row;
    }

    function save()
    {
        $mainframe = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        require_once (JPATH_COMPONENT.DS.'lib'.DS.'class.upload.php');
        $row = JTable::getInstance('K2Category', 'Table');
        $params = JComponentHelper::getParams('com_k2');

        if (!$row->bind(JRequest::get('post')))
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=categories');
        }

        $isNew = ($row->id) ? false : true;

        //Trigger the finder before save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderBeforeSave', array('com_k2.category', $row, $isNew));

        $row->description = JRequest::getVar('description', '', 'post', 'string', 2);
        if ($params->get('xssFiltering'))
        {
            $filter = new JFilterInput( array(), array(), 1, 1, 0);
            $row->description = $filter->clean($row->description);
        }

        if (!$row->id)
        {
            $row->ordering = $row->getNextOrder('parent = '.$row->parent.' AND trash=0');
        }

        if (!$row->check())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=category&cid='.$row->id);
        }

        if (!$row->store())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=categories');
        }

        if (!$params->get('disableCompactOrdering'))
            $row->reorder('parent = '.$row->parent.' AND trash=0');

        if ((int)$params->get('imageMemoryLimit'))
        {
            ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
        }

        $files = JRequest::get('files');

        $savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'categories'.DS;

        $existingImage = JRequest::getVar('existingImage');
        if (($files['image']['error'] === 0 || $existingImage) && !JRequest::getBool('del_image'))
        {
            if ($files['image']['error'] === 0)
            {
                $image = $files['image'];
            }
            else
            {
                $image = JPATH_SITE.DS.JPath::clean($existingImage);
            }

            $handle = new Upload($image);
            if ($handle->uploaded)
            {
                $handle->file_auto_rename = false;
                $handle->jpeg_quality = $params->get('imagesQuality', '85');
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $row->id;
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_x = $params->get('catImageWidth', '100');
                $handle->Process($savepath);
                if ($files['image']['error'] === 0)
                    $handle->Clean();
            }
            else
            {
            	$mainframe->enqueueMessage($handle->error, 'error');
                $mainframe->redirect('index.php?option=com_k2&view=categories');
            }
            $row->image = $handle->file_dst_name;
        }

        if (JRequest::getBool('del_image'))
        {
            $currentRow = JTable::getInstance('K2Category', 'Table');
            $currentRow->load($row->id);
            if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'categories'.DS.$currentRow->image))
            {
                JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'categories'.DS.$currentRow->image);
            }
            $row->image = '';
        }

        if (!$row->store())
        {
        	$mainframe->enqueueMessage($row->getError(), 'error');
            $mainframe->redirect('index.php?option=com_k2&view=categories');
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
					$insert[] = '('.$id.','.(int)$row->id.',"category",'.$extraFieldsGroupsId.')';
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

        //Trigger the finder after save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.category', $row, $isNew));

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch(JRequest::getCmd('task'))
        {
            case 'apply' :
                $msg = JText::_('K2_CHANGES_TO_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=category&cid='.$row->id;
                break;
            case 'saveAndNew' :
                $msg = JText::_('K2_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=category';
                break;
            case 'save' :
            default :
                $msg = JText::_('K2_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=categories';
                break;
        }
		$mainframe->enqueueMessage($msg);
        $mainframe->redirect($link);
    }

    function countCategoryItems($catid, $trash = 0)
    {

        $db = JFactory::getDBO();
        $catid = (int)$catid;
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid={$catid} AND trash = ".(int)$trash;
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;

    }

}
