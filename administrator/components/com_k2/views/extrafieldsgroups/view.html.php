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

jimport('joomla.application.component.view');

class K2ViewExtraFieldsGroups extends K2View
{

    function display($tpl = null)
    {

        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', '', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        $model = $this->getModel();
        $total = $model->getTotalGroups();
        if ($limitstart > $total - $limit)
        {
            $limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
            JRequest::setVar('limitstart', $limitstart);
        }
        $extraFieldGroups = $model->getGroups();

        $this->assignRef('rows', $extraFieldGroups);

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $this->assignRef('page', $pageNav);

        JToolBarHelper::title(JText::_('K2_EXTRA_FIELD_GROUPS'), 'k2.png');

        JToolBarHelper::deleteList('', 'remove', 'K2_DELETE');
        JToolBarHelper::editList();
        JToolBarHelper::addNew();

        if (K2_JVERSION != '15')
        {
            JToolBarHelper::preferences('com_k2', 550, 875, 'K2_PARAMETERS');
        }
        else
        {
            $toolbar = JToolBar::getInstance('toolbar');
            $toolbar->appendButton('Popup', 'config', 'Parameters', 'index.php?option=com_k2&view=settings');
        }

        $this->loadHelper('html');
        K2HelperHTML::subMenu();

        parent::display($tpl);
    }

}
