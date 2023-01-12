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

class K2ModelUserGroup extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2UserGroup', 'Table');
        $row->load($cid);
        return $row;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        $row = JTable::getInstance('K2UserGroup', 'Table');

        if (!$row->bind(JRequest::get('post'))) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=usergroups');
        }

        if (!$row->check()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=usergroup&cid='.$row->id);
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=usergroups');
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_USER_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=usergroup&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_USER_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=usergroup';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_USER_GROUP_SAVED');
                $link = 'index.php?option=com_k2&view=usergroups';
                break;
        }
        $app->enqueueMessage($msg);
        $app->redirect($link);
    }
}
