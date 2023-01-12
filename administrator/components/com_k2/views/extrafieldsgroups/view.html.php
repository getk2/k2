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

jimport('joomla.application.component.view');

class K2ViewExtraFieldsGroups extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');

        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', '', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        $model = $this->getModel();
        $total = $model->getTotalGroups();
        if ($limitstart > $total - $limit) {
            $limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
            JRequest::setVar('limitstart', $limitstart);
        }
        $extraFieldGroups = $model->getGroups();

        $this->assignRef('rows', $extraFieldGroups);

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $this->assignRef('page', $pageNav);

        // Toolbar
        JToolBarHelper::title(JText::_('K2_EXTRA_FIELD_GROUPS'), 'k2.png');

        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::deleteList('', 'remove', 'K2_DELETE');

        if (K2_JVERSION != '15') {
            JToolBarHelper::preferences('com_k2', '(window.innerHeight) * 0.9', '(window.innerWidth) * 0.7', 'K2_SETTINGS');
        } else {
            $toolbar = JToolBar::getInstance('toolbar');
            $toolbar->appendButton('Popup', 'config', 'K2_SETTINGS', 'index.php?option=com_k2&view=settings', '(window.innerWidth) * 0.7', '(window.innerHeight) * 0.9');
        }

        $this->loadHelper('html');
        K2HelperHTML::subMenu();

        // JS
        $document = JFactory::getDocument();
        $document->addScriptDeclaration("
            Joomla.submitbutton = function(pressbutton) {
                if (pressbutton == 'remove') {
                    if (confirm('".JText::_('K2_WARNING_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_EXTRA_FIELDS_GROUPS_DELETING_THE_GROUPS_WILL_ALSO_DELETE_THE_ASSIGNED_EXTRA_FIELDS', true)."')){
                        submitform(pressbutton);
                    }
                } else {
                    submitform(pressbutton);
                }
            };
        ");

        parent::display($tpl);
    }
}
