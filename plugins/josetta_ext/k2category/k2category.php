<?php
/**
 * @version     $Id: k2category.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// include K2Base Josetta plugin, which shares many methods and thus is used as a base class
require_once JPATH_PLUGINS . '/josetta_ext/k2item/classes/basek2plugin.php';

class plgJosetta_extK2Category extends plgJosetta_extBaseK2Plugin
{

	protected $_context = 'com_k2_category';
	protected $_defaultTable = 'K2Category';

	/**
	 * Method to build the dropdown of josetta translator screen
	 *
	 * @return array
	 *
	 */
	public function onJosettaGetTypes()
	{
		$this->loadLanguages();
		$item = array(self::$this->_context => 'K2 ' . JText::_('K2_CATEGORIES'));
		$items[] = $item;
		return $items;
	}

	/**
	 * Overriden method, to add indentation to the list of categories
	 *
	 */
	public function onJosettaLoadItems($context, $state)
	{

		if ((!empty($context) && ($context != $this->_context)))
		{
			return null;
		}

		// read data. Can't use parent, as this would slice the results
		// using limitstart and limit. K2 needs to slice later on,
		// after indenting has been done
		$items = array();
		$db = JFactory::getDbo();
		$this->_buildItemsListQuery($state);
		$db->setQuery($this->_query);
		$rawItems = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			ShlSystem_Log::logError(__METHOD__ . ': ' . $db->getErrorMsg());
			$rawItems = array();
		}

		// now indent
		if (!empty($rawItems))
		{

			// Joomla! framework menu utility used to indent
			// requires fields as parent_id instead of parent
			foreach ($rawItems as &$item)
			{
				$item->title = $item->name;
				$item->parent_id = $item->parent;
			}

			// indent cat list, for easier reading
			$items = self::indentCategories($rawItems);
			foreach ($items as &$item)
			{
				$item->name = JString::str_ireplace('<sup>|_</sup>', '', $item->treename);
			}

			// finally slice up to get the set we need
			$items = array_slice($items, $state->get('list.start'), $state->get('list.limit'));
		}

		return $items;
	}

	/**
	 *
	 * @see JosettaClassesExtensionplugin::onJosettaLoadItem()
	 */
	public function onJosettaLoadItem($context, $id = '')
	{

		if ((!empty($context) && ($context != $this->_context)) || (empty($id)))
		{
			return null;
		}

		//call the parent base class method to load the context information
		$category = parent::onJosettaLoadItem($context, $id);

		// Display the parent category name instead of the ID
		$db = JFactory::getDBO();
		$db->setQuery('SELECT name FROM #__k2_categories WHERE id = ' . (int) $category->parent);
		$category->parent = $db->loadResult();

		// Convert the meta description and meta keywords params to fields so user can translate them
		$categoryParams = new JRegistry($category->params);
		$category->metadesc = $categoryParams->get('catMetaDesc');
		$category->metakey = $categoryParams->get('catMetaKey');
		return $category;
	}

	/**
	 * Save an item after it has been translated
	 * This will be called by Josetta when a user clicks
	 * the Save button. The context is passed so
	 * that each plugin knows if it must process the data or not
	 *
	 * if $item->reference_id is empty, this is
	 * a new item, otherwise we are updating the item
	 *
	 * $item->data contains the fields entered by the user
	 * that needs to be saved
	 *
	 *@param context type
	 *@param data in form of array
	 *
	 *return table id if data is inserted
	 *
	 *return false if error occurs
	 *
	 */

	public function onJosettaSaveItem($context, $item, &$errors)
	{

		if (($context != $this->_context))
		{
			return;
		}

		// load languages for form and error messages
		$this->loadLanguages();

		// Save
		jimport('joomla.filesystem.file');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');
		require_once(JPATH_ADMINISTRATOR . '/components/com_k2/lib/class.upload.php');
		$row = JTable::getInstance('K2Category', 'Table');
		$params = JComponentHelper::getParams('com_k2');

		if (!$row->bind($item))
		{
			JosettaHelper::enqueueMessages($row->getError());
			return false;
		}

		$row->parent = (int) $row->parent;

		//$input = JRequest::get('post');
		$filter = JFilterInput::getInstance();
		$categoryParams = new JRegistry($row->params);
		$categoryParams->set('catMetaDesc', $filter->clean($item['metadesc']));
		$categoryParams->set('catMetaKey', $filter->clean($item['metakey']));
		$row->params = $categoryParams->toString();

		$isNew = ($row->id) ? false : true;

		//Trigger the finder before save event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');
		$results = $dispatcher->trigger('onFinderBeforeSave', array('com_k2.category', $row, $isNew));

		if ($params->get('xssFiltering'))
		{
			$filter = new JFilterInput(array(), array(), 1, 1, 0);
			$row->description = $filter->clean($row->description);
		}

		if (!$row->id)
		{
			$row->ordering = $row->getNextOrder('parent = ' . $row->parent . ' AND trash=0');
		}

		$savepath = JPATH_ROOT . '/media/k2/categories/';
		if ($row->image && JFile::exists($savepath . $image))
		{
			$uniqueName = uniqid() . '.jpg';
			JFile::copy($savepath . $row->image, $savepath . $uniqueName);
			$row->image = $uniqueName;
		}

		if (!$row->check())
		{
			JosettaHelper::enqueueMessages($row->getError());
			return false;
		}

		if (!$row->store())
		{
			JosettaHelper::enqueueMessages($row->getError());
			return false;
		}

		if (!$params->get('disableCompactOrdering'))
			$row->reorder('parent = ' . $row->parent . ' AND trash=0');

		if ((int) $params->get('imageMemoryLimit'))
		{
			ini_set('memory_limit', (int) $params->get('imageMemoryLimit') . 'M');
		}

		//$files = JRequest::get('files');

		$savepath = JPATH_ROOT . '/media/k2/categories/';

		// TODO: this will be renamed when used through Josetta
		//$existingImage = JRequest::getVar('existingImage');
		if (!empty($item['files']) && !empty($item['files']['image']))
		{
			if (($item['files']['image']['error'] === 0 || !empty($item['existingImage'])) && empty($item['del_image']))
			{
				if ($item['files']['image']['error'] === 0)
				{
					$image = $item['files']['image'];
				}
				else
				{
					$image = JPATH_SITE . '/' . JPath::clean($item['existingImage']);
				}

				$handle = new Upload($image);
				if ($handle->uploaded)
				{
					$handle->file_auto_rename = false;
					$handle->jpeg_quality = $params->get('imagesQuality', '85');
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $row->id;
					$handle->image_resize = true;
					$handle->image_ratio_y = true;
					$handle->image_x = $params->get('catImageWidth', '100');
					$handle->Process($savepath);
					if ($files['image']['error'] === 0)
						$handle->Clean();
				}
				else
				{
					JosettaHelper::enqueueMessages($handle->error);
					return false;
				}
				$row->image = $handle->file_dst_name;
			}
		}

		// TODO: this will be renamed when used through Josetta
		if (!empty($item['del_image']))
		{
			$currentRow = JTable::getInstance('K2Category', 'Table');
			$currentRow->load($row->id);
			if (JFile::exists(JPATH_ROOT . '/media/k2/categories/' . $currentRow->image))
			{
				JFile::delete(JPATH_ROOT . '/media/k2/categories/' . $currentRow->image);
			}
			$row->image = '';
		}

		if (!$row->store())
		{
			JosettaHelper::enqueueMessages($row->getError());
			return false;
		}

		//Trigger the finder after save event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');
		$results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.category', $row, $isNew));

		$cache = JFactory::getCache('com_k2');
		$cache->clean();

		return $row->id;

	}

	public static function indentCategories(&$rows, $root = 0)
	{

		$children = array();
		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$categories = JHTML::_('menu.treerecurse', $root, '', array(), $children);

		return $categories;
	}

}
