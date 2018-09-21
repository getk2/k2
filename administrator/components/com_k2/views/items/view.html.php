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

class K2ViewItems extends K2View
{
	function display($tpl = null)
	{
		jimport('joomla.filesystem.file');
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();

		$user = JFactory::getUser();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');

		$params = JComponentHelper::getParams('com_k2');
		$this->assignRef('params', $params);

		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'i.id', 'cmd');
		$filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_trash = $application->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
		$filter_featured = $application->getUserStateFromRequest($option.$view.'filter_featured', 'filter_featured', -1, 'int');
		$filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $application->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\"\-_]/u', '', $search));
		$tag = $application->getUserStateFromRequest($option.$view.'tag', 'tag', 0, 'int');
		$language = $application->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');

		$db = JFactory::getDbo();
		$nullDate = $db->getNullDate();

		// JS
		$document->addScriptDeclaration("
			var K2SelectItemsError = '".JText::_('K2_SELECT_SOME_ITEMS_FIRST')."';
			\$K2(document).ready(function(){
				\$K2('#K2ImportContentButton').click(function(event){
					var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_ALL_SECTIONS_CATEGORIES_AND_ARTICLES_FROM_JOOMLAS_CORE_CONTENT_COMPONENT_COM_CONTENT_INTO_K2_IF_THIS_IS_THE_FIRST_TIME_YOU_IMPORT_CONTENT_TO_K2_AND_YOUR_SITE_HAS_MORE_THAN_A_FEW_THOUSAND_ARTICLES_THE_PROCESS_MAY_TAKE_A_FEW_MINUTES_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED', true)."');
					if(!answer){
						event.preventDefault();
					}
				});
			});
		");

		$this->assignRef('nullDate', $nullDate);

		if(K2_JVERSION == '30' && $filter_featured == 1 && $filter_order == 'i.ordering')
		{
			$filter_order = 'i.featured_ordering';
			JRequest::setVar('filter_order', 'i.featured_ordering');
		}

		if(K2_JVERSION == '30' && $filter_featured != 1 && $filter_order == 'i.featured_ordering')
		{
			$filter_order = 'i.ordering';
			JRequest::setVar('filter_order', 'i.ordering');
		}

		$model = $this->getModel();
		$total = $model->getTotal();
		if ($limitstart > $total - $limit)
		{
			$limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
			JRequest::setVar('limitstart', $limitstart);
		}
		$items = $model->getData();

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

		foreach ($items as $key => $item)
		{
			if (K2_JVERSION != '15')
			{
				$item->status = JHtml::_('jgrid.published', $item->published, $key, '', ($filter_trash == 0), 'cb', $item->publish_up, $item->publish_down);
				$states = array(
					1 => array(
						'featured',
						'K2_FEATURED',
						'K2_REMOVE_FEATURED_FLAG',
						'K2_FEATURED',
						false,
						'publish',
						'publish'
					),
					0 => array(
						'featured',
						'K2_NOT_FEATURED',
						'K2_FLAG_AS_FEATURED',
						'K2_NOT_FEATURED',
						false,
						'unpublish',
						'unpublish'
					),
				);
				$item->featuredStatus = JHtml::_('jgrid.state', $states, $item->featured, $key, '', $filter_trash == 0);
				$item->canChange = $user->authorise('core.edit.state', 'com_k2.item.'.$item->id);
				$item->language = $item->language ? $item->language : '*';
				if (isset($langsMapping))
				{
					$item->language = $langsMapping[$item->language];
				}
			}
			else
			{
				$now = JFactory::getDate();
				$config = JFactory::getConfig();
				$publish_up = JFactory::getDate($item->publish_up);
				$publish_down = JFactory::getDate($item->publish_down);
				$publish_up->setOffset($config->getValue('config.offset'));
				$publish_down->setOffset($config->getValue('config.offset'));
				$img = 'tick.png';
				if ($now->toUnix() <= $publish_up->toUnix() && $item->published == 1)
				{
					$img = 'publish_y.png';
				}
				else if (($now->toUnix() <= $publish_down->toUnix() || $item->publish_down == $nullDate) && $item->published == 1)
				{
					$img = 'tick.png';
				}
				else if ($now->toUnix() > $publish_down->toUnix() && $item->published == 1)
				{
					$img = 'publish_r.png';
				}
				$item->status = JHTML::_('grid.published', $item, $key, $img);
				if ($filter_trash)
				{
					$item->status = strip_tags($item->status, '<img>');
				}

				$item->featuredStatus = '';
				if (!$filter_trash)
				{
					$tmpTitle = $item->featured ? JText::_('K2_REMOVE_FEATURED_FLAG') : JText::_('K2_FLAG_AS_FEATURED');
					$item->featuredStatus .= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$key.'\',\'featured\')" title="'.$tmpTitle.'">';

				}
				$item->state = $item->published;
				$item->published = $item->featured;
				$item->featuredStatus .= strip_tags(JHTML::_('grid.published', $item, $key), '<img>');
				$item->published = $item->state;
				if (!$filter_trash)
				{
					$item->featuredStatus .= '</a>';
				}

			}
		}
		$this->assignRef('rows', $items);

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

		if (!$filter_order)
		{
			$filter_order = 'category';
		}
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$filter_trash_options[] = JHTML::_('select.option', 0, JText::_('K2_CURRENT'));
		$filter_trash_options[] = JHTML::_('select.option', 1, JText::_('K2_TRASHED'));
		$lists['trash'] = JHTML::_('select.genericlist', $filter_trash_options, 'filter_trash', '', 'value', 'text', $filter_trash);

		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
		$categoriesModel = K2Model::getInstance('Categories', 'K2Model');
		$categories_option[] = JHTML::_('select.option', 0, JText::_('K2_SELECT_CATEGORY'));
		$categories = $categoriesModel->categoriesTree(NULL, true, false);
		$categories_options = @array_merge($categories_option, $categories);
		$lists['categories'] = JHTML::_('select.genericlist', $categories_options, 'filter_category', '', 'value', 'text', $filter_category);

		$authors = $model->getItemsAuthors();
		$options = array();
		$options[] = JHTML::_('select.option', 0, JText::_('K2_NO_USER'));
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

		$filter_state_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_PUBLISHING_STATE'));
		$filter_state_options[] = JHTML::_('select.option', 1, JText::_('K2_PUBLISHED'));
		$filter_state_options[] = JHTML::_('select.option', 0, JText::_('K2_UNPUBLISHED'));
		$lists['state'] = JHTML::_('select.genericlist', $filter_state_options, 'filter_state', '', 'value', 'text', $filter_state);

		$filter_featured_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_FEATURED_STATE'));
		$filter_featured_options[] = JHTML::_('select.option', 1, JText::_('K2_FEATURED'));
		$filter_featured_options[] = JHTML::_('select.option', 0, JText::_('K2_NOT_FEATURED'));
		$lists['featured'] = JHTML::_('select.genericlist', $filter_featured_options, 'filter_featured', '', 'value', 'text', $filter_featured);

		if ($params->get('showTagFilter'))
		{
			$tagsModel = K2Model::getInstance('Tags', 'K2Model');
			$options = $tagsModel->getFilter();
			$option = new JObject();
			$option->id = 0;
			$option->name = JText::_('K2_SELECT_TAG');
			array_unshift($options, $option);
			$lists['tag'] = JHTML::_('select.genericlist', $options, 'tag', '', 'id', 'name', $tag);
		}

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$languages = JHTML::_('contentlanguage.existing', true, true);
			array_unshift($languages, JHTML::_('select.option', '', JText::_('K2_SELECT_LANGUAGE')));
			$lists['language'] = JHTML::_('select.genericlist', $languages, 'language', '', 'value', 'text', $language);
		}

		// Batch Operations
		$categoriesModel = K2Model::getInstance('Categories', 'K2Model');
		$categories = $categoriesModel->categoriesTree(null, true, false);
		array_unshift($categories, JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')));
		$lists['batchCategories'] = JHTML::_('select.genericlist', $categories, 'batchCategory', 'class="inputbox" size="8"', 'value', 'text');
		$lists['batchAccess'] = version_compare(JVERSION, '2.5', 'ge') ? JHTML::_('access.level', 'batchAccess', null, '', array(JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')))) : str_replace('size="3"', "", JHTML::_('list.accesslevel', $item));

		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			$languages = JHTML::_('contentlanguage.existing', true, true);
			array_unshift($languages, JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')));
			$lists['batchLanguage'] = JHTML::_('select.genericlist', $languages, 'batchLanguage', '', 'value', 'text', null);
		}

		$model = $this->getModel('items');
		$authors = $model->getItemsAuthors();
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED'));
		foreach ($authors as $author)
		{
			$name = $author->name;
			if ($author->block)
			{
				$name .= ' ['.JText::_('K2_USER_DISABLED').']';
			}
			$options[] = JHTML::_('select.option', $author->id, $name);
		}
		$lists['batchAuthor'] = JHTML::_('select.genericlist', $options, 'batchAuthor', '', 'value', 'text', null);
		$this->assignRef('lists', $lists);

		// Pagination
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		$this->assignRef('page', $pageNav);

		// Augment with plugin events
		$filters = array();
		$columns = array();
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeAssignFilters', array(&$filters));
		$this->assignRef('filters', $filters);
		$dispatcher->trigger('onK2BeforeAssignColumns', array(&$columns));
		$this->assignRef('columns', $columns);

		// Toolbar
		$toolbar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('K2_ITEMS'), 'k2.png');

		if ($filter_trash == 1)
		{
			JToolBarHelper::deleteList('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_ITEMS', 'remove', 'K2_DELETE');
			JToolBarHelper::custom('restore', 'publish.png', 'publish_f2.png', 'K2_RESTORE', true);
		}
		else
		{
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
			if(K2_JVERSION == '30')
			{
				JToolBarHelper::custom('featured', 'featured.png', 'featured_f2.png', 'K2_TOGGLE_FEATURED_STATE', true);
			}
			else
			{
				JToolBarHelper::custom('featured', 'default.png', 'default_f2.png', 'K2_TOGGLE_FEATURED_STATE', true);
			}
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::trash('trash');
			JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'K2_COPY', true);
			// Batch button in modal
			if (K2_JVERSION == '30')
			{
					$batchButton = '<a id="K2BatchButton" class="btn btn-small" href="#"><i class="icon-edit "></i>'.JText::_('K2_BATCH').'</a>';
			}
			else
			{
					$batchButton = '<a id="K2BatchButton" href="#"><span class="icon-32-edit" title="'.JText::_('K2_BATCH').'"></span>'.JText::_('K2_BATCH').'</a>';
			}
			$toolbar->appendButton('Custom', $batchButton);

			// Display import button for Joomla content
			if ($user->gid > 23 && !$params->get('hideImportButton'))
			{
				$buttonUrl = JURI::base().'index.php?option=com_k2&amp;view=items&amp;task=import';
				$buttonText = JText::_('K2_IMPORT_JOOMLA_CONTENT');
				if (K2_JVERSION == '30')
				{
					$button = '<a id="K2ImportContentButton" class="btn btn-small" href="'.$buttonUrl.'"><i class="icon-archive "></i>'.$buttonText.'</a>';
				}
				else
				{
					$button = '<a id="K2ImportContentButton" href="'.$buttonUrl.'"><span class="icon-32-archive" title="'.$buttonText.'"></span>'.$buttonText.'</a>';
				}
				$toolbar->appendButton('Custom', $button);
			}
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

		$template = $application->getTemplate();
		$this->assignRef('template', $template);
		$this->assignRef('filter_featured', $filter_featured);
		$this->assignRef('filter_trash', $filter_trash);
		$this->assignRef('user', $user);
		if (K2_JVERSION != '15')
		{
			$dateFormat = JText::_('K2_J16_DATE_FORMAT');
		}
		else
		{
			$dateFormat = JText::_('K2_DATE_FORMAT');
		}
		$this->assignRef('dateFormat', $dateFormat);

		$ordering = (($this->lists['order'] == 'i.ordering' || $this->lists['order'] == 'category' || ($this->filter_featured > 0 && $this->lists['order'] == 'i.featured_ordering')) && (!$this->filter_trash));
		$this->assignRef('ordering', $ordering);

		JTable::addIncludePath(JPATH_COMPONENT.'/tables');
		$table = JTable::getInstance('K2Item', 'Table');
		$this->assignRef('table', $table);

		// Joomla 3.x drag-n-drop sorting variables
		if (K2_JVERSION == '30')
		{
			if ($ordering)
			{
				$action = $this->filter_featured == 1 ? 'savefeaturedorder' : 'saveorder';
				JHtml::_('sortablelist.sortable', 'k2ItemsList', 'adminForm', strtolower($this->lists['order_Dir']), 'index.php?option=com_k2&view=items&task='.$action.'&format=raw');
			}
			$document->addScriptDeclaration('
				/* K2 */
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
