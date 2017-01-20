<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

if (K2_JVERSION != '15')
{
    $language = JFactory::getLanguage();
    $language->load('mod_k2.j16', JPATH_ADMINISTRATOR, null, true);
}

require_once (dirname(__FILE__).DS.'helper.php');

// Params
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$getTemplate = $params->get('getTemplate', 'Default');
$itemAuthorAvatarWidthSelect = $params->get('itemAuthorAvatarWidthSelect', 'custom');
$itemAuthorAvatarWidth = $params->get('itemAuthorAvatarWidth', 50);
$itemCustomLinkTitle = $params->get('itemCustomLinkTitle', '');
$itemCustomLinkURL = trim($params->get('itemCustomLinkURL'));
$itemCustomLinkMenuItem = $params->get('itemCustomLinkMenuItem');

if ($itemCustomLinkURL && $itemCustomLinkURL!='http://')
{
	if ($itemCustomLinkTitle=='')
	{
		if (strpos($itemCustomLinkURL, '://')!==false)
		{
			$linkParts = explode('://', $itemCustomLinkURL);
			$itemCustomLinkURL = $linkParts[1];
		}
		$itemCustomLinkTitle = $itemCustomLinkURL;
	}
}
else if ($itemCustomLinkMenuItem)
{
    $menu = JMenu::getInstance('site');
    $menuLink = $menu->getItem($itemCustomLinkMenuItem);
    if (!$itemCustomLinkTitle)
    {
        $itemCustomLinkTitle = (K2_JVERSION != '15') ? $menuLink->title : $menuLink->name;
    }
    $itemCustomLinkURL = JRoute::_('index.php?&Itemid='.$menuLink->id);
}

// Make params backwards compatible
$params->set('itemCustomLinkTitle', $itemCustomLinkTitle);
$params->set('itemCustomLinkURL', $itemCustomLinkURL);

// Get component params
$componentParams = JComponentHelper::getParams('com_k2');

// User avatar
if ($itemAuthorAvatarWidthSelect == 'inherit')
{
    $avatarWidth = $componentParams->get('userImageWidth');
}
else
{
    $avatarWidth = $itemAuthorAvatarWidth;
}

$items = modK2ContentHelper::getItems($params);

if (count($items))
{
    $file = JModuleHelper::getLayoutPath('mod_k2_content', $getTemplate.DS.'default');
    if(file_exists($file))
        require ($file);
}
