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

class K2ModelCategory extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Category', 'Table');
        $row->load($cid);
        return $row;
    }

    public function save()
    {
        $application = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        require_once(JPATH_SITE.'/media/k2/assets/vendors/verot/class.upload.php/src/class.upload.php');
        $row = JTable::getInstance('K2Category', 'Table');
        $params = JComponentHelper::getParams('com_k2');

        if (!$row->bind(JRequest::get('post'))) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=categories');
        }

        $isNew = ($row->id) ? false : true;

        // Trigger the finder before save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderBeforeSave', array('com_k2.category', $row, $isNew));

        $row->description = JRequest::getVar('description', '', 'post', 'string', 2);
        if ($params->get('xssFiltering')) {
            $filter = new JFilterInput(array(), array(), 1, 1, 0);
            $row->description = $filter->clean($row->description);
        }

        if (!$row->id) {
            $row->ordering = $row->getNextOrder('parent = '.(int)$row->parent.' AND trash=0');
        }

        if (!$row->check()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=category&cid='.$row->id);
        }

        if (!$row->store()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=categories');
        }

        if (!$params->get('disableCompactOrdering')) {
            $row->reorder('parent = '.(int)$row->parent.' AND trash=0');
        }

        if ((int)$params->get('imageMemoryLimit')) {
            ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
        }

        $files = JRequest::get('files');

        $savepath = JPATH_ROOT.'/media/k2/categories/';

        $existingImage = JRequest::getVar('existingImage');
        if (($files['image']['error'] == 0 || $existingImage) && !JRequest::getBool('del_image')) {
            if ($files['image']['error'] == 0) {
                $image = $files['image'];
            } else {
                $image = JPATH_SITE.'/'.JPath::clean($existingImage);
            }

            $handle = new Upload($image);
            if ($handle->uploaded) {
                $handle->file_auto_rename = false;
                $handle->jpeg_quality = $params->get('imagesQuality', '85');
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $row->id;
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_x = $params->get('catImageWidth', '100');
                $handle->Process($savepath);
                if ($files['image']['error'] == 0) {
                    $handle->Clean();
                }
            } else {
                $application->enqueueMessage($handle->error, 'error');
                $application->redirect('index.php?option=com_k2&view=categories');
            }
            $row->image = $handle->file_dst_name;
        }

        if (JRequest::getBool('del_image')) {
            $currentRow = JTable::getInstance('K2Category', 'Table');
            $currentRow->load($row->id);
            if (JFile::exists(JPATH_ROOT.'/media/k2/categories/'.$currentRow->image)) {
                JFile::delete(JPATH_ROOT.'/media/k2/categories/'.$currentRow->image);
            }
            $row->image = '';
        }

        if (!$row->store()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=categories');
        }

        // Trigger the finder after save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.category', $row, $isNew));

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=category&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=category';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_CATEGORY_SAVED');
                $link = 'index.php?option=com_k2&view=categories';
                break;
        }
        $application->enqueueMessage($msg);
        $application->redirect($link);
    }

    public function countCategoryItems($catid, $trash = 0)
    {
        $db = JFactory::getDbo();
        $catid = (int)$catid;
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid={$catid} AND trash = ".(int)$trash;
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }
}
