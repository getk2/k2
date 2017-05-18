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
        $document = JFactory::getDocument();

		// Attributes
		$fieldID = 'fieldID_'.md5($name);
        if (K2_JVERSION != '15')
        {
            $fieldName = $name.'[]';
            if($node->attributes()->scope)
            {
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
            if($node->attributes('scope')){
	            $scope = $node->attributes('scope');
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
        	var K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST')."';
        	var K2_REMOVE_THIS_ENTRY = '".JText::_('K2_REMOVE_THIS_ENTRY')."';
        	var K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST')."';
        ");

		// Output
        $output = '
        <div class="k2SelectorButton">
			<a data-k2-modal="iframe" class="btn" title="'.JText::_('K2_SELECT').'" href="index.php?option=com_k2&view='.$scope.'&tmpl=component&context=modalselector&output=list&fid='.$fieldID.'&fname='.$fieldName.'">
	        	<i class="fa fa-file-text-o"></i> '.JText::_('K2_SELECT').'
	        </a>
        </div>
        <ul id="'.$fieldID.'" class="k2SortableListContainer">
        ';

        if($scope == 'items')
        {
	        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
        }

        foreach ($saved as $id)
        {
			if($scope == 'items')
			{
            	$row = JTable::getInstance('K2Item', 'Table');
				$row->load($id);
				$entryName = $row->title;
			}
			if($scope == 'users')
			{
				$row = JFactory::getUser($id);
				$entryName = $row->name;
			}

            $output .= '<li class="handle"><a class="k2EntryRemove" href="#" title="'.JText::_('K2_REMOVE_THIS_ENTRY').'"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">'.$entryName.'</span><input type="hidden" name="'.$fieldName.'" value="'.$row->id.'" /></li>';
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
