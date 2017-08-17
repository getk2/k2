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

$user = JFactory::getUser();

if (K2_JVERSION != '15')
{
	if (!$user->authorise('core.manage', 'com_k2'))
	{
		return;
	}
	$language = JFactory::getLanguage();
	$language->load('com_k2.dates', JPATH_ADMINISTRATOR);
	if ($user->authorise('core.admin', 'com_k2'))
	{
		$user->gid = 1000;
	}
	else
	{
		$user->gid = 1;
	}
}

// JoomlaWorks reference parameters
$mod_name = "mod_k2_quickicons";
$mod_copyrights_start = "\n\n<!-- JoomlaWorks \"K2 QuickIcons\" Module starts here -->\n";
$mod_copyrights_end = "\n<!-- JoomlaWorks \"K2 QuickIcons\" Module ends here -->\n\n";

// API
$application = JFactory::getApplication();
$document = JFactory::getDocument();
$user = JFactory::getUser();

// Module parameters
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$modCSSStyling = (int)$params->get('modCSSStyling', 1);
$modLogo = (int)$params->get('modLogo', 1);

// Component parameters
$componentParams = JComponentHelper::getParams('com_k2');

$onlineImageEditor = $componentParams->get('onlineImageEditor', 'picozu');

switch($onlineImageEditor)
{
	default:
	case 'picozu':
		$onlineImageEditorName = JText::_('K2_IMG_EDITOR_PICOZU');
		$onlineImageEditorLink = 'https://www.picozu.com/editor/';
		break;
	case 'gravit':
		$onlineImageEditorName = JText::_('K2_IMG_EDITOR_GRAVIT');
		$onlineImageEditorLink = 'https://app.designer.io/';
		break;
}

// Load CSS & JS
K2HelperHTML::loadHeadIncludes(true, false, true, false);
if ($modCSSStyling)
{
	$document->addStyleSheet(JURI::base(true).'/modules/'.$mod_name.'/tmpl/css/style.css?v='.K2_CURRENT_VERSION);
}

// Output content with template
echo $mod_copyrights_start;
require(JModuleHelper::getLayoutPath($mod_name, 'default'));
echo $mod_copyrights_end;
