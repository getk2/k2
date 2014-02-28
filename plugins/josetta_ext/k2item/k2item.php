<?php
/**
 * @version     2.6.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// include K2Base Josetta plugin, which shares many methods and thus is used as a base class
require_once JPATH_PLUGINS . '/josetta_ext/k2item/classes/basek2plugin.php';

class plgJosetta_extK2item extends plgJosetta_extBaseK2Plugin
{

	protected $_context = 'com_k2_item';
	protected $_defaultTable = 'K2Item';

	public function onJosettaGetTypes()
	{
		$this->loadLanguages();
		$item = array(self::$this->_context => 'K2 ' . JText::_('K2_ITEMS'));
		$items[] = $item;
		return $items;
	}

	public function onJosettaLoadItem($context, $id = '')
	{

		if ((!empty($context) && ($context != $this->_context)) || (empty($id)))
		{
			return null;
		}
		$item = parent::onJosettaLoadItem($context, $id);

		// Merge introtext and fulltext
		$item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

		// Get tags
		K2Model::addIncludePath(JPATH_SITE . '/components/com_k2/models');
		JLoader::register('K2HelperUtilities', JPATH_SITE . '/components/com_k2/helpers/utilities.php');
		$model = K2Model::getInstance('Item', 'K2Model');
		$tags = $model->getItemTags($item->id);
		$tmp = array();
		foreach ($tags as $tag)
		{
			$tmp[] = $tag->name;
		}
		$item->tags = implode(', ', $tmp);

		// Get extra fields
		$extraFields = $model->getItemExtraFields($item->extra_fields);
		$html = '';
		if (count($extraFields))
		{
			$html .= '<ul>';
			foreach ($extraFields as $key => $extraField)
			{
				$html .= '<li class="type' . ucfirst($extraField->type) . ' group' . $extraField->group
					. '">
                <span class="itemExtraFieldsLabel">' . $extraField->name . ':</span>
                <span class="itemExtraFieldsValue">' . $extraField->value . '</span>
            </li>';
			}
			$html .= '</ul>';
		}
		$item->extra_fields = $html;

		// Return the item
		return $item;
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

		require_once JPATH_PLUGINS . '/josetta_ext/k2item/models/item.php';
		$k2ItemModel = new JosettaK2ModelItem();

		$savedItemId = $k2ItemModel->save($item);

		if (empty($savedItemId))
		{
			// make sure errors are displayed
			JosettaHelper::enqueueMessages($k2ItemModel->getErrors());
			return false;
		}

		return $savedItemId;

	}

}
