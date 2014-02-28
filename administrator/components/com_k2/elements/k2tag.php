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

class K2ElementK2Tag extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $doc = JFactory::getDocument();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
        $tag = JTable::getInstance('K2Tag', 'Table');
        if ($value)
        {
            $db = JFactory::getDBO();
            $query = "SELECT * FROM #__k2_tags WHERE name=".$db->Quote($value);
            $db->setQuery($query);
            $tag = $db->loadObject();
        }
        if (is_null($tag))
        {
            $tag = new stdClass;
            $tag->name = JText::_('K2_SELECT_A_TAG');
        }
        // Move this to main JS file
        $js = "
		function jSelectTag(id, title, object) {
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
        $link = 'index.php?option=com_k2&amp;view=tags&amp;task=element&amp;tmpl=component&amp;object='.$name;
        JHTML::_('behavior.modal', 'a.modal');
        if (K2_JVERSION == '30')
        {
            $html = '<span class="input-append">
                <input type="text" id="'.$name.'_name" value="'.htmlspecialchars($tag->name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
                <a class="modal btn" title="'.JText::_('K2_SELECT_A_TAG').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-file"></i>'.JText::_('K2_SELECT').'</a>
                <input type="hidden" class="required modal-value" id="'.$name.'_id" name="'.$fieldName.'" value="'.$value.'" />
                </span>';
        }
        else
        {
            $html = '
            <div style="float:left;">
                <input style="background:#fff;margin:3px 0;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($tag->name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            </div>
            <div class="button2-left">
                <div class="blank">
                    <a class="modal" title="'.JText::_('K2_SELECT_A_TAG').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">'.JText::_('K2_SELECT').'</a>
                </div>
            </div>
            <input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.$value.'" />
            ';
        }
        return $html;
    }

}

class JFormFieldK2Tag extends K2ElementK2Tag
{
    var $type = 'k2tag';
}

class JElementK2Tag extends K2ElementK2Tag
{
    var $_name = 'k2tag';
}
