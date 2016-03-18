<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementCategoriesLatest extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        JHTML::_('behavior.modal');

        $params = JComponentHelper::getParams('com_k2');

        $document = JFactory::getDocument();

        if (version_compare(JVERSION, '1.6.0', 'ge'))
        {
            JHtml::_('behavior.framework');
        }
        else
        {
            JHTML::_('behavior.mootools');
        }
        K2HelperHTML::loadjQuery();
        
        $mainframe = JFactory::getApplication();

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

        $js = "
		function jSelectCategory(id, title, object) {
			var exists = false;
			\$K2('#categoriesList input').each(function(){
					if(\$K2(this).val()==id){
						alert('".JText::_('K2_THE_SELECTED_CATEGORY_IS_ALREADY_IN_THE_LIST', true)."');
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
				alert('".JText::_('K2_CATEGORY_ADDED_IN_THE_LIST', true)."');
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
		";

        $document->addScriptDeclaration($js);
        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.modules.css?v=2.7.1');

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
		<div class="button2-left">
			<div class="blank">
				<a class="modal btn" title="'.JText::_('K2_CLICK_TO_SELECT_ONE_OR_MORE_CATEGORIES').'"  href="index.php?option=com_k2&view=categories&task=element&tmpl=component" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">'.JText::_('K2_CLICK_TO_SELECT_ONE_OR_MORE_CATEGORIES').'</a>
			</div>
		</div>
		<div style="clear:both;"></div>
		';

        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');

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
				<span style="clear:both;"></span>
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
