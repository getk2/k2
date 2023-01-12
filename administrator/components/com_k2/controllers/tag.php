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

class K2ControllerTag extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'tag');
        parent::display();
    }

    public function apply()
    {
        $this->save();
    }

    public function save()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('tag');
        $model->save();
    }

    public function saveAndNew()
    {
        $this->save();
    }

    public function cancel()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=tags');
    }
}
