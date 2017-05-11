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

class K2ElementCategoriesLatest extends K2Element
{

    function fetchElementValue($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();

        JHTML::_('behavior.modal');

        K2HelperHTML::loadHeadIncludes(true, true, false, true);

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
            if (!$node->attributes()->multiple)
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

		// JS
        $document->addScriptDeclaration("
			function jSelectCategory(id, title, object) {
				var exists = false;
				\$K2('#categoriesList input').each(function(){
					if(\$K2(this).val()==id){
						\$K2().k2Alert('".JText::_('K2_THE_SELECTED_CATEGORY_IS_ALREADY_IN_THE_LIST')."', 3000);
						exists = true;
					}
				});
				if(!exists){
					var container = \$K2('<li/>').appendTo(\$K2('#categoriesList'));
					var img = \$K2('<img/>',{'class':'remove', src:'".$image."'}).appendTo(container);
					img.click(function(){\$K2(this).parent().remove();});
					var span = \$K2('<span/>',{'class':'handle'}).html(title).appendTo(container);
					var input = \$K2('<input/>',{value:id, type:'hidden', name:'".$fieldName."'}).appendTo(container);
					var div = \$K2('<div/>',{style:'clear:both;'}).appendTo(container);
					\$K2('#categoriesList').sortable('refresh');
					\$K2().k2Alert('".JText::_('K2_CATEGORY_ADDED_IN_THE_LIST')."', 1000);
				}
			}

			\$K2(document).ready(function(){
				\$K2('#categoriesList').sortable({
					containment: '#categoriesList',
					items: 'li',
					handle: 'span.handle'
				});
				\$K2('#categoriesList .remove').click(function(){
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

        $output = '
		<div class="button2-left">
			<div class="blank">
				<a class="modal btn" title="'.JText::_('K2_CLICK_TO_SELECT_ONE_OR_MORE_CATEGORIES').'" href="index.php?option=com_k2&view=categories&tmpl=component&context=modalselector" rel="{handler:\'iframe\', size:{x:(document.documentElement.clientWidth)*0.96, y:(document.documentElement.clientHeight)*0.96}}">'.JText::_('K2_CLICK_TO_SELECT_ONE_OR_MORE_CATEGORIES').'</a>
			</div>
		</div>
		<div class="k2clr"></div>
		';
        $output .= '<ul id="categoriesList">';
        foreach ($current as $id)
        {
            $row = JTable::getInstance('K2Category', 'Table');
            $row->load($id);
            $output .= '
			<li>
				<img class="remove" src="'.$image.'" />
				<span class="handle">'.$row->name.'</span>
				<input type="hidden" value="'.$row->id.'" name="'.$fieldName.'" />
				<span class="k2clr"></span>
			</li>
			';
        }
        $output .= '</ul>';
        return $output;
    }
}

class JFormFieldCategoriesLatest extends K2ElementCategoriesLatest
{
    var $type = 'categorieslatest';
}

class JElementCategoriesLatest extends K2ElementCategoriesLatest
{
    var $_name = 'categorieslatest';
}
