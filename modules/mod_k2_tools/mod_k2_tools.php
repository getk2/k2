<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

if (K2_JVERSION != '15')
{
    $language = JFactory::getLanguage();
    $language->load('mod_k2.j16', JPATH_ADMINISTRATOR, null, true);
}

require_once (dirname(__FILE__).DS.'helper.php');

// Params
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$module_usage = $params->get('module_usage', 0);
$authorAvatarWidthSelect = $params->get('authorAvatarWidthSelect', 'custom');
$authorAvatarWidth = $params->get('authorAvatarWidth', 50);
$button = $params->get('button', '');
$imagebutton = $params->get('imagebutton', '');
$button_pos = $params->get('button_pos', 'left');
$button_text = $params->get('button_text', JText::_('K2_SEARCH'));
$width = intval($params->get('width', 20));
$maxlength = $width > 20 ? $width : 20;
$text = $params->get('text', JText::_('K2_SEARCH'));

// API
$document = JFactory::getDocument();
$app = JFactory::getApplication();

// Output
switch ($module_usage)
{

    case '0' :
        $months = modK2ToolsHelper::getArchive($params);
        if (count($months))
        {
            require (JModuleHelper::getLayoutPath('mod_k2_tools', 'archive'));
        }
        break;

    case '1' :
        // User avatar
        if ($authorAvatarWidthSelect == 'inherit')
        {
            $componentParams = JComponentHelper::getParams('com_k2');
            $avatarWidth = $componentParams->get('userImageWidth');
        }
        else
        {
            $avatarWidth = $authorAvatarWidth;
        }
        $authors = modK2ToolsHelper::getAuthors($params);
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'authors'));
        break;

    case '2' :
        $calendar = modK2ToolsHelper::calendar($params);
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'calendar'));
        break;

    case '3' :
        $breadcrumbs = modK2ToolsHelper::breadcrumbs($params);
        $path = $breadcrumbs[0];
        $title = $breadcrumbs[1];
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'breadcrumbs'));
        break;

    case '4' :
        $output = modK2ToolsHelper::treerecurse($params, 0, 0, true);
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'categories'));
        break;

    case '5' :
        echo modK2ToolsHelper::treeselectbox($params);
        break;

    case '6' :
        $categoryFilter = modK2ToolsHelper::getSearchCategoryFilter($params);
		$action = JRoute::_(K2HelperRoute::getSearchRoute());
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'search'));
        break;

    case '7' :
        $tags = modK2ToolsHelper::tagCloud($params);
        if (count($tags))
        {
            require (JModuleHelper::getLayoutPath('mod_k2_tools', 'tags'));
        }
        break;

    case '8' :
        $customcode = modK2ToolsHelper::renderCustomCode($params);
        require (JModuleHelper::getLayoutPath('mod_k2_tools', 'customcode'));
        break;
}
