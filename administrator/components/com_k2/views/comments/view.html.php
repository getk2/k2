<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.view');

class K2ViewComments extends K2View
{

	function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		if ($mainframe->isSite())
		{
			$filter_author = $user->id;
			JRequest::setVar('filter_author', $user->id);
		}
		$this->loadHelper('html');
		K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model = K2Model::getInstance('Comments', 'K2Model');
		$params = JComponentHelper::getParams('com_k2');
		$total = $model->getTotal();
		if ($limitstart > $total - $limit)
		{
			$limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
			JRequest::setVar('limitstart', $limitstart);
		}
		$comments = $model->getData();

		$reportLink = $mainframe->isAdmin() ? 'index.php?option=com_k2&view=user&task=report&id=' : 'index.php?option=com_k2&view=comments&task=reportSpammer&id=';
		foreach ($comments as $key => $comment)
		{
			$comment->reportUserLink = false;
			$comment->commenterLastVisitIP = NULL;
			if ($comment->userID)
			{

				$db = JFactory::getDBO();
				$db->setQuery("SELECT ip FROM #__k2_users WHERE userID = ".$comment->userID);
				$comment->commenterLastVisitIP = $db->loadResult();

				$commenter = JFactory::getUser($comment->userID);
				if ($commenter->name)
				{
					$comment->userName = $commenter->name;
				}
				if ($mainframe->isSite())
				{
					if (K2_JVERSION != '15')
					{
						if ($user->authorise('core.admin', 'com_k2'))
						{
							$comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
						}
					}
					else
					{
						if ($user->gid > 24)
						{
							$comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
						}
					}
				}
				else
				{
					$comment->reportUserLink = JRoute::_($reportLink.$comment->userID);
				}
			}

			if ($mainframe->isSite())
			{
				$comment->status = K2HelperHTML::stateToggler($comment, $key);
			}
			else
			{
				$comment->status = K2_JVERSION == '15' ? JHTML::_('grid.published', $comment, $key) : JHtml::_('jgrid.published', $comment->published, $key);
			}

		}

		$this->assignRef('rows', $comments);

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		$this->assignRef('page', $pageNav);

		$lists = array();
		$lists['search'] = $search;
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
		$options[] = JHTML::_('select.option', 0, '- '.JText::_('K2_NO_USER').' -');
		foreach ($authors as $author)
		{
			$name = $author->name;
			if ($author->block)
			{
				$name .= ' ['.JText::_('K2_USER_DISABLED').']';
			}
			$options[] = JHTML::_('select.option', $author->id, $name);
		}
		$lists['authors'] = JHTML::_('select.genericlist', $options, 'filter_author', '', 'value', 'text', $filter_author);
		$this->assignRef('lists', $lists);
		$this->assignRef('mainframe', $mainframe);

		if (K2_JVERSION != '15')
		{
			$dateFormat = JText::_('K2_J16_DATE_FORMAT');
		}
		else
		{
			$dateFormat = JText::_('K2_DATE_FORMAT');
		}
		$this->assignRef('dateFormat', $dateFormat);

		if ($mainframe->isAdmin())
		{
			JToolBarHelper::title(JText::_('K2_COMMENTS'), 'k2.png');
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'K2_DELETE');
			JToolBarHelper::custom('deleteUnpublished', 'delete', 'delete', 'K2_DELETE_ALL_UNPUBLISHED', false);
			$toolbar = JToolBar::getInstance('toolbar');

			if (K2_JVERSION != '15')
			{
				JToolBarHelper::preferences('com_k2', 550, 875, 'K2_PARAMETERS');
			}
			else
			{
				$toolbar->appendButton('Popup', 'config', 'Parameters', 'index.php?option=com_k2&view=settings');
			}
			K2HelperHTML::subMenu();

			if (K2_JVERSION != '15')
			{
				$userEditLink = JURI::base().'index.php?option=com_k2&view=user&cid=';
			}
			else
			{
				$userEditLink = JURI::base().'index.php?option=com_k2&view=user&cid=';
			}
			$this->assignRef('userEditLink', $userEditLink);

		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration('var K2Language = ["'.JText::_('K2_YOU_CANNOT_EDIT_TWO_COMMENTS_AT_THE_SAME_TIME', true).'", "'.JText::_('K2_THIS_WILL_PERMANENTLY_DELETE_ALL_UNPUBLISHED_COMMENTS_ARE_YOU_SURE', true).'", "'.JText::_('K2_REPORT_USER_WARNING', true).'"];');

		if ($mainframe->isSite())
		{
			// CSS
			$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.frontend.css?v=2.7.0');
			$document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
			$document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');
			if (K2_JVERSION != '15')
			{
				$document->addStyleSheet(JURI::root(true).'/administrator/templates/bluestork/css/template.css');
				$document->addStyleSheet(JURI::root(true).'/media/system/css/system.css');
			}
			else
			{
				$document->addStyleSheet(JURI::root(true).'/administrator/templates/khepri/css/general.css');
			}
		}

		parent::display($tpl);
	}

}
