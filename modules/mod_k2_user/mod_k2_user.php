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

if (K2_JVERSION != '15')
{
    $language = JFactory::getLanguage();
    $language->load('mod_k2.j16', JPATH_ADMINISTRATOR, null, true);
	require_once JPATH_SITE.'/components/com_users/helpers/route.php';
}

require_once (dirname(__FILE__).DS.'helper.php');

$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$userGreetingText = $params->get('userGreetingText', '');
$userAvatarWidthSelect = $params->get('userAvatarWidthSelect', 'custom');
$userAvatarWidth = $params->get('userAvatarWidth', 50);

// Legacy params
$greeting = 0;

$type = modK2UserHelper::getType();
$return = modK2UserHelper::getReturnURL($params, $type);
$user = JFactory::getUser();

$componentParams = JComponentHelper::getParams('com_k2');
$K2CommentsEnabled = $componentParams->get('comments');

// User avatar
if ($userAvatarWidthSelect == 'inherit')
{
    $avatarWidth = $componentParams->get('userImageWidth');
}
else
{
    $avatarWidth = $userAvatarWidth;
}

// Load the right template
if ($user->guest)
{
    // OpenID stuff (do not edit)
    if (JPluginHelper::isEnabled('authentication', 'openid'))
    {
        $lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
        $langScript = '
			var JLanguage = {};
			JLanguage.WHAT_IS_OPENID = \''.JText::_('K2_WHAT_IS_OPENID').'\';
			JLanguage.LOGIN_WITH_OPENID = \''.JText::_('K2_LOGIN_WITH_OPENID').'\';
			JLanguage.NORMAL_LOGIN = \''.JText::_('K2_NORMAL_LOGIN').'\';
			var modlogin = 1;
		';
        $document = JFactory::getDocument();
        $document->addScriptDeclaration($langScript);
        JHTML::_('script', 'openid.js');
    }

    // Get user stuff (do not edit)
    $usersConfig = JComponentHelper::getParams('com_users');

    // Define some variables depending on Joomla! version
    $passwordFieldName = K2_JVERSION != '15' ? 'password' : 'passwd';
    $resetLink = JRoute::_((K2_JVERSION != '15') ? 'index.php?option=com_users&view=reset&Itemid='.UsersHelperRoute::getResetRoute() : 'index.php?option=com_user&view=reset');
    $remindLink = JRoute::_((K2_JVERSION != '15') ? 'index.php?option=com_users&view=remind&Itemid='.UsersHelperRoute::getRemindRoute() : 'index.php?option=com_user&view=remind');
    $registrationLink = JRoute::_((K2_JVERSION != '15') ? 'index.php?option=com_users&view=registration&Itemid='.UsersHelperRoute::getRegistrationRoute() : 'index.php?option=com_user&view=register');
    $option = K2_JVERSION != '15' ? 'com_users' : 'com_user';
    $task = K2_JVERSION != '15' ? 'user.login' : 'login';
    require (JModuleHelper::getLayoutPath('mod_k2_user', 'login'));
}
else
{
		JHTML::_('behavior.modal');

    $user->profile = modK2UserHelper::getProfile($params);
    $user->numOfComments = modK2UserHelper::countUserComments($user->id);
    $menu = modK2UserHelper::getMenu($params);
    $profileLink = JRoute::_((K2_JVERSION != '15') ? 'index.php?option=com_users&view=profile&layout=edit&Itemid='.UsersHelperRoute::getProfileRoute() : 'index.php?option=com_user&view=user&task=edit');
    $option = K2_JVERSION != '15' ? 'com_users' : 'com_user';
    $task = K2_JVERSION != '15' ? 'user.logout' : 'logout';
    require (JModuleHelper::getLayoutPath('mod_k2_user', 'userblock'));
}
