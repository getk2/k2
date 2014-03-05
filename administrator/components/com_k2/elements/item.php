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

class K2ElementItem extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $doc = JFactory::getDocument();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
        $item = JTable::getInstance('K2Item', 'Table');
        if ($value)
        {
            $item->load($value);
        }
        else
        {
            $item->title = JText::_('K2_SELECT_AN_ITEM');
        }

        $js = "
		function jSelectItem(id, title, object) {
			document.getElementById('".$name."' + '_id').value = id;
			document.getElementById('".$name."' + '_name').value = title;
			if(typeof(window.parent.SqueezeBox.close=='function')){
				window.parent.SqueezeBox.close();
			}
			else {
				document.getElementById('sbox-window').close();
			}
		}
		";
        $doc->addScriptDeclaration($js);
        $link = 'index.php?option=com_k2&amp;view=items&amp;task=element&amp;tmpl=component&amp;object='.$name;
        JHTML::_('behavior.modal', 'a.modal');
        if (K2_JVERSION == '30')
        {
            $html = '<span class="input-append">
            <input type="text" id="'.$name.'_name" value="'.htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            <a class="modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-file"></i>'.JText::_('K2_SELECT').'</a>
            <input type="hidden" class="required modal-value" id="'.$name.'_id" name="'.$fieldName.'" value="'.( int )$value.'" />
            </span>';
        }
        else
        {
            $html = '
            <div style="float:left;">
                <input style="background:#fff;margin:3px 0;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            </div>
            <div class="button2-left">
                <div class="blank">
                    <a class="modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">'.JText::_('K2_SELECT').'</a>
                </div>
            </div>
            <input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.( int )$value.'" />';
        }

        return $html;
    }

}

class JFormFieldItem extends K2ElementItem
{
    var $type = 'item';
}

class JElementItem extends K2ElementItem
{
    var $_name = 'item';
}
