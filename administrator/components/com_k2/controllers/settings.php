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

jimport('joomla.application.component.controller');

class K2ControllerSettings extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        if (K2_JVERSION != '15')
        {
            $application = JFactory::getApplication();
            $application->redirect('index.php?option=com_config&view=component&component=com_k2&path=&tmpl=component');
        }
        else
        {
            JRequest::setVar('tmpl', 'component');
            parent::display();
        }
    }

    function save()
    {
        $application = JFactory::getApplication();
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('settings');
        $model->save();
        $application->redirect('index.php?option=com_k2&view=settings');

    }

}
