<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
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
        $app = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        require_once(JPATH_SITE.'/media/k2/assets/vendors/verot/class.upload.php/src/class.upload.php');
        $row = JTable::getInstance('K2Category', 'Table');
        $params = JComponentHelper::getParams('com_k2');

        // Plugin Events
        JPluginHelper::importPlugin('k2');
        JPluginHelper::importPlugin('content');
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();

        if (!$row->bind(JRequest::get('post'))) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=categories');
        }

        $isNew = ($row->id) ? false : true;

        // Trigger K2 plugins
        $result = $dispatcher->trigger('onBeforeK2Save', array(&$row, $isNew));

        if (in_array(false, $result, true)) {
            JError::raiseError(500, $row->getError());
            return false;
        }

        // Trigger content & finder plugins before the save event
        $dispatcher->trigger('onContentBeforeSave', array('com_k2.category', $row, $isNew));
        $dispatcher->trigger('onFinderBeforeSave', array('com_k2.category', $row, $isNew));

        $row->description = JRequest::getVar('description', '', 'post', 'string', 2);
        if ($params->get('xssFiltering')) {
            $filter = new JFilterInput(array(), array(), 1, 1, 0);
            $row->description = $filter->clean($row->description);
        }

        if (!$row->id) {
            $row->ordering = $row->getNextOrder('parent = '.(int)$row->parent.' AND trash=0');
        }

        if (!$row->check()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=category&cid='.$row->id);
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=categories');
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
                $app->enqueueMessage($handle->error, 'error');
                $app->redirect('index.php?option=com_k2&view=categories');
            }
            $row->image = $handle->file_dst_name;
        }

        if (JRequest::getBool('del_image')) {
            $savedRow = JTable::getInstance('K2Category', 'Table');
            $savedRow->load($row->id);
            if (JFile::exists(JPATH_ROOT.'/media/k2/categories/'.$savedRow->image)) {
                JFile::delete(JPATH_ROOT.'/media/k2/categories/'.$savedRow->image);
            }
            $row->image = '';
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=categories');
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        // Trigger K2 plugins
        $dispatcher->trigger('onAfterK2Save', array(&$row, $isNew));

        // Trigger content & finder plugins after the save event
        if (K2_JVERSION != '15') {
            $dispatcher->trigger('onContentAfterSave', array('com_k2.category', &$row, $isNew));
        } else {
            $dispatcher->trigger('onAfterContentSave', array(&$row, $isNew));
        }
        $results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.category', $row, $isNew));

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
        $app->enqueueMessage($msg);
        $app->redirect($link);
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
