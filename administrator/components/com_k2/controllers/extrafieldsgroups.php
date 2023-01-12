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

class K2ControllerExtraFieldsGroups extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'extrafieldsgroups');
        $model = $this->getModel('extraFields');
        $view = $this->getView('extrafieldsgroups', 'html');
        $view->setModel($model, true);
        parent::display();
    }

    public function add()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=extrafieldsgroup');
    }

    public function edit()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $app->redirect('index.php?option=com_k2&view=extrafieldsgroup&cid='.$cid[0]);
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->removeGroups();
    }
}
