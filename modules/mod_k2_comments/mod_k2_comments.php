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

if (K2_JVERSION != '15')
{
    $language = JFactory::getLanguage();
    $language->load('com_k2.dates', JPATH_ADMINISTRATOR, null, true);
}

require_once(dirname(__FILE__).'/helper.php');

// Params
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$module_usage = $params->get('module_usage', '0');

$commentAvatarWidthSelect = $params->get('commentAvatarWidthSelect', 'custom');
$commentAvatarWidth = $params->get('commentAvatarWidth', 50);

$commenterAvatarWidthSelect = $params->get('commenterAvatarWidthSelect', 'custom');
$commenterAvatarWidth = $params->get('commenterAvatarWidth', 50);

// Get component params
$componentParams = JComponentHelper::getParams('com_k2');

// User avatar for latest comments
if ($commentAvatarWidthSelect == 'inherit')
{
    $lcAvatarWidth = $componentParams->get('commenterImgWidth');
}
else
{
    $lcAvatarWidth = $commentAvatarWidth;
}

// User avatar for top commenters
if ($commenterAvatarWidthSelect == 'inherit')
{
    $tcAvatarWidth = $componentParams->get('commenterImgWidth');
}
else
{
    $tcAvatarWidth = $commenterAvatarWidth;
}

switch($module_usage)
{
    case '0' :
        $comments = modK2CommentsHelper::getLatestComments($params);
        require(JModuleHelper::getLayoutPath('mod_k2_comments', 'comments'));
        break;

    case '1' :
        $commenters = modK2CommentsHelper::getTopCommenters($params);
        require(JModuleHelper::getLayoutPath('mod_k2_comments', 'commenters'));
        break;
}
