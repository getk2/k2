<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewUsers extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $db = JFactory::getDbo();

        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $task = JRequest::getCmd('task');

        $context = JRequest::getCmd('context');

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'juser.name', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_status = $app->getUserStateFromRequest($option.$view.'filter_status', 'filter_status', -1, 'int');
        $filter_group = $app->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', '', 'string');
        $filter_group_k2 = $app->getUserStateFromRequest($option.$view.'filter_group_k2', 'filter_group_k2', '', 'string');
        $search = $app->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));

        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');
        $model = K2Model::getInstance('Users', 'K2Model');
        $total = $model->getTotal();
        if ($limitstart > $total - $limit) {
            $limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
            JRequest::setVar('limitstart', $limitstart);
        }
        $users = $model->getData();
        for ($i = 0; $i < count($users); $i++) {
            $users[$i]->loggedin = $model->checkLogin($users[$i]->id);
            $users[$i]->profileID = $model->hasProfile($users[$i]->id);
            if ($users[$i]->profileID) {
                $db->setQuery("SELECT ip FROM #__k2_users WHERE id = ".$users[$i]->profileID);
                $users[$i]->ip = $db->loadResult();
            } else {
                $users[$i]->ip = '';
            }

            if ($users[$i]->lastvisitDate == "0000-00-00 00:00:00") {
                $users[$i]->lvisit = false;
            } else {
                $users[$i]->lvisit = $users[$i]->lastvisitDate;
            }
            $users[$i]->link = JRoute::_('index.php?option=com_k2&view=user&cid='.$users[$i]->id);
            if (K2_JVERSION == '15') {
                $users[$i]->published = $users[$i]->loggedin;
                $users[$i]->loggedInStatus = strip_tags(JHTML::_('grid.published', $users[$i], $i), '<img>');
                $users[$i]->blockStatus = '';
                if ($users[$i]->block) {
                    $users[$i]->blockStatus .= '<a title="'.JText::_('K2_ENABLE').'" onclick="return listItemTask(\'cb'.$i.',\'enable\')" href="#"><img alt="'.JText::_('K2_ENABLED').'" src="images/publish_x.png"></a>';
                } else {
                    $users[$i]->blockStatus .= '<a title="'.JText::_('K2_DISABLE').'" onclick="return listItemTask(\'cb'.$i.',\'disable\')" href="#"><img alt="'.JText::_('K2_DISABLED').'" src="images/tick.png"></a>';
                }
                if ($context == 'modalselector') {
                    $users[$i]->blockStatus = strip_tags($users[$i]->blockStatus, '<img>');
                }
            } else {
                $states = array(1 => array('', 'K2_LOGGED_IN', 'K2_LOGGED_IN', 'K2_LOGGED_IN', false, 'publish', 'publish'), 0 => array('', 'K2_NOT_LOGGED_IN', 'K2_NOT_LOGGED_IN', 'K2_NOT_LOGGED_IN', false, 'unpublish', 'unpublish'), );
                $users[$i]->loggedInStatus = JHtml::_('jgrid.state', $states, $users[$i]->loggedin, $i, '', false);
                $states = array(
                0 => array('disable', 'K2_ENABLED', 'K2_DISABLE', 'K2_ENABLED', false, 'publish', 'publish'),
                1 => array('enable', 'K2_DISABLED', 'K2_ENABLE', 'K2_DISABLED', false, 'unpublish', 'unpublish'));
                $users[$i]->blockStatus = JHtml::_('jgrid.state', $states, $users[$i]->block, $i, '', $context != 'modalselector');
            }
        }

        $this->assignRef('rows', $users);

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $this->assignRef('page', $pageNav);

        $lists = array();
        $lists['search'] = $search;
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $filter_status_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_STATE'));
        $filter_status_options[] = JHTML::_('select.option', 0, JText::_('K2_ENABLED'));
        $filter_status_options[] = JHTML::_('select.option', 1, JText::_('K2_BLOCKED'));
        $lists['status'] = JHTML::_('select.genericlist', $filter_status_options, 'filter_status', '', 'value', 'text', $filter_status);

        $userGroups = $model->getUserGroups();
        $groups[] = JHTML::_('select.option', '0', JText::_('K2_SELECT_JOOMLA_GROUP'));

        foreach ($userGroups as $userGroup) {
            $groups[] = JHTML::_('select.option', $userGroup->value, $userGroup->text);
        }

        $lists['filter_group'] = JHTML::_('select.genericlist', $groups, 'filter_group', '', 'value', 'text', $filter_group);

        $K2userGroups = $model->getUserGroups('k2');
        $K2groups[] = JHTML::_('select.option', '0', JText::_('K2_SELECT_K2_GROUP'));

        foreach ($K2userGroups as $K2userGroup) {
            $K2groups[] = JHTML::_('select.option', $K2userGroup->id, $K2userGroup->name);
        }

        $lists['filter_group_k2'] = JHTML::_('select.genericlist', $K2groups, 'filter_group_k2', '', 'value', 'text', $filter_group_k2);

        $this->assignRef('lists', $lists);

        if (K2_JVERSION != '15') {
            $dateFormat = JText::_('K2_J16_DATE_FORMAT');
        } else {
            $dateFormat = JText::_('K2_DATE_FORMAT');
        }
        $this->assignRef('dateFormat', $dateFormat);

        $template = $app->getTemplate();
        $this->assignRef('template', $template);

        if ($app->isAdmin()) {
            // JS
            $document->addScriptDeclaration("
                var K2Language = ['".JText::_('K2_REPORT_USER_WARNING', true)."'];

                \$K2(document).ready(function() {
                    \$K2('#K2ImportUsersButton').click(function(event) {
                        var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_JOOMLA_USERS_TO_K2_GENERATING_CORRESPONDING_K2_USER_GROUPS_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED', true)."');
                        if (!answer) {
                            event.preventDefault();
                        }
                    });
                });
            ");

            // Toolbar
            $toolbar = JToolBar::getInstance('toolbar');
            JToolBarHelper::title(JText::_('K2_USERS'), 'k2.png');

            JToolBarHelper::editList();
            JToolBarHelper::publishList('enable', 'K2_ENABLE');
            JToolBarHelper::unpublishList('disable', 'K2_DISABLE');
            JToolBarHelper::deleteList('K2_WARNING_YOU_ARE_ABOUT_TO_DELETE_THE_SELECTED_USERS_PERMANENTLY_FROM_THE_SYSTEM', 'delete', 'K2_DELETE');
            JToolBarHelper::deleteList('K2_ARE_YOU_SURE_YOU_WANT_TO_RESET_SELECTED_USERS', 'remove', 'K2_RESET_USER_DETAILS');
            JToolBarHelper::custom('move', 'move.png', 'move_f2.png', 'K2_MOVE', true);

            $canImport = false;
            if (K2_JVERSION == '15') {
                $canImport = $user->gid > 23;
            } else {
                $canImport = $user->authorise('core.admin', 'com_k2');
            }
            if ($canImport) {
                if (!$params->get('hideImportButton')) {
                    $buttonUrl = JURI::base().'index.php?option=com_k2&amp;view=users&amp;task=import';
                    $buttonText = JText::_('K2_IMPORT_JOOMLA_USERS');
                    if (K2_JVERSION == '30') {
                        $button = '<a id="K2ImportUsersButton" class="btn btn-small" href="'.$buttonUrl.'"><i class="icon-archive "></i>'.$buttonText.'</a>';
                    } else {
                        $button = '<a id="K2ImportUsersButton" href="'.$buttonUrl.'"><span class="icon-32-archive" title="'.$buttonText.'"></span>'.$buttonText.'</a>';
                    }
                    $toolbar->appendButton('Custom', $button);
                }
            }

            $this->loadHelper('html');
            K2HelperHTML::subMenu();

            // Preferences (Parameters/Settings)
            if (K2_JVERSION != '15') {
                JToolBarHelper::preferences('com_k2', '(window.innerHeight) * 0.9', '(window.innerWidth) * 0.7', 'K2_SETTINGS');
            } else {
                $toolbar->appendButton('Popup', 'config', 'K2_SETTINGS', 'index.php?option=com_k2&view=settings', '(window.innerWidth) * 0.7', '(window.innerHeight) * 0.9');
            }
        }
        $isAdmin = $app->isAdmin();
        $this->assignRef('isAdmin', $isAdmin);

        // Head includes
        K2HelperHTML::loadHeadIncludes(true, false, true, true);
        if ($app->isSite()) {
            // CSS
            $document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
            $document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');
        }

        parent::display($tpl);
    }

    public function move()
    {
        $app = JFactory::getApplication();

        $cid = JRequest::getVar('cid');
        JArrayHelper::toInteger($cid);
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        foreach ($cid as $id) {
            $row = JFactory::getUser($id);
            $rows[] = $row;
        }
        $this->assignRef('rows', $rows);

        $model = $this->getModel('users');
        $lists = array();
        $userGroups = $model->getUserGroups();
        $groups[] = JHTML::_('select.option', '', JText::_('K2_DO_NOT_CHANGE'));
        foreach ($userGroups as $userGroup) {
            $groups[] = JHTML::_('select.option', $userGroup->value, JText::_($userGroup->text));
        }
        $fieldName = 'group';
        $attributes = 'size="10"';
        if (K2_JVERSION != '15') {
            $attributes .= 'multiple="multiple"';
            $fieldName .= '[]';
        }

        $lists['group'] = JHTML::_('select.genericlist', $groups, $fieldName, $attributes, 'value', 'text', '');

        $K2userGroups = $model->getUserGroups('k2');
        $K2groups[] = JHTML::_('select.option', '0', JText::_('K2_DO_NOT_CHANGE'));
        foreach ($K2userGroups as $K2userGroup) {
            $K2groups[] = JHTML::_('select.option', $K2userGroup->id, $K2userGroup->name);
        }
        $lists['k2group'] = JHTML::_('select.genericlist', $K2groups, 'k2group', 'size="10"', 'value', 'text', 0);

        $this->assignRef('lists', $lists);

        // Toolbar
        JToolBarHelper::title(JText::_('K2_MOVE_USERS'), 'k2.png');

        JToolBarHelper::custom('saveMove', 'save.png', 'save_f2.png', 'K2_SAVE', false);
        JToolBarHelper::custom('cancelMove', 'cancel.png', 'cancel_f2.png', 'K2_CANCEL', false);

        parent::display();
    }
}
