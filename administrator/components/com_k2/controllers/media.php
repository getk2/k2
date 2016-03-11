<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

class K2ControllerMedia extends K2Controller
{

	public function display($cachable = false, $urlparams = array())
	{
		JRequest::setVar('view', 'media');
		parent::display();
	}

	function connector()
	{
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_media');
		$root = $params->get('file_path', 'media');
		$folder = JRequest::getVar('folder', $root, 'default', 'path');
		$type = JRequest::getCmd('type', 'video');
		if (JString::trim($folder) == "")
		{
			$folder = $root;
		}
		else
		{
			// Ensure that we are always below the root directory
			if (strpos($folder, $root) !== 0)
			{
				$folder = $root;
			}
		}

		// Disable debug
		JRequest::setVar('debug', false);

		$url = JURI::root(true).'/'.$folder;
		$path = JPATH_SITE.DS.JPath::clean($folder);

		JPath::check($path);
		include_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'elfinder'.DS.'elFinderConnector.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'elfinder'.DS.'elFinder.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'elfinder'.DS.'elFinderVolumeDriver.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'elfinder'.DS.'elFinderVolumeLocalFileSystem.class.php';
		function access($attr, $path, $data, $volume)
		{
			$mainframe = JFactory::getApplication();
			// Hide PHP files.
			$ext = strtolower(JFile::getExt(basename($path)));
			if ($ext == 'php')
			{
				return true;
			}

			// Hide files and folders starting with .
			if (strpos(basename($path), '.') === 0 && $attr == 'hidden')
			{
				return true;
			}
			// Read only access for front-end. Full access for administration section.
			switch($attr)
			{
				case 'read' :
					return true;
					break;
				case 'write' :
					return ($mainframe->isSite()) ? false : true;
					break;
				case 'locked' :
					return ($mainframe->isSite()) ? true : false;
					break;
				case 'hidden' :
					return false;
					break;
			}

		}

		if ($mainframe->isAdmin())
		{
			$permissions = array('read' => true, 'write' => true);
		}
		else
		{
			$permissions = array('read' => true, 'write' => false);
		}
		$options = array('debug' => false, 'roots' => array( array('driver' => 'LocalFileSystem', 'path' => $path, 'URL' => $url, 'accessControl' => 'access', 'defaults' => $permissions, 'uploadAllow' => array('image', 'video', 'audio', 'text/plain', 'text/html', 'application/json', 'application/pdf', 'application/zip', 'application/x-7z-compressed', 'application/x-bzip', 'application/x-bzip2', 'text/css', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'), 'uploadOrder' => array('allow', 'deny'))));
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
	}

}
