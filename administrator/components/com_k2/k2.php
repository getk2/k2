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

$user = JFactory::getUser();
$view = JRequest::getWord('view', 'items');
$view = JString::strtolower($view);
$task = JRequest::getCmd('task');
$params = JComponentHelper::getParams('com_k2');

if($view != 'media') {
  JHTML::_('behavior.tooltip');
}

if(K2_JVERSION=='15'){
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
} else {

	JLoader::register('K2HelperPermissions', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'permissions.j16.php');
	K2HelperPermissions::checkPermissions();

	// Compatibility for gid variable
	if($user->authorise('core.admin', 'com_k2')){
		$user->gid = 1000;
	} else {
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

if(version_compare(JVERSION,'1.6.0','ge')) {
	JHtml::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}

// CSS
$document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css?v=2.7.0');
$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v=2.7.0');

K2HelperHTML::loadjQuery(true, JRequest::getCmd('view') == 'media');

// JS
if(K2_JVERSION == '30')
{
	if($view == 'item' && !$params->get('taggingSystem'))
	{
		JHtml::_('formbehavior.chosen', 'select:not(#selectedTags, #tags)');
	}
	else
	{
		JHtml::_('formbehavior.chosen', 'select');
	}

}
$document->addScriptDeclaration('

	// Set K2 version as global JS var
	K2JVersion = "'.K2_JVERSION.'";

	// Set Joomla version as body tag
	(function(){
		var addedClass = "isJ'.K2_JVERSION.' k2ViewIs'.ucfirst(JRequest::getCmd('view')).' k2TaskIs'.ucfirst(JRequest::getCmd('task')).'";
		if(document.getElementsByTagName("html")[0].className !== ""){
			document.getElementsByTagName("html")[0].className += " "+addedClass;
		} else {
			document.getElementsByTagName("html")[0].className = addedClass;
		}
	})();

');
$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v=2.7.0&amp;sitepath='.JURI::root(true).'/');

// Container CSS class definition
if(K2_JVERSION == '15'){
	$k2CSSContainerClass = ' oldJ isJ15';
} elseif(K2_JVERSION == '25'){
	$k2CSSContainerClass = ' oldJ isJ25';
} elseif(K2_JVERSION == '30'){
	$k2CSSContainerClass = ' isJ25 isJ30';
} else {
	$k2CSSContainerClass = '';
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
): ?>
<div id="k2AdminContainer" class="K2AdminView<?php echo ucfirst($view).$k2CSSContainerClass; ?>">
<?php endif;

JLoader::register('K2Controller', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('K2View', JPATH_COMPONENT.'/views/view.php');
JLoader::register('K2Model', JPATH_COMPONENT.'/models/model.php');

$controller = JRequest::getWord('view', 'items');
$controller = JString::strtolower($controller);
require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
$classname = 'K2Controller'.$controller;
$controller = new $classname();
$controller->registerTask('saveAndNew', 'save');
$controller->execute(JRequest::getWord('task'));
$controller->redirect();

if(
	$document->getType() != 'raw' &&
	JRequest::getWord('task')!='deleteAttachment' &&
	JRequest::getWord('task')!='connector' &&
	JRequest::getWord('task')!='tag' &&
	JRequest::getWord('task')!='tags' &&
	JRequest::getWord('task')!='extrafields' &&
	JRequest::getWord('task')!='download' &&
	JRequest::getWord('task')!='saveComment'
): ?>
</div>
<div id="k2AdminFooter">
	<a target="_blank" href="https://getk2.org/">K2 v2.7.0</a> | Copyright &copy; 2006-<?php echo date('Y'); ?> <a target="_blank" href="http://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
</div>

<?php

$loadUpdateService = false;
if (K2_JVERSION != '15'){
	if ($user->authorise('core.admin', 'com_k2')) $loadUpdateService = true;
} else {
	if ($user->gid > 24) $loadUpdateService = true;
}

if($loadUpdateService): ?>
<!-- K2 Update Service -->
<script type="text/javascript">
	var K2_INSTALLED_VERSION = '2.7.0';
</script>
<script type="text/javascript" src="https://getk2.org/app/update.js?t=<?php echo date('Ymd'); ?>"></script>
<?php endif; ?>

<?php endif;
