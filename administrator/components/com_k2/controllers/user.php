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

class K2ControllerUser extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'user');
        parent::display();
    }

    function save()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('user');
        $model->save();
    }

    function apply()
    {
        $this->save();
    }

    function cancel()
    {
        $application = JFactory::getApplication();
        $application->redirect('index.php?option=com_k2&view=users');
    }

    function report()
    {
	    $app = JFactory::getApplication();
        $model = K2Model::getInstance('User', 'K2Model');
        $model->setState('id', JRequest::getInt('id'));
        $model->reportSpammer();
		if(JRequest::getCmd('context') == "modalselector"){
			$app->redirect('index.php?option=com_k2&view=users&tmpl=component&context=modalselector');

		} else {
			//$this->setRedirect('index.php?option=com_k2&view=users');
			$app->redirect('index.php?option=com_k2&view=users');
		}
    }
}
