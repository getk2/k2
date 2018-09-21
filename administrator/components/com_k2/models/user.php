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

class K2ModelUser extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getInt('cid');
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_users WHERE userID = ".$cid;
        $db->setQuery($query);
        $row = $db->loadObject();
        if (!$row) {
            $row = JTable::getInstance('K2User', 'Table');
        }
        return $row;
    }

    public function save()
    {
        $application = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        require_once(JPATH_SITE.'/media/k2/assets/vendors/verot/class.upload.php/src/class.upload.php');
        $row = JTable::getInstance('K2User', 'Table');
        $params = JComponentHelper::getParams('com_k2');

        if (!$row->bind(JRequest::get('post'))) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=users');
        }

        $row->description = JRequest::getVar('description', '', 'post', 'string', 2);
        if ($params->get('xssFiltering')) {
            $filter = new JFilterInput(array(), array(), 1, 1, 0);
            $row->description = $filter->clean($row->description);
        }
        $jUser = JFactory::getUser($row->userID);
        $row->userName = $jUser->name;

        if (!$row->store()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=users');
        }

        // Image
        if ((int)$params->get('imageMemoryLimit')) {
            ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
        }

        $file = JRequest::get('files');

        $savepath = JPATH_ROOT.'/media/k2/users/';

        if ($file['image']['error'] == 0 && !JRequest::getBool('del_image')) {
            $handle = new Upload($file['image']);
            if ($handle->uploaded) {
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $row->id;
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_x = $params->get('userImageWidth', '100');
                $handle->Process($savepath);
                $handle->Clean();
            } else {
                $application->enqueueMessage($handle->error, 'error');
                $application->redirect('index.php?option=com_k2&view=users');
            }
            $row->image = $handle->file_dst_name;
        }

        if (JRequest::getBool('del_image')) {
            $current = JTable::getInstance('K2User', 'Table');
            $current->load($row->id);
            if (JFile::exists(JPATH_ROOT.'/media/k2/users/'.$current->image)) {
                JFile::delete(JPATH_ROOT.'/media/k2/users/'.$current->image);
            }
            $row->image = '';
        }

        if (!$row->check()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=user&cid='.$row->id);
        }

        if (!$row->store()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=users');
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_USER_SAVED');
                $link = 'index.php?option=com_k2&view=user&cid='.$row->userID;
                break;
            case 'save':
            default:
                $msg = JText::_('K2_USER_SAVED');
                $link = 'index.php?option=com_k2&view=users';
                break;
        }
        $application->enqueueMessage($msg);
        $application->redirect($link);
    }

    public function getUserGroups()
    {
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_user_groups";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function reportSpammer()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $id = (int)$this->getState('id');
        if (!$id) {
            return false;
        }
        $user = JFactory::getUser();
        if ($user->id == $id) {
            $application->enqueueMessage(JText::_('K2_YOU_CANNOT_REPORT_YOURSELF'), 'error');
            return false;
        }
        $db = JFactory::getDbo();

        // Unpublish user comments
        $db->setQuery("UPDATE #__k2_comments SET published = 0 WHERE userID = ".$id);
        $db->query();
        $application->enqueueMessage(JText::_('K2_USER_COMMENTS_UNPUBLISHED'));

        // Unpublish user items
        $db->setQuery("UPDATE #__k2_items SET published = 0 WHERE created_by = ".$id);
        $db->query();
        $application->enqueueMessage(JText::_('K2_USER_ITEMS_UNPUBLISHED'));

        // Report the user to stopforumspam.com
        // We need the IP for this, so the user has to be a registered K2 user
        $spammer = JFactory::getUser($id);
        $db->setQuery("SELECT ip FROM #__k2_users WHERE userID=".$id, 0, 1);
        $ip = $db->loadResult();
        $stopForumSpamApiKey = trim($params->get('stopForumSpamApiKey'));
        if ($ip && function_exists('fsockopen') && $stopForumSpamApiKey) {
            $data = "username=".$spammer->username."&ip_addr=".$ip."&email=".$spammer->email."&api_key=".$stopForumSpamApiKey;
            $fp = fsockopen("www.stopforumspam.com", 80);
            fputs($fp, "POST /add.php HTTP/1.1\n");
            fputs($fp, "Host: www.stopforumspam.com\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
            fputs($fp, "Content-length: ".strlen($data)."\n");
            fputs($fp, "Connection: close\n\n");
            fputs($fp, $data);
            fclose($fp);
            $application->enqueueMessage(JText::_('K2_USER_DATA_SUBMITTED_TO_STOPFORUMSPAM'));
        }

        // Finally block the user
        $db->setQuery("UPDATE #__users SET block = 1 WHERE id=".$id);
        $db->query();
        $application->enqueueMessage(JText::_('K2_USER_BLOCKED'));
        return true;
    }
}
