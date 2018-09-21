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

if (K2_JVERSION != '15') {
    $language = JFactory::getLanguage();
    $language->load('com_k2.dates', JPATH_ADMINISTRATOR, null, true);
}

require_once(dirname(__FILE__).'/helper.php');

// Params
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$getTemplate = $params->get('getTemplate', 'Default');
$itemAuthorAvatarWidthSelect = $params->get('itemAuthorAvatarWidthSelect', 'custom');
$itemAuthorAvatarWidth = $params->get('itemAuthorAvatarWidth', 50);
$itemCustomLinkTitle = $params->get('itemCustomLinkTitle', '');
$itemCustomLinkURL = trim($params->get('itemCustomLinkURL'));
$itemCustomLinkMenuItem = $params->get('itemCustomLinkMenuItem');

if ($itemCustomLinkURL && ($itemCustomLinkURL!='http://' || $itemCustomLinkURL!='https://')) {
    if ($itemCustomLinkTitle=='') {
        if (strpos($itemCustomLinkURL, '://')!==false) {
            $linkParts = explode('://', $itemCustomLinkURL);
            $itemCustomLinkURL = $linkParts[1];
        }
        $itemCustomLinkTitle = $itemCustomLinkURL;
    }
} elseif ($itemCustomLinkMenuItem) {
    $menu = JMenu::getInstance('site');
    $menuLink = $menu->getItem($itemCustomLinkMenuItem);
    if (!$itemCustomLinkTitle) {
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
if ($itemAuthorAvatarWidthSelect == 'inherit') {
    $avatarWidth = $componentParams->get('userImageWidth');
} else {
    $avatarWidth = $itemAuthorAvatarWidth;
}

$items = modK2ContentHelper::getItems($params);

if (count($items)) {
    require(JModuleHelper::getLayoutPath('mod_k2_content', $getTemplate.'/default'));
}
