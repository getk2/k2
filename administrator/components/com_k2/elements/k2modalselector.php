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

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2modalselector extends K2Element
{
    function fetchElementValue($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();

		JHTML::_('behavior.modal', 'a.modal');

        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

		// Attributes
		$fieldID = 'fieldID_'.md5($name);
        if (K2_JVERSION != '15')
        {
            $fieldName = $name.'[]';
            if($node->attributes()->scope)){
	            $scope = $node->attributes()->scope;
            }
            else
            {
	            $scope = 'items';
            }
        }
        else
        {
            $fieldName = $control_name.'['.$name.'][]';
            if($node->attributes('scope'))){
	            $scope = $node->attributes()->scope;
            }
            else
            {
	            $scope = 'items';
            }
        }
        if(!$value)
        {
          $value = '';
        }
        $saved = array();
        if (is_string($value) && !empty($value))
        {
            $saved[] = $value;
        }
        if (is_array($value))
        {
            $saved = $value;
        }

		// JS
        $document->addScriptDeclaration("
        	var K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST = '".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."';
        	var K2_REMOVE_THIS_ENTRY = '".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."';
        	var K2_ITEM_ADDED_IN_THE_LIST = '".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."';
        ");

		// Output
        $output = '
        <div class="k2SelectorButton">
			<a class="modal btn" title="'.JText::_('K2_SELECT').'" href="index.php?option=com_k2&view='.$scope.'&tmpl=component&context=modalselector&output=list&fid='.$fieldID.'&fname='.$fieldName.'" rel="{handler:\'iframe\', size:{x:(document.documentElement.clientWidth)*0.96, y:(document.documentElement.clientHeight)*0.96}}">
	        	<i class="fa fa-file-text-o"></i> '.JText::_('K2_SELECT').'
	        </a>
        </div>
        <ul id="'.$fieldID.'" class="k2SortableListContainer">
        ';

        foreach ($saved as $id)
        {
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load($id);

            $output .= '<li class="handle"><a class="k2EntryRemove" href="#" title="'.JText::_('K2_REMOVE_THIS_ENTRY').'"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">'.$row->title.'</span><input type="hidden" name="'.$fieldName.'" value="'.$row->id.'" /></li>';
        }
        $output .= '
        </ul>
        ';

        return $output;
    }
}

class JFormFieldK2modalselector extends K2ElementK2modalselector
{
    var $type = 'k2modalselector';
}

class JElementK2modalselector extends K2ElementK2modalselector
{
    var $_name = 'k2modalselector';
}
