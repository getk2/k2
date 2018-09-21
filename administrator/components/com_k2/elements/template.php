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

class K2ElementTemplate extends K2Element
{
    public function fetchElement($name, $value, &$node, $control_name)
    {
        jimport('joomla.filesystem.folder');
        $application = JFactory::getApplication();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        $componentPath = JPATH_SITE.'/components/com_k2/templates';
        $componentFolders = JFolder::folders($componentPath);
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

        if (JFolder::exists(JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates'))
        {
            $templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates';
        }
        else
        {
            $templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2';
        }

        if (JFolder::exists($templatePath))
        {
            $templateFolders = JFolder::folders($templatePath);
            $folders = @array_merge($templateFolders, $componentFolders);
            $folders = @array_unique($folders);
        }
        else
        {
            $folders = $componentFolders;
        }

        $exclude = 'default';
        $options = array();
        foreach ($folders as $folder)
        {
            if (preg_match(chr(1).$exclude.chr(1), $folder))
            {
                continue;
            }
            $options[] = JHTML::_('select.option', $folder, $folder);
        }

        array_unshift($options, JHTML::_('select.option', '', '-- '.JText::_('K2_USE_DEFAULT').' --'));

        return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value, $control_name.$name);
    }
}

class JFormFieldTemplate extends K2ElementTemplate
{
    var $type = 'template';
}

class JElementTemplate extends K2ElementTemplate
{
    var $_name = 'template';
}
