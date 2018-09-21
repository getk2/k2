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

if (K2_JVERSION != '15')
{
    $user = JFactory::getUser();
    if ($user->authorise('core.admin', 'com_k2'))
    {
        $user->gid = 1000;
    }
    else
    {
        $user->gid = 1;
    }
}

JLoader::register('K2Controller', JPATH_COMPONENT.'/controllers/controller.php');
JLoader::register('K2View', JPATH_COMPONENT_ADMINISTRATOR.'/views/view.php');
JLoader::register('K2Model', JPATH_COMPONENT_ADMINISTRATOR.'/models/model.php');

JLoader::register('K2HelperRoute', JPATH_COMPONENT.'/helpers/route.php');
JLoader::register('K2HelperPermissions', JPATH_COMPONENT.'/helpers/permissions.php');
JLoader::register('K2HelperUtilities', JPATH_COMPONENT.'/helpers/utilities.php');

K2HelperPermissions::setPermissions();
K2HelperPermissions::checkPermissions();

$controller = JRequest::getWord('view', 'itemlist');
$task = JRequest::getWord('task');

if ($controller == 'media')
{
    $controller = 'item';
    if ($task != 'connector')
    {
        $task = 'media';
    }
}

if ($controller == 'users')
{
    $controller = 'item';
    $task = 'users';
}

jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');

if (JFile::exists(JPATH_COMPONENT.'/controllers/'.$controller.'.php'))
{
    $classname = 'K2Controller'.$controller;
    if(!class_exists($classname))
        require_once(JPATH_COMPONENT.'/controllers/'.$controller.'.php');
    $controller = new $classname();
    $controller->execute($task);
    $controller->redirect();
}
else
{
    JError::raiseError(404, JText::_('K2_NOT_FOUND'));
}

if (JRequest::getCmd('format') != 'json')
{
    echo "\n<!-- JoomlaWorks \"K2\" (v".K2_CURRENT_VERSION.") | Learn more about K2 at http://getk2.org -->\n\n";
}
