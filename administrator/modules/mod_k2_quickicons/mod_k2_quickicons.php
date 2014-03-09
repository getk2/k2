<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

$user = JFactory::getUser();

if (K2_JVERSION != '15')
{

	if (!$user->authorise('core.manage', 'com_k2'))
	{
		return;
	}

	$language = JFactory::getLanguage();
	$language->load('mod_k2.j16', JPATH_ADMINISTRATOR);
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
$mainframe = JFactory::getApplication();
$document = JFactory::getDocument();
$user = JFactory::getUser();

// Module parameters
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$modCSSStyling = (int)$params->get('modCSSStyling', 1);
$modLogo = (int)$params->get('modLogo', 1);

// Component parameters
$componentParams = JComponentHelper::getParams('com_k2');

$onlineImageEditor = $componentParams->get('onlineImageEditor', 'splashup');

switch($onlineImageEditor)
{
	case 'splashup' :
		$onlineImageEditorLink = 'http://splashup.com/splashup/';
		break;
	case 'sumopaint' :
		$onlineImageEditorLink = 'http://www.sumopaint.com/app/';
		break;
	case 'pixlr' :
		$onlineImageEditorLink = 'http://pixlr.com/editor/';
		break;
}

// Call the modal and add some needed JS
JHTML::_('behavior.modal');

// Append CSS to the document's head
if ($modCSSStyling)
	$document->addStyleSheet(JURI::base(true).'/modules/'.$mod_name.'/tmpl/css/style.css?v=2.6.9');

// Output content with template
echo $mod_copyrights_start;
require (JModuleHelper::getLayoutPath($mod_name, 'default'));
echo $mod_copyrights_end;
