<?php
/**
 * @version    2.12 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class K2ControllerItemlist extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        $model = $this->getModel('item');
        $format = JRequest::getWord('format', 'html');
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('itemlist', $viewType);
        $view->setModel($model);
        $user = JFactory::getUser();
        if ($user->guest) {
            $cache = true;
        } else {
            $cache = false;
        }
        if (K2_JVERSION != '15') {
            $urlparams['amp'] = 'INT';
            $urlparams['day'] = 'INT';
            $urlparams['id'] = 'INT';
            $urlparams['Itemid'] = 'INT';
            $urlparams['lang'] = 'CMD';
            $urlparams['limit'] = 'UINT';
            $urlparams['limitstart'] = 'UINT';
            $urlparams['m'] = 'INT';
            $urlparams['moduleID'] = 'INT';
            $urlparams['month'] = 'INT';
            $urlparams['ordering'] = 'CMD';
            $urlparams['print'] = 'INT';
            $urlparams['searchword'] = 'STRING';
            $urlparams['tag'] = 'STRING';
            $urlparams['template'] = 'CMD';
            $urlparams['tmpl'] = 'CMD';
            $urlparams['year'] = 'INT';
        }
        parent::display($cache, $urlparams);
    }

    // For mod_k2_content
    public function module()
    {
        $document = JFactory::getDocument();
        $view = $this->getView('itemlist', 'raw');
        $model = $this->getModel('itemlist');
        $view->setModel($model);
        $model = $this->getModel('item');
        $view->setModel($model);
        $view->module();
    }

    // For mod_k2_tools
    public function calendar()
    {
        require_once(JPATH_SITE.'/modules/mod_k2_tools/helper.php');
        $calendar = new modK2ToolsHelper();
        $calendar->calendarNavigation();
    }
}
