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

class K2ElementHeader extends K2Element
{
    public function fetchElement($name, $value, &$node, $control_name)
    {
        $additionalCssClass = '';
        if (version_compare(JVERSION, '2.5.0', 'ge')) {
            if ($node->attributes()->class) {
                $additionalCssClass = ' '.$node->attributes()->class;
            }
        } else {
            if ($node->attributes('class')) {
                $additionalCssClass = ' '.$node->attributes('class');
            }
        }

        if (version_compare(JVERSION, '2.5.0', 'ge')) {
            return '<div class="jwHeaderContainer'.$additionalCssClass.'"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
        } else {
            return '<div class="jwHeaderContainer15'.$additionalCssClass.'"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
        }
    }

    public function fetchTooltip($label, $description, &$node, $control_name, $name)
    {
        return null;
    }
}

class JFormFieldHeader extends K2ElementHeader
{
    public $type = 'header';
}

class JElementHeader extends K2ElementHeader
{
    public $_name = 'header';
}
