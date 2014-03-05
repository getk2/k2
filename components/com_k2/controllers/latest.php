<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
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
        if ($user->guest)
        {
            $cache = true;
        }
        else
        {
            $cache = false;
        }
        parent::display($cache);
    }

}
