<?php
/**
 * @version     2.8.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementHeader extends K2Element
{
    public function fetchElementValue($name, $value, &$node, $control_name)
    {
		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			return '<div class="jwHeaderContainer"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
		}
		else
		{
			return '<div class="jwHeaderContainer15"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
		}
    }

    public function fetchElementName($label, $description, &$node, $control_name, $name)
    {
        return NULL;
    }
}

class JFormFieldHeader extends K2ElementHeader
{
    var $type = 'header';
}

class JElementHeader extends K2ElementHeader
{
    var $_name = 'header';
}
