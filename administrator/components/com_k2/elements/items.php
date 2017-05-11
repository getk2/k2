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

class K2ElementItems extends K2Element
{
    function fetchElementValue($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();

        K2HelperHTML::loadHeadIncludes(true, true, false, true);

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
            $attribute = K2_JVERSION == '25' ? $node->getAttribute('multiple') : $node->attributes()->multiple;
            if (!$attribute)
            {
                $fieldName .= '[]';
            }
            $image = JURI::root(true).'/administrator/templates/'.$mainframe->getTemplate().'/images/admin/publish_x.png';
        }
        else
        {
            $fieldName = $control_name.'['.$name.'][]';
            $image = JURI::root(true).'/administrator/images/publish_x.png';
        }

        $document->addScriptDeclaration("
			function jSelectItem(id, title, object) {
				var exists = false;
				\$K2('#itemsList input').each(function(){
					if(\$K2(this).val()==id){
						\$K2().k2Alert('".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."'.replace('ITEM_NAME_HERE', title), 3000);
						exists = true;
					}
				});
				if(!exists){
					var container = \$K2('<li/>').appendTo(\$K2('#itemsList'));
					var img = \$K2('<img/>',{'class':'remove', src:'".$image."'}).appendTo(container);
					img.click(function(){\$K2(this).parent().remove();});
					var span = \$K2('<span/>',{'class':'handle'}).html(title).appendTo(container);
					var input = \$K2('<input/>',{value:id, type:'hidden', name:'".$fieldName."'}).appendTo(container);
					var div = \$K2('<div/>',{style:'clear:both;'}).appendTo(container);
					\$K2('#itemsList').sortable('refresh');
					\$K2().k2Alert('".JText::_('K2_ITEM_ADDED_IN_THE_LIST')."'.replace('ITEM_NAME_HERE', title), 1000);
				}
			}

			\$K2(document).ready(function(){
				\$K2('#itemsList').sortable({
					containment: '#itemsList',
					items: 'li',
					handle: 'span.handle'
				});
				\$K2('#itemsList .remove').click(function(){
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

        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
        $output = '<div style="clear:both"></div><ul id="itemsList">';
        foreach ($current as $id)
        {
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load($id);
            $output .= '
			<li>
				<img class="remove" src="'.$image.'" alt="'.JText::_('K2_REMOVE_ENTRY_FROM_LIST').'" />
				<span class="handle">'.$row->title.'</span>
				<input type="hidden" value="'.$row->id.'" name="'.$fieldName.'" />
				<span style="clear:both;"></span>
			</li>
			';
        }
        $output .= '</ul>';
        return $output;
    }
}

class JFormFieldItems extends K2ElementItems
{
    var $type = 'items';
}

class JElementItems extends K2ElementItems
{
    var $_name = 'items';
}
