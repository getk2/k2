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

class K2ControllerLatest extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->getView('latest', 'html');
        $model = $this->getModel('itemlist');
        $view->setModel($model);
        $itemModel = $this->getModel('item');
        $view->setModel($itemModel);
        $user = JFactory::getUser();
        if ($user->guest) {
            $cache = true;
        } else {
            $cache = false;
        }
        if (K2_JVERSION != '15') {
            $urlparams['Itemid'] = 'INT';
            $urlparams['m'] = 'INT';
            $urlparams['amp'] = 'INT';
            $urlparams['tmpl'] = 'CMD';
            $urlparams['template'] = 'CMD';
        }
        parent::display($cache, $urlparams);
    }
}
