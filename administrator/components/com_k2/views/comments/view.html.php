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

class K2ViewComments extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');

        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.id', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
        $filter_state = $app->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
        $filter_category = $app->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
        $filter_author = $app->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
        $search = $app->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\"\.\@\-_]/u', '', $search));
        if ($app->isSite()) {
            $filter_author = $user->id;
            JRequest::setVar('filter_author', $user->id);
        }
        $this->loadHelper('html');

        // Head includes
        K2HelperHTML::loadHeadIncludes(true, false, true, true);

        // JS
        $document->addScriptDeclaration("
			var K2Language = [
				'".JText::_('K2_YOU_CANNOT_EDIT_TWO_COMMENTS_AT_THE_SAME_TIME', true)."',
				'".JText::_('K2_THIS_WILL_PERMANENTLY_DELETE_ALL_UNPUBLISHED_COMMENTS_ARE_YOU_SURE', true)."',
				'".JText::_('K2_REPORT_USER_WARNING', true)."'
			];

			Joomla.submitbutton = function(pressbutton) {
				if (pressbutton == 'remove') {
					if (document.adminForm.boxchecked.value==0){
						alert('".JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_DELETE', true)."');
						return false;
					}
					if (confirm('".JText::_('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_COMMENTS', true)."')){
						submitform(pressbutton);
					}
				} else if (pressbutton == 'deleteUnpublished') {
					if (confirm('".JText::_('K2_THIS_WILL_PERMANENTLY_DELETE_ALL_UNPUBLISHED_COMMENTS_ARE_YOU_SURE', true)."')){
						submitform(pressbutton);
					}
				} else if (pressbutton == 'publish') {
					if (document.adminForm.boxchecked.value==0){
						alert('".JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_PUBLISH', true)."');
						return false;
					}
					submitform(pressbutton);
				} else if (pressbutton == 'unpublish') {
					if (document.adminForm.boxchecked.value==0){
						alert('".JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_UNPUBLISH', true)."');
						return false;
					}
					submitform(pressbutton);
				}  else {
					submitform(pressbutton);
				}
			};
		");

        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $total = $model->getTotal();
        $comments = $model->getData();

        if ($limitstart > $total - $limit) {
            $limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
            JRequest::setVar('limitstart', $limitstart);
        }

        $reportLink = $app->isAdmin() ? 'index.php?option=com_k2&view=user&task=report&id=' : 'index.php?option=com_k2&view=comments&task=reportSpammer&id=';
        foreach ($comments as $key => $comment) {
            $comment->reportUserLink = false;
            $comment->commenterLastVisitIP = null;
            if ($comment->userID) {
                $db = JFactory::getDbo();
                $db->setQuery("SELECT ip FROM #__k2_users WHERE userID = ".$comment->userID);
                $comment->commenterLastVisitIP = $db->loadResult();

                $commenter = JFactory::getUser($comment->userID);
                if ($commenter->name) {
                    $comment->userName = $commenter->name;
                }
                if ($app->isSite()) {
                    if (K2_JVERSION != '15') {
                        if ($user->authorise('core.admin', 'com_k2')) {
                            $comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
                        }
                    } else {
                        if ($user->gid > 24) {
                            $comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
                        }
                    }
                } else {
                    $comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
                }
            }

            if ($app->isSite()) {
                $comment->status = K2HelperHTML::stateToggler($comment, $key);
            } else {
                $comment->status = K2_JVERSION == '15' ? JHTML::_('grid.published', $comment, $key) : JHtml::_('jgrid.published', $comment->published, $key);
            }
        }
        $this->assignRef('rows', $comments);

        // Pagination
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $this->assignRef('page', $pageNav);

        $lists = array();

        // Detect exact search phrase using double quotes in search string
        if (substr($search, 0, 1)=='"' && substr($search, -1)=='"') {
            $lists['search'] = "\"".trim(str_replace('"', '', $search))."\"";
        } else {
            $lists['search'] = trim(str_replace('"', '', $search));
        }

        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $filter_state_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_STATE'));
        $filter_state_options[] = JHTML::_('select.option', 1, JText::_('K2_PUBLISHED'));
        $filter_state_options[] = JHTML::_('select.option', 0, JText::_('K2_UNPUBLISHED'));
        $lists['state'] = JHTML::_('select.genericlist', $filter_state_options, 'filter_state', '', 'value', 'text', $filter_state);

        require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
        $categoriesModel = K2Model::getInstance('Categories', 'K2Model');
        $categories_option[] = JHTML::_('select.option', 0, JText::_('K2_SELECT_CATEGORY'));
        $categories = $categoriesModel->categoriesTree(null, true, false);
        $categories_options = @array_merge($categories_option, $categories);
        $lists['categories'] = JHTML::_('select.genericlist', $categories_options, 'filter_category', '', 'value', 'text', $filter_category);

        require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/items.php';
        $itemsModel = K2Model::getInstance('Items', 'K2Model');
        $authors = $itemsModel->getItemsAuthors();
        $options = array();
        $options[] = JHTML::_('select.option', 0, JText::_('K2_NO_USER'));
        foreach ($authors as $author) {
            $name = $author->name;
            if ($author->block) {
                $name .= ' ['.JText::_('K2_USER_DISABLED').']';
            }
            $options[] = JHTML::_('select.option', $author->id, $name);
        }
        $lists['authors'] = JHTML::_('select.genericlist', $options, 'filter_author', '', 'value', 'text', $filter_author);
        $this->assignRef('lists', $lists);

        if (K2_JVERSION != '15') {
            $dateFormat = JText::_('K2_J16_DATE_FORMAT');
        } else {
            $dateFormat = JText::_('K2_DATE_FORMAT');
        }
        $this->assignRef('dateFormat', $dateFormat);

        if ($app->isAdmin()) {
            // Toolbar
            $toolbar = JToolBar::getInstance('toolbar');
            JToolBarHelper::title(JText::_('K2_COMMENTS'), 'k2.png');

            JToolBarHelper::publishList();
            JToolBarHelper::unpublishList();
            JToolBarHelper::deleteList('', 'remove', 'K2_DELETE');
            JToolBarHelper::custom('deleteUnpublished', 'delete', 'delete', 'K2_DELETE_ALL_UNPUBLISHED', false);

            // Preferences (Parameters/Settings)
            if (K2_JVERSION != '15') {
                JToolBarHelper::preferences('com_k2', '(window.innerHeight) * 0.9', '(window.innerWidth) * 0.7', 'K2_SETTINGS');
            } else {
                $toolbar->appendButton('Popup', 'config', 'K2_SETTINGS', 'index.php?option=com_k2&view=settings', '(window.innerWidth) * 0.7', '(window.innerHeight) * 0.9');
            }
            K2HelperHTML::subMenu();

            $userEditLink = JURI::base().'index.php?option=com_k2&view=user&cid=';
            $this->assignRef('userEditLink', $userEditLink);
        }

        if ($app->isSite()) {
            // Enforce the "system" template in the frontend
            JRequest::setVar('template', 'system');

            // CSS
            $document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
            $document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');
        }

        parent::display($tpl);
    }
}
