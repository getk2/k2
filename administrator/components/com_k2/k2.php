<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_k2');
$user = JFactory::getUser();

$option = JRequest::getCmd('option');
$view = JRequest::getCmd('view', 'items');
$task = JRequest::getCmd('task');
$tmpl = JRequest::getCmd('tmpl');
$context = JRequest::getCmd('context');

if (K2_JVERSION == '15') {
    if (
        ($params->get('lockTags') && $user->gid<=23 && ($view=='tags' || $view=='tag')) ||
        ($user->gid <= 23) && (
            $view=='extrafield' ||
            $view=='extrafields' ||
            $view=='extrafieldsgroup' ||
            $view=='extrafieldsgroups' ||
            $view=='user' ||
            ($view=='users' && $context != 'modalselector') ||
            $view=='usergroup' ||
            $view=='usergroups'
        )
    ) {
        JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
    }
} else {
    JLoader::register('K2HelperPermissions', JPATH_SITE.'/administrator/components/com_k2/helpers/permissions.php');
    K2HelperPermissions::checkPermissions();

    // Compatibility for gid variable
    if ($user->authorise('core.admin', 'com_k2')) {
        $user->gid = 1000;
    } else {
        $user->gid = 1;
    }

    if (
        ($params->get('lockTags') && !$user->authorise('core.admin', 'com_k2') && ($view=='tags' || $view=='tag')) ||
        (!$user->authorise('core.admin', 'com_k2')) && (
            $view=='extrafield' ||
            $view=='extrafields' ||
            $view=='extrafieldsgroup' ||
            $view=='extrafieldsgroups' ||
            $view=='user' ||
            ($view=='users' && $context != 'modalselector') ||
            $view=='usergroup' ||
            $view=='usergroups'
        )
    ) {
        JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
    }
}

$document = JFactory::getDocument();

$document->setMetadata('theme-color', '#10223e');

K2HelperHTML::loadHeadIncludes(true, true);

// Container CSS class definition
if (K2_JVERSION == '15') {
    $k2CSSContainerClass = ' isJ15'; // oldJ isJ15
} elseif (K2_JVERSION == '25') {
    $k2CSSContainerClass = ' isJ25'; // oldJ isJ25
} elseif (K2_JVERSION == '30') {
    $k2CSSContainerClass = ' isJ30'; // isJ25 isJ30
} else {
    $k2CSSContainerClass = '';
}

if (JRequest::getCmd('context') == "modalselector" || ($view == 'media' && $tmpl == 'component') || $view == 'settings') {
    $k2CSSContainerClass .= ' inModalSelector';
    $k2FooterClass = 'inModalSelector';
} else {
    $k2FooterClass = '';
}

$editForms = array('item', 'category', 'tag', 'user', 'usergroup', 'extrafield', 'extrafieldsgroup');
if (in_array($view, $editForms)) {
    $k2CSSContainerClass .= ' isEditForm';
}

if (
    $document->getType() != 'raw' &&
    $task != 'deleteAttachment' &&
    $task != 'connector' &&
    $task != 'tag' &&
    $task != 'tags' &&
    $task != 'extraFields' &&
    $task != 'download' &&
    $task != 'saveComment' &&
    $context != 'ajax'
) {
    $k2ComponentHeader = '
    <div id="k2AdminContainer" class="K2AdminView'.ucfirst($view).$k2CSSContainerClass.'">
        <div id="k2Sidebar" style="visibility:hidden;">
            <button aria-expanded="false" aria-controls="menu" id="k2ui-menu-control">&#8801;</button>
            '.K2HelperHTML::sidebarMenu().'
            <div id="k2Copyrights">
                <a target="_blank" href="https://getk2.org/">K2 v'.K2_CURRENT_VERSION.'</a>
                <div>
                    Copyright &copy; 2006-'.date('Y').' <a target="_blank" href="https://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
                </div>
            </div>
        </div>
        <div id="k2ContentView">
    ';
    $k2ComponentFooter = '
            <div class="k2clr"></div>
        </div>
        '.K2HelperHTML::mobileMenu().'
    </div>

    <!-- K2 Update Service -->
    <script type="text/javascript">var K2_INSTALLED_VERSION = \''.K2_CURRENT_VERSION.'\';</script>
    <script type="text/javascript" src="https://getk2.org/app/update.js?t='.date('Ymd_H').'"></script>
    ';
} else {
    $k2ComponentHeader = '';
    $k2ComponentFooter = '';
}

// Output
echo $k2ComponentHeader;

JLoader::register('K2Controller', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('K2View', JPATH_COMPONENT.'/views/view.php');
JLoader::register('K2Model', JPATH_COMPONENT.'/models/model.php');

$controller = strtolower($view);
require_once(JPATH_COMPONENT.'/controllers/'.$controller.'.php');
$classname = 'K2Controller'.ucfirst($controller);
$controller = new $classname();
$controller->registerTask('saveAndNew', 'save');
$controller->execute($task);
$controller->redirect();

echo $k2ComponentFooter;
