<?php
/**
 * @version		$Id: html.php 2002 2013-07-08 15:43:14Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

class K2HelperHTML
{

	public static function subMenu()
	{
		$user = JFactory::getUser();
		$view = JRequest::getCmd('view');
		$view = JString::strtolower($view);
		$params = JComponentHelper::getParams('com_k2');
		JSubMenuHelper::addEntry(JText::_('K2_ITEMS'), 'index.php?option=com_k2&view=items', $view == 'items');
		JSubMenuHelper::addEntry(JText::_('K2_CATEGORIES'), 'index.php?option=com_k2&view=categories', $view == 'categories');
		if (!$params->get('lockTags') || $user->gid > 23)
		{
			JSubMenuHelper::addEntry(JText::_('K2_TAGS'), 'index.php?option=com_k2&view=tags', $view == 'tags');
		}
		JSubMenuHelper::addEntry(JText::_('K2_COMMENTS'), 'index.php?option=com_k2&view=comments', $view == 'comments');
		if ($user->gid > 23)
		{
			JSubMenuHelper::addEntry(JText::_('K2_USERS'), 'index.php?option=com_k2&view=users', $view == 'users');
			JSubMenuHelper::addEntry(JText::_('K2_USER_GROUPS'), 'index.php?option=com_k2&view=usergroups', $view == 'usergroups');
			JSubMenuHelper::addEntry(JText::_('K2_EXTRA_FIELDS'), 'index.php?option=com_k2&view=extrafields', $view == 'extrafields');
			JSubMenuHelper::addEntry(JText::_('K2_EXTRA_FIELD_GROUPS'), 'index.php?option=com_k2&view=extrafieldsgroups', $view == 'extrafieldsgroups');
		}
		JSubMenuHelper::addEntry(JText::_('K2_MEDIA_MANAGER'), 'index.php?option=com_k2&view=media', $view == 'media');
		JSubMenuHelper::addEntry(JText::_('K2_INFORMATION'), 'index.php?option=com_k2&view=info', $view == 'info');
	}

	public static function stateToggler(&$row, $key, $property = 'published', $tasks = array('publish', 'unpublish'), $labels = array('K2_PUBLISH', 'K2_UNPUBLISH'))
	{
		$task = $row->$property ? $tasks[1] : $tasks[0];
		$action = $row->$property ? JText::_($labels[1]) : JText::_($labels[0]);
		$class = 'k2Toggler';
		$status = $row->$property ? 'k2Active' : 'k2Inactive';
		$href = '<a class="'.$class.' '.$status.'" href="javascript:void(0);" onclick="return listItemTask(\'cb'.$key.'\',\''.$task.'\')" title="'.$action.'">'.$action.'</a>';
		return $href;
	}

	public static function loadjQuery($ui = false, $mediaManager = false)
	{
		JLoader::register('K2HelperUtilities', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = K2HelperUtilities::getParams('com_k2');

		if ($document->getType() == 'html')
		{

			if (K2_JVERSION == '15')
			{
				JHtml::_('behavior.mootools');
			}
			else if (K2_JVERSION == '25')
			{
				JHtml::_('behavior.framework');
			}
			else
			{
				JHtml::_('behavior.framework');
				if ($application->isAdmin() || ($application->isSite() && $params->get('jQueryHandling')))
				{
					JHtml::_('jquery.framework');
				}
			}

			$handling = $application->isAdmin() ? $params->get('backendJQueryHandling', 'remote') : $params->get('jQueryHandling', '1.8remote');
			// jQuery
			if (K2_JVERSION != '30')
			{
				if ($handling == 'remote')
				{
					$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
				}
				else if ($handling == 'local')
				{
					$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-1.8.3.min.js');
				}
				else
				{
					if ($handling && JString::strpos($handling, 'remote') !== false)
					{
						if ($handling == '1.9remote')
						{
							$handling = '1remote';
						}
						$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/'.str_replace('remote', '', $handling).'/jquery.min.js');
					}
					else if ($handling && JString::strpos($handling, 'remote') === false)
					{
						$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-'.$handling.'.min.js');
					}
				}
			}

			// jQuery UI
			if ($application->isAdmin() || $ui)
			{

				// No conflict loaded when $ui requested or in the backend.
				// No need to reload for $mediaManager as the latter is always called with $ui
				$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.noconflict.js');

				if ($handling == 'local')
				{
					$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-ui-1.8.24.custom.min.js');
				}
				else
				{
					$document->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
				}
			}

			if ($mediaManager)
			{
				$document->addScript(JURI::root(true).'/media/k2/assets/js/elfinder.min.js?v=2.6.7');
			}
		}
	}

}
