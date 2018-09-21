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

class K2ElementModuleTemplate extends K2Element
{
    function fetchElement($name, $value, &$node, $control_name)
    {
        jimport('joomla.filesystem.folder');
        if (K2_JVERSION != '15')
        {
            $moduleName = $node->attributes()->modulename;
        }
        else
        {
            $moduleName = $node->_attributes['modulename'];
        }
        $moduleTemplatesPath = JPATH_SITE.'/modules/'.$moduleName.'/tmpl';
        $moduleTemplatesFolders = JFolder::folders($moduleTemplatesPath);

        $db = JFactory::getDbo();
        if (K2_JVERSION != '15')
        {
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
        }
        else
        {
            $query = "SELECT template FROM #__templates_menu WHERE client_id = 0 AND menuid = 0";
        }
        $db->setQuery($query);
        $defaultemplate = $db->loadResult();
        $templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/'.$moduleName;

        if (JFolder::exists($templatePath))
        {
            $templateFolders = JFolder::folders($templatePath);
            $folders = @array_merge($templateFolders, $moduleTemplatesFolders);
            $folders = @array_unique($folders);
        }
        else
        {
            $folders = $moduleTemplatesFolders;
        }

        $exclude = 'Default';
        $options = array();

        foreach ($folders as $folder)
        {
            if (preg_match(chr(1).$exclude.chr(1), $folder))
            {
                continue;
            }
            $options[] = JHTML::_('select.option', $folder, $folder);
        }

        array_unshift($options, JHTML::_('select.option', 'Default', '-- '.JText::_('K2_USE_DEFAULT').' --'));

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
        }
        else
        {
            $fieldName = $control_name.'['.$name.']';
        }

        return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value, $control_name.$name);
    }
}

class JFormFieldModuleTemplate extends K2ElementModuleTemplate
{
    var $type = 'moduletemplate';
}

class JElementModuleTemplate extends K2ElementModuleTemplate
{
    var $_name = 'moduletemplate';
}
