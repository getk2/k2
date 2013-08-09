<?php
/**
 * @version		$Id: k2user.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2User extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $doc = JFactory::getDocument();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        if ($value)
        {
            $user = JFactory::getUser($value);
        }
        else
        {
            $user = new stdClass;
            $user->name = JText::_('K2_SELECT_A_USER');
        }
        // Move this to main JS file
        $js = "
		function jSelectUser(id, title, object) {
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
        $link = 'index.php?option=com_k2&amp;view=users&amp;task=element&amp;tmpl=component&amp;object='.$name;
        JHTML::_('behavior.modal', 'a.modal');
        if (K2_JVERSION == '30')
        {
            $html = '<span class="input-append">
            <input type="text" id="'.$name.'_name" value="'.htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            <a class="modal btn" title="'.JText::_('K2_SELECT_A_USER').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-file"></i>'.JText::_('K2_SELECT').'</a>
            <input type="hidden" class="required modal-value" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />
            </span>';
        }
        else
        {
            $html = '
            <div style="float:left;">
                <input style="background:#fff;margin:3px 0;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            </div>
            <div class="button2-left">
                <div class="blank">
                    <a class="modal" title="'.JText::_('K2_SELECT_A_USER').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">'.JText::_('K2_SELECT').'</a>
                </div>
            </div>
            <input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />
            ';
        }
        return $html;
    }

}

class JFormFieldK2User extends K2ElementK2User
{
    var $type = 'k2user';
}

class JElementK2User extends K2ElementK2User
{
    var $_name = 'k2user';
}
