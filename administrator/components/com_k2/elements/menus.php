<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementMenus extends K2Element
{
    function fetchElement($name, $value, &$node, $control_name)
    {
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        $db = JFactory::getDbo();
        $query = "SELECT menutype, title FROM #__menu_types";
        $db->setQuery($query);
        $menus = $db->loadObjectList();
        $options = array();
        $options[] = JHTML::_('select.option', '', JText::_('K2_NONE_ONSELECTLISTS'));
        foreach ($menus as $menu)
        {
            $options[] = JHTML::_('select.option', $menu->menutype, $menu->title);
        }
        return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value);
    }
}

class JFormFieldMenus extends K2ElementMenus
{
    var $type = 'menus';
}

class JElementMenus extends K2ElementMenus
{
    var $_name = 'menus';
}
