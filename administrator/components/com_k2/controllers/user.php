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

jimport('joomla.application.component.controller');

class K2ControllerUser extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'user');
        parent::display();
    }

    public function save()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('user');
        $model->save();
    }

    public function apply()
    {
        $this->save();
    }

    public function cancel()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=users');
    }

    public function report()
    {
        $app = JFactory::getApplication();
        $model = K2Model::getInstance('User', 'K2Model');
        $model->setState('id', JRequest::getInt('id'));
        $model->reportSpammer();
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=users&tmpl=component&template=system&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=users');
        }
    }
}
