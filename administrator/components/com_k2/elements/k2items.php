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

class K2ElementK2Items extends K2Element
{
    function fetchElementValue($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();

		JHTML::_('behavior.modal', 'a.modal');

        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
            $attribute = K2_JVERSION == '25' ? $node->getAttribute('multiple') : $node->attributes()->multiple;
            if (!$attribute)
            {
                $fieldName .= '[]';
            }
        }
        else
        {
            $fieldName = $control_name.'['.$name.'][]';
        }

        $document->addScriptDeclaration("
			function jSelectItem(id, title, object) {
				var exists = false;
				\$K2('#k2SelectorItemList input').each(function(){
					if(\$K2(this).val()==id){
						\$K2().k2Alert('".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."'.replace('ITEM_NAME_HERE', title), 3000);
						exists = true;
					}
				});
				if(!exists){
					var entry = '<li class=\"handle\"><a class=\"k2EntryRemove\" href=\"#\" title=\"".JText::_('K2_REMOVE_THIS_ENTRY')."\"><i class=\"fa fa-trash-o\"></i></a><span class=\"k2EntryText\">'+title+'</span><input type=\"hidden\" name=\"".$fieldName."\" value=\"'+id+'\" /></li>';
					\$K2('#k2SelectorItemList').append(entry).sortable('refresh');
					\$K2().k2Alert('".JText::_('K2_ITEM_ADDED_IN_THE_LIST')."'.replace('ITEM_NAME_HERE', title), 1000);
				}
			}

			\$K2(document).ready(function(){
				\$K2('#k2SelectorItemList').sortable();
				\$K2('#k2SelectorItemList .k2EntryRemove').click(function(e){
					e.preventDefault();
					\$K2(this).parent().remove();
				});
			});
        ");

        $current = array();
        if (is_string($value) && !empty($value))
        {
            $current[] = $value;
        }
        if (is_array($value))
        {
            $current = $value;
        }
        $output = '
        <div class="k2SelectorButton">
			<a class="modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="index.php?option=com_k2&amp;view=items&amp;tmpl=component&amp;context=modalselector&amp;object='.$name.'" rel="{handler:\'iframe\', size:{x:(document.documentElement.clientWidth)*0.96, y:(document.documentElement.clientHeight)*0.96}}">
	        	<i class="fa fa-file-text-o"></i> '.JText::_('K2_SELECT').'
	        </a>
        </div>
        <ul id="k2SelectorItemList" class="k2SelectorContainer">';
        foreach ($current as $id)
        {
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load($id);

            $output .= '<li class="handle"><a class="k2EntryRemove" href="#" title="'.JText::_('K2_REMOVE_THIS_ENTRY').'"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">'.$row->title.'</span><input type="hidden" name="'.$fieldName.'" value="'.$row->id.'" /></li>';
        }
        $output .= '</ul><div class="k2clr"></div>';

        return $output;
    }
}

class JFormFieldItems extends K2ElementK2Items
{
    var $type = 'k2items';
}

class JElementItems extends K2ElementK2Items
{
    var $_name = 'k2items';
}
