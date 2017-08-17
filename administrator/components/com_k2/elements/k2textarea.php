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

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2textarea extends K2Element
{
    function fetchElementValue($name, $value, &$node, $control_name)
    {
		// Attributes
        if (version_compare(JVERSION, '1.5.0', 'gt'))
        {
			$fieldName = $name;
            if($node->attributes()->chars)
            {
	            $chars = $node->attributes()->chars;
            }
            if($node->attributes()->cols)
            {
	            $cols = $node->attributes()->cols;
            }
            if($node->attributes()->rows)
            {
	            $rows = $node->attributes()->rows;
            }
        }
        else
        {
	        $fieldName = $control_name.'['.$name.']';
            if($node->attributes('chars')){
	            $chars = $node->attributes('chars');
            }
            if($node->attributes('cols')){
	            $cols = $node->attributes('cols');
            }
            if($node->attributes('rows')){
	            $rows = $node->attributes('rows');
            }
        }
        if(!$value)
        {
          $value = '';
        }

        // Output
        return '<textarea name="'.$fieldName.'" rows="'.$rows.'" cols="'.$cols.'" data-k2-chars="'.$chars.'">'.$value.'</textarea>';
    }
}

class JFormFieldK2textarea extends K2ElementK2textarea
{
    var $type = 'k2textarea';
}

class JElementK2textarea extends K2ElementK2textarea
{
    var $_name = 'k2textarea';
}
