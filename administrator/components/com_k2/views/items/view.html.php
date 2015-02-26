<?php
/**
 * @version		2.7.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.view');

class K2ViewItems extends K2View
{
	function display($tpl = null)
	{
		JHTML::_('behavior.modal');
		jimport('joomla.filesystem.file');
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'i.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_trash = $mainframe->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
		$filter_featured = $mainframe->getUserStateFromRequest($option.$view.'filter_featured', 'filter_featured', -1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$tag = $mainframe->getUserStateFromRequest($option.$view.'tag', 'tag', 0, 'int');
		$language = $mainframe->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');
		$params = JComponentHelper::getParams('com_k2');

		$db = JFactory::getDBO();
		$nullDate = $db->getNullDate();
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
			// JAW modified - added rating
			if (!empty($item->rating_sum))
			{
				$item->rating = number_format(((int)$item->rating_sum / (int)$item->rating_count), 2);
			}
			else
			{
				$item->rating = 0;
			}
		}
		$this->assignRef('rows', $items);

		$lists = array();
		$lists['search'] = $search;

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

		$this->assignRef('lists', $lists);

		jimport('joomla.html.pagination');

		$pageNav = new JPagination($total, $limitstart, $limit);
		$this->assignRef('page', $pageNav);

		$filters = array();
		$columns = array();
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeAssignFilters', array(&$filters));
		$this->assignRef('filters', $filters);
		$dispatcher->trigger('onK2BeforeAssignColumns', array(&$columns));
		$this->assignRef('columns', $columns);

		JToolBarHelper::title(JText::_('K2_ITEMS'), 'k2.png');
		if ($filter_trash == 1)
		{
			JToolBarHelper::custom('restore', 'publish.png', 'publish_f2.png', 'K2_RESTORE', true);
			JToolBarHelper::deleteList('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_ITEMS', 'remove', 'K2_DELETE');
		}
		else
		{

			$params = JComponentHelper::getParams('com_k2');
			$toolbar = JToolBar::getInstance('toolbar');

			K2_JVERSION == '30' ? JToolBarHelper::custom('featured', 'featured.png', 'featured_f2.png', 'K2_TOGGLE_FEATURED_STATE', true) : JToolBarHelper::custom('featured', 'default.png', 'default_f2.png', 'K2_TOGGLE_FEATURED_STATE', true);
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::custom('move', 'move.png', 'move_f2.png', 'K2_MOVE', true);
			JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'K2_COPY', true);
			JToolBarHelper::editList();
			JToolBarHelper::addNew();
			JToolBarHelper::trash('trash');

		}

		$toolbar = JToolBar::getInstance('toolbar');
		if (K2_JVERSION != '15')
		{
			JToolBarHelper::preferences('com_k2', 550, 875, 'K2_PARAMETERS');
		}
		else
		{
			$toolbar->appendButton('Popup', 'config', 'K2_PARAMETERS', 'index.php?option=com_k2&view=settings');
		}

		// Import Joomla! content button
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

		$this->loadHelper('html');
		K2HelperHTML::subMenu();

		$template = $mainframe->getTemplate();
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

		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		$table = JTable::getInstance('K2Item', 'Table');
		$this->assignRef('table', $table);

		// Joomla! 3.0 drag-n-drop sorting variables
		if (K2_JVERSION == '30')
		{
			if ($ordering)
			{
				$action = $this->filter_featured == 1 ? 'savefeaturedorder' : 'saveorder';
				JHtml::_('sortablelist.sortable', 'k2ItemsList', 'adminForm', strtolower($this->lists['order_Dir']), 'index.php?option=com_k2&view=items&task='.$action.'&format=raw');
			}
			$document = JFactory::getDocument();
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
            }');
		}

		parent::display($tpl);
	}

	function move()
	{

		$mainframe = JFactory::getApplication();
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		$cid = JRequest::getVar('cid');

		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$rows[] = $row;
		}

		$categoriesModel = K2Model::getInstance('Categories', 'K2Model');
		$categories = $categoriesModel->categoriesTree(null, true, false);
		$lists['categories'] = JHTML::_('select.genericlist', $categories, 'category', 'class="inputbox" size="8"', 'value', 'text');

		$this->assignRef('rows', $rows);
		$this->assignRef('lists', $lists);

		JToolBarHelper::title(JText::_('K2_MOVE_ITEMS'), 'k2.png');

		JToolBarHelper::custom('saveMove', 'save.png', 'save_f2.png', 'K2_SAVE', false);
		JToolBarHelper::cancel();

		parent::display();
	}

}
