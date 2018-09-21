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

$user = JFactory::getUser();
$option = JRequest::getCmd('option');
$view = strtolower(JRequest::getWord('view', 'items'));
$task = JRequest::getCmd('task');
$params = JComponentHelper::getParams('com_k2');

if(K2_JVERSION=='15')
{
    if(($params->get('lockTags') && $user->gid<=23 && ($view=='tags' || $view=='tag')) || ($user->gid <= 23) && (
    			$view=='extrafield' ||
    			$view=='extrafields' ||
    			$view=='extrafieldsgroup' ||
    			$view=='extrafieldsgroups' ||
    			$view=='user' ||
    			($view=='users' && $task != 'element') ||
    			$view=='usergroup' ||
    			$view=='usergroups'
    		)
    	)
    	{
    		JError::raiseError( 403, JText::_('K2_ALERTNOTAUTH') );
    	}
}
else
{
	JLoader::register('K2HelperPermissions', JPATH_SITE.'/administrator/components/com_k2/helpers/permissions.php');
	K2HelperPermissions::checkPermissions();

	// Compatibility for gid variable
	if($user->authorise('core.admin', 'com_k2'))
	{
		$user->gid = 1000;
	}
	else
	{
		$user->gid = 1;
	}

    if(
    	($params->get('lockTags') && !$user->authorise('core.admin', 'com_k2') && ($view=='tags' || $view=='tag')) ||
		(!$user->authorise('core.admin', 'com_k2')) && (
			$view=='extrafield' ||
			$view=='extrafields' ||
			$view=='extrafieldsgroup' ||
			$view=='extrafieldsgroups' ||
			$view=='user' ||
			($view=='users' && $task != 'element') ||
			$view=='usergroup' ||
			$view=='usergroups'
		)
	)
	{
		JError::raiseError( 403, JText::_('K2_ALERTNOTAUTH') );
	}
}

$document = JFactory::getDocument();
K2HelperHTML::loadHeadIncludes(true, true);

// Container CSS class definition
if(K2_JVERSION == '15')
{
	$k2CSSContainerClass = ' oldJ isJ15';
}
elseif (K2_JVERSION == '25')
{
	$k2CSSContainerClass = ' oldJ isJ25';
}
elseif (K2_JVERSION == '30')
{
	$k2CSSContainerClass = ' isJ25 isJ30';
}
else
{
	$k2CSSContainerClass = '';
}

if(JRequest::getCmd('context') == "modalselector")
{
	$k2CSSContainerClass .= ' inModalSelector';
	$k2FooterClass = 'inModalSelector';
}
else
{
	$k2FooterClass = '';
}

if(
	$document->getType() != 'raw' &&
	JRequest::getWord('task')!='deleteAttachment' &&
	JRequest::getWord('task')!='connector' &&
	JRequest::getWord('task')!='tag' &&
	JRequest::getWord('task')!='tags' &&
	JRequest::getWord('task')!='extrafields' &&
	JRequest::getWord('task')!='download' &&
	JRequest::getWord('task')!='saveComment'
)
{
	$k2ComponentHeader = '<div id="k2AdminContainer" class="K2AdminView'.ucfirst($view).$k2CSSContainerClass.'">';
	$k2ComponentFooter = '
	</div>

	<div id="k2AdminFooter" class="'.$k2FooterClass.'">
		<a target="_blank" href="https://getk2.org/">K2 v'.K2_CURRENT_VERSION.K2_BUILD.'</a> | Copyright &copy; 2006-'.date('Y').' <a target="_blank" href="https://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
	</div>

	<!-- K2 Update Service -->
	<script type="text/javascript">var K2_INSTALLED_VERSION = \''.K2_CURRENT_VERSION.'\';</script>
	<script type="text/javascript" src="https://getk2.org/app/update.js?t='.date('Ymd').'"></script>
	';
}
else
{
	$k2ComponentHeader = '';
	$k2ComponentFooter = '';
}

// Output
echo $k2ComponentHeader;

JLoader::register('K2Controller', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('K2View', JPATH_COMPONENT.'/views/view.php');
JLoader::register('K2Model', JPATH_COMPONENT.'/models/model.php');

$controller = JRequest::getWord('view', 'items');
$controller = JString::strtolower($controller);
require_once(JPATH_COMPONENT.'/controllers/'.$controller.'.php');
$classname = 'K2Controller'.$controller;
$controller = new $classname();
$controller->registerTask('saveAndNew', 'save');
$controller->execute(JRequest::getWord('task'));
$controller->redirect();

echo $k2ComponentFooter;
