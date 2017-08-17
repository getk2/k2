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

		if ($_POST)
		{
			JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));
		}
		else
		{
			JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		}

		$application = JFactory::getApplication();
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
		$path = JPATH_SITE.'/'.JPath::clean($folder);

		JPath::check($path);

		require_once(JPATH_SITE.'/media/k2/assets/vendors/studio-42/elfinder/php/autoload.php');

		function access($attr, $path, $data, $volume)
		{
			$application = JFactory::getApplication();

			// Hide PHP files
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
					return ($application->isSite()) ? false : true;
					break;
				case 'locked' :
					return ($application->isSite()) ? true : false;
					break;
				case 'hidden' :
					return false;
					break;
			}

		}

		if ($application->isAdmin())
		{
			$permissions = array('read' => true, 'write' => true);
		}
		else
		{
			$permissions = array('read' => true, 'write' => false);
		}

		$options = array(
			'debug' => false,
			'roots' => array(
				array(
					'driver' => 'LocalFileSystem',
					'path' => $path,
					'URL' => $url,
					'accessControl' => 'access',
					'defaults' => $permissions,
					'mimeDetect' => 'internal',
					'uploadDeny' => array('all'),
					'uploadAllow' => array('image', 'video', 'audio', 'text/plain', 'text/html', 'application/json', 'application/pdf', 'application/zip', 'application/x-7z-compressed', 'application/x-bzip', 'application/x-bzip2', 'text/css', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
					'uploadOrder' => array('deny', 'allow')
				)
			)
		);
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
	}

}
