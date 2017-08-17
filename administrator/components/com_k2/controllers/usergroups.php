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

jimport('joomla.application.component.controller');

class K2ControllerUserGroups extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'usergroups');
        parent::display();
    }

    function edit()
    {
        $application = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $application->redirect('index.php?option=com_k2&view=usergroup&cid='.$cid[0]);
    }

    function add()
    {
        $application = JFactory::getApplication();
        $application->redirect('index.php?option=com_k2&view=usergroup');
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('userGroups');
        $model->remove();
    }

}
