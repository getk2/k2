<?php
/**
 * @version		$Id: usergroup.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
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
        return $row;
    }

    function save()
    {
        $mainframe = JFactory::getApplication();
        $row = JTable::getInstance('K2UserGroup', 'Table');

        if (!$row->bind(JRequest::get('post')))
        {
            $mainframe->redirect('index.php?option=com_k2&view=usergroups', $row->getError(), 'error');
        }

        if (!$row->check())
        {
            $mainframe->redirect('index.php?option=com_k2&view=usergroup&cid='.$row->id, $row->getError(), 'error');
        }

        if (!$row->store())
        {
            $mainframe->redirect('index.php?option=com_k2&view=usergroups', $row->getError(), 'error');
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
        $mainframe->redirect($link, $msg);
    }

}
