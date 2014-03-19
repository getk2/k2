<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementUsers extends K2Element
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
            if (!$node->attributes('multiple'))
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
		function jSelectUser(id, title, object) {
			var exists = false;
			\$K2('#usersList input').each(function(){
					if(\$K2(this).val()==id){
						alert('".JText::_('K2_THE_SELECTED_USER_IS_ALREADY_IN_THE_LIST', true)."');
						exists = true;
					}
			});
			if(!exists){
				var container = \$K2('<li/>').appendTo(\$K2('#usersList'));
				var img = \$K2('<img/>',{'class':'remove', src:'".$image."'}).appendTo(container);
				img.click(function(){\$K2(this).parent().remove();});
				var span = \$K2('<span/>',{'class':'handle'}).html(title).appendTo(container);
				var input = \$K2('<input/>',{value:id, type:'hidden', name:'".$fieldName."'}).appendTo(container);
				var div = \$K2('<div/>',{style:'clear:both;'}).appendTo(container);
				\$K2('#usersList').sortable('refresh');
				alert('".JText::_('K2_USER_ADDED_IN_THE_LIST', true)."');
			}
		}

		\$K2(document).ready(function(){
			\$K2('#usersList').sortable({
				containment: '#usersList',
				items: 'li',
				handle: 'span.handle'
			});
			\$K2('#usersList .remove').click(function(){
				\$K2(this).parent().remove();
			});
		});
		";

        $document->addScriptDeclaration($js);
        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.modules.css?v=2.6.9');

        $current = array();
        if (is_string($value) && !empty($value))
        {
            $current[] = $value;
        }
        if (is_array($value))
        {
            $current = $value;
        }

        $output = '<ul id="usersList">';
        foreach ($current as $id)
        {
            $row = JFactory::getUser($id);
            $output .= '
			<li>
				<img class="remove" src="'.$image.'" alt="'.JText::_('K2_REMOVE_ENTRY_FROM_LIST').'" />
				<span class="handle">'.$row->name.'</span>
				<input type="hidden" value="'.$row->id.'" name="'.$fieldName.'"/>
				<div style="clear:both;"></div>
			</li>
			';
        }
        $output .= '</ul>';
        return $output;
    }

}

class JFormFieldUsers extends K2ElementUsers
{
    var $type = 'users';
}

class JElementUsers extends K2ElementUsers
{
    var $_name = 'users';
}
