<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2textarea extends K2Element
{
    public function fetchElement($name, $value, &$node, $control_name)
    {
        // Attributes
        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            $fieldName = $name;
            if ($node->attributes()->chars) {
                $chars = $node->attributes()->chars;
            }
            if ($node->attributes()->cols) {
                $cols = $node->attributes()->cols;
            }
            if ($node->attributes()->rows) {
                $rows = $node->attributes()->rows;
            }
        } else {
            $fieldName = $control_name.'['.$name.']';
            if ($node->attributes('chars')) {
                $chars = $node->attributes('chars');
            }
            if ($node->attributes('cols')) {
                $cols = $node->attributes('cols');
            }
            if ($node->attributes('rows')) {
                $rows = $node->attributes('rows');
            }
        }
        if (!$value) {
            $value = '';
        }

        // Output
        return '<textarea name="'.$fieldName.'" rows="'.$rows.'" cols="'.$cols.'" data-k2-chars="'.$chars.'">'.$value.'</textarea>';
    }
}

class JFormFieldK2textarea extends K2ElementK2textarea
{
    public $type = 'k2textarea';
}

class JElementK2textarea extends K2ElementK2textarea
{
    public $_name = 'k2textarea';
}
