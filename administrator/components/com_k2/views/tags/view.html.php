<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewTags extends K2View
{
	function display($tpl = null)
	{
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');

		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'id', 'cmd');
		$filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));

		$model = $this->getModel();
		$total = $model->getTotal();
		$tags = $model->getData();

		if ($limitstart > $total - $limit)
		{
			$limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
			JRequest::setVar('limitstart', $limitstart);
		}

		foreach ($tags as $key => $tag)
		{
			$tag->status = K2_JVERSION == '15' ? JHTML::_('grid.published', $tag, $key) : JHtml::_('jgrid.published', $tag->published, $key, '', $task != 'element');
		}
		$this->assignRef('rows', $tags);

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

		$this->assignRef('lists', $lists);

		// JS
		$document->addScriptDeclaration("
			Joomla.submitbutton = function(pressbutton) {
				if (pressbutton == 'remove') {
					if (confirm('".JText::_('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_TAGS', true)."')){
						submitform( pressbutton );
					}
				} else {
					submitform( pressbutton );
				}
			};
		");

		// Toolbar
		JToolBarHelper::title(JText::_('K2_TAGS'), 'k2.png');

		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList('', 'remove', 'K2_DELETE');
		JToolBarHelper::custom('removeOrphans', 'delete', 'delete', 'K2_DELETE_ORPHAN_TAGS', false);

		// Preferences (Parameters/Settings)
		if (K2_JVERSION != '15')
		{
			JToolBarHelper::preferences('com_k2', 580, 800, 'K2_PARAMETERS');
		}
		else
		{
			$toolbar = JToolBar::getInstance('toolbar');
			$toolbar->appendButton('Popup', 'config', 'K2_PARAMETERS', 'index.php?option=com_k2&view=settings', 800, 580);
		}

		$this->loadHelper('html');
		K2HelperHTML::subMenu();

		parent::display($tpl);
	}
}
