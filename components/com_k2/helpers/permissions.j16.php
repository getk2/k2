<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.html.parameter');

class K2HelperPermissions
{

    public static function checkPermissions()
    {
        // Set some variables
        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $task = JRequest::getCmd('task');
        $id = ($task == 'apply' || $task == 'save') ? JRequest::getInt('id') : JRequest::getVar('cid');

        //Generic manage check
        if (!$user->authorise('core.manage', $option))
        {
            JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
            $application->redirect('index.php');
        }

        // Determine action for rest checks
        $action = false;
        if ($application->isAdmin() && $view != '' && $view != 'info')
        {
            switch($task)
            {
                case '' :
                case 'save' :
                case 'apply' :
                    if (!$id)
                    {
                        $action = 'core.create';
                    }
                    else
                    {
                        $action = 'core.edit';
                    }
                    break;
                case 'trash' :
                case 'remove' :
                    $action = 'core.delete';
                    break;
                case 'publish' :
                case 'unpublish' :
				case 'featured':
                    $action = 'core.edit.state';
            }

            // Edit or Edit own action
            if ($action == 'core.edit' && $view == 'item' && $id)
            {
                JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
                $item = JTable::getInstance('K2Item', 'Table');
                $item->load($id);
                if ($item->created_by == $user->id)
                {
                    $action = 'core.edit.own';
                }
            }

            // Check the determined action
            if ($action)
            {
                if (!$user->authorise($action, $option))
                {
                    JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
                    $application->redirect('index.php?option=com_k2');
                }
            }

        }
    }

}
