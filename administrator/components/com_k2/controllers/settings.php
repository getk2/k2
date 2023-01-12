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

class K2ControllerSettings extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        if (K2_JVERSION != '15') {
            $app = JFactory::getApplication();
            $app->redirect('index.php?option=com_config&view=component&component=com_k2&path=&tmpl=component');
        } else {
            JRequest::setVar('tmpl', 'component');
            parent::display();
        }
    }

    public function save()
    {
        $app = JFactory::getApplication();
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('settings');
        $model->save();
        $app->redirect('index.php?option=com_k2&view=settings');
    }
}
