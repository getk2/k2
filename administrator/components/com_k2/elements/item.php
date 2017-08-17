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

// Load CSS/JS for all elements/fields
require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementItem extends K2Element
{

    function fetchElementValue($name, $value, &$node, $control_name)
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $document = JFactory::getDocument();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
        $item = JTable::getInstance('K2Item', 'Table');
        if ($value)
        {
            $item->load($value);
        }
        else
        {
            $item->title = JText::_('K2_SELECT_AN_ITEM');
        }

        $document->addScriptDeclaration("
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
        ");

        $link = 'index.php?option=com_k2&amp;view=items&amp;tmpl=component&amp;context=modalselector&amp;object='.$name;
        JHTML::_('behavior.modal', 'a.modal');
        if (K2_JVERSION == '30')
        {
            $value = (int) $value;
            if(!$value) {
              $value = '';
            }
            $class = '';
            if($node->attributes()->required) {
              $class = 'required ';
            }
            $html = '
            <span class="input-append">
	            <input type="text" id="'.$name.'_name" value="'.htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
	            <a class="modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler:\'iframe\', size:{x:(document.documentElement.clientWidth)*0.96, y:(document.documentElement.clientHeight)*0.96}}">
	            	<i class="icon-file"></i>'.JText::_('K2_SELECT').'
	            </a>
	            <input type="hidden" class="'.$class.'modal-value" id="'.$name.'_id" name="'.$fieldName.'" value="'.$value.'" />
            </span>
            ';
        }
        else
        {
            $html = '
            <div style="float:left;">
                <input style="background:#fff;margin:3px 0;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            </div>
            <div class="button2-left">
                <div class="blank">
                    <a class="modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler:\'iframe\', size:{x:(document.documentElement.clientWidth)*0.96, y:(document.documentElement.clientHeight)*0.96}}">
                    	'.JText::_('K2_SELECT').'
                    </a>
                </div>
            </div>
            <input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.( int )$value.'" />
            ';
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
