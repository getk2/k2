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

jimport('joomla.application.component.view');

class K2ViewCategories extends K2View
{
	function display($tpl = null)
	{
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		$params = JComponentHelper::getParams('com_k2');
		$this->assignRef('params', $params);

		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');

		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.ordering', 'cmd');
		$filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');
		$filter_trash = $application->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
		$filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$language = $application->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\"\-_]/u', '', $search));
		$model = $this->getModel();
		$total = $model->getTotal();
		$task = JRequest::getCmd('task');
		if ($limitstart > $total - $limit)
		{
			$limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
			JRequest::setVar('limitstart', $limitstart);
		}

		$categories = $model->getData();
		$categoryModel = K2Model::getInstance('Category', 'K2Model');

		// JS
		$document->addScriptDeclaration("
			var K2SelectItemsError = '".JText::_('K2_SELECT_SOME_ITEMS_FIRST')."';

			Joomla.submitbutton = function(pressbutton) {
				if (pressbutton == 'trash') {
					var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_TRASH_THE_SELECTED_CATEGORIES_THEIR_CHILDREN_CATEGORIES_AND_ALL_THEIR_INCLUDED_ITEMS', true)."')
					if (answer){
						submitform(pressbutton);
					} else {
						return;
					}
				} else {
					submitform(pressbutton);
				}
			};
		");

		if (K2_JVERSION != '15')
		{
			$langs = JLanguageHelper::getLanguages();
			$langsMapping = array();
			$langsMapping['*'] = JText::_('K2_ALL');
			foreach ($langs as $lang)
			{
				$langsMapping[$lang->lang_code] = $lang->title;
			}
		}

		for ($i = 0; $i < sizeof($categories); $i++)
		{
			$categories[$i]->status = K2_JVERSION == '15' ? JHTML::_('grid.published', $categories[$i], $i) : JHtml::_('jgrid.published', $categories[$i]->published, $i, '', $filter_trash == 0 && $task != 'element');
			if ($params->get('showItemsCounterAdmin'))
			{
				$categories[$i]->numOfItems = $categoryModel->countCategoryItems($categories[$i]->id);
				$categories[$i]->numOfTrashedItems = $categoryModel->countCategoryItems($categories[$i]->id, 1);
			}
			if (K2_JVERSION == '30')
			{
				$categories[$i]->canChange = $user->authorise('core.edit.state', 'com_k2.category.'.$categories[$i]->id);
			}

			// Detect the category template
			if (K2_JVERSION != '15')
			{
				$categoryParams = json_decode($categories[$i]->params);
				$categories[$i]->template = $categoryParams->theme;
				$categories[$i]->language = $categories[$i]->language ? $categories[$i]->language : '*';
				if (isset($langsMapping))
				{
					$categories[$i]->language = $langsMapping[$categories[$i]->language];
				}
			}
			else
			{
				if (function_exists('parse_ini_string'))
				{
					$categoryParams = parse_ini_string($categories[$i]->params);
					$categories[$i]->template = $categoryParams['theme'];
				}
				else
				{
					$categoryParams = new JParameter($categories[$i]->params);
					$categories[$i]->template = $categoryParams->get('theme');
				}
			}
			if (!$categories[$i]->template)
			{
				$categories[$i]->template = 'default';
			}
		}

		$this->assignRef('rows', $categories);

		// Show message for trash entries in Categories
		if(count($categories) && $filter_trash) {
			$application->enqueueMessage(JText::_('K2_ALL_TRASHED_ITEMS_IN_A_CATEGORY_MUST_BE_DELETED_FIRST'));
		}

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		$this->assignRef('page', $pageNav);

		$lists = array();

		// Detect exact search phrase using double quotes in search string
		if(substr($search, 0, 1)=='"' && substr($search, -1)=='"')
		{
			$lists['search'] = "\"".trim(str_replace('"', '', $search))."\"";
		}
		else
		{
			$lists['search'] = trim(str_replace('"', '', $search));
		}

		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$filter_trash_options[] = JHTML::_('select.option', 0, JText::_('K2_CURRENT'));
		$filter_trash_options[] = JHTML::_('select.option', 1, JText::_('K2_TRASHED'));
		$lists['trash'] = JHTML::_('select.genericlist', $filter_trash_options, 'filter_trash', '', 'value', 'text', $filter_trash);

		$filter_state_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_STATE'));
		$filter_state_options[] = JHTML::_('select.option', 1, JText::_('K2_PUBLISHED'));
		$filter_state_options[] = JHTML::_('select.option', 0, JText::_('K2_UNPUBLISHED'));
		$lists['state'] = JHTML::_('select.genericlist', $filter_state_options, 'filter_state', '', 'value', 'text', $filter_state);

		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
		$categoriesModel = K2Model::getInstance('Categories', 'K2Model');
		$categories_option[] = JHTML::_('select.option', 0, JText::_('K2_SELECT_CATEGORY'));
		$categoriesFilter = $categoriesModel->categoriesTree(NULL, true, false);
		$categoriesTree = $categoriesFilter;
		$categories_options = @array_merge($categories_option, $categoriesFilter);
		$lists['categories'] = JHTML::_('select.genericlist', $categories_options, 'filter_category', '', 'value', 'text', $filter_category);

		// Batch Operations
		$extraFieldsModel = K2Model::getInstance('ExtraFields', 'K2Model');
		$extraFieldsGroups = $extraFieldsModel->getGroups();
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED'));
		$options[] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'));
		foreach ($extraFieldsGroups as $extraFieldsGroup)
		{
			$name = $extraFieldsGroup->name;
			$options[] = JHTML::_('select.option', $extraFieldsGroup->id, $name);
		}
		$lists['batchExtraFieldsGroups'] = JHTML::_('select.genericlist', $options, 'batchExtraFieldsGroups', '', 'value', 'text', null);

		array_unshift($categoriesTree, JHtml::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS')));
		array_unshift($categoriesTree, JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')));

		$lists['batchCategories'] = JHTML::_('select.genericlist', $categoriesTree, 'batchCategory', 'class="inputbox" size="8"', 'value', 'text', null);

		$lists['batchAccess'] = version_compare(JVERSION, '2.5', 'ge') ? JHTML::_('access.level', 'batchAccess', null, '', array(JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')))) : str_replace('size="3"', "", JHTML::_('list.accesslevel', ''));

		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			$languages = JHTML::_('contentlanguage.existing', true, true);
			array_unshift($languages, JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')));
			$lists['batchLanguage'] = JHTML::_('select.genericlist', $languages, 'batchLanguage', '', 'value', 'text', null);
		}

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$languages = JHTML::_('contentlanguage.existing', true, true);
			array_unshift($languages, JHTML::_('select.option', '', JText::_('K2_SELECT_LANGUAGE')));
			$lists['language'] = JHTML::_('select.genericlist', $languages, 'language', '', 'value', 'text', $language);
		}
		$this->assignRef('lists', $lists);

		// Toolbar
		JToolBarHelper::title(JText::_('K2_CATEGORIES'), 'k2.png');
		$toolbar = JToolBar::getInstance('toolbar');

		if ($filter_trash == 1)
		{
			JToolBarHelper::deleteList('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_CATEGORIES', 'remove', 'K2_DELETE');
			JToolBarHelper::custom('restore', 'publish.png', 'publish_f2.png', 'K2_RESTORE', true);
		}
		else
		{
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::trash('trash');
			JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'K2_COPY', true);
			if (K2_JVERSION == '30')
			{
				$batchButton = '<a id="K2BatchButton" class="btn btn-small" href="#"><i class="icon-edit "></i>'.JText::_('K2_BATCH').'</a>';
			}
			else
			{
				$batchButton = '<a id="K2BatchButton" href="#"><span class="icon-32-edit" title="'.JText::_('K2_BATCH').'"></span>'.JText::_('K2_BATCH').'</a>';
			}
			$toolbar->appendButton('Custom', $batchButton);
		}

		// Preferences (Parameters/Settings)
		if (K2_JVERSION != '15')
		{
			JToolBarHelper::preferences('com_k2', 580, 800, 'K2_PARAMETERS');
		}
		else
		{
			$toolbar->appendButton('Popup', 'config', 'K2_PARAMETERS', 'index.php?option=com_k2&view=settings', 800, 580);
		}

		$this->loadHelper('html');
		K2HelperHTML::subMenu();

		$this->assignRef('filter_trash', $filter_trash);
		$template = $application->getTemplate();
		$this->assignRef('template', $template);
		$ordering = (($this->lists['order'] == 'c.ordering' || $this->lists['order'] == 'c.parent, c.ordering') && (!$this->filter_trash));
		$this->assignRef('ordering', $ordering);

		// Joomla 3.0 drag-n-drop sorting variables
		if (K2_JVERSION == '30')
		{
			if ($ordering)
			{
				JHtml::_('sortablelist.sortable', 'k2CategoriesList', 'adminForm', strtolower($this->lists['order_Dir']), 'index.php?option=com_k2&view=categories&task=saveorder&format=raw');
			}
			$document->addScriptDeclaration('
	            Joomla.orderTable = function() {
	                table = document.getElementById("sortTable");
	                direction = document.getElementById("directionTable");
	                order = table.options[table.selectedIndex].value;
	                if (order != \''.$this->lists['order'].'\') {
						dirn = \'asc\';
		            } else {
		                dirn = direction.options[direction.selectedIndex].value;
		            }
					Joomla.tableOrdering(order, dirn, "");
	            }
            ');
		}

		parent::display($tpl);
	}
}
