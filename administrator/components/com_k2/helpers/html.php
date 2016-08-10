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

	public static function loadHeadIncludes($loadFramework = false, $jQueryUI = false, $adminHeadIncludes = false, $adminModuleIncludes = false)
	{
		JLoader::register('K2HelperUtilities', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$option = JRequest::getCmd('option');
		$view = JRequest::getWord('view', 'items');
		$view = JString::strtolower($view);
		$task = JRequest::getCmd('task');
		$params = K2HelperUtilities::getParams('com_k2');
		$jQueryHandling = $params->get('jQueryHandling', '1.8remote');
		$backendJQueryHandling = $params->get('backendJQueryHandling', 'remote');

		if ($document->getType() == 'html')
		{

			if ($loadFramework && $view != 'media')
			{
				if (version_compare(JVERSION, '1.6.0', 'ge'))
				{
					JHtml::_('behavior.framework');
				}
				else
				{
					JHTML::_('behavior.mootools');
				}
			}

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				if ($application->isAdmin() || ($application->isSite() && $params->get('jQueryHandling')))
				{
					JHtml::_('jquery.framework');
				}
			}

			// jQuery
			if (version_compare(JVERSION, '3.0.0', 'lt'))
			{
				// Frontend
				if ($jQueryHandling && JString::strpos($jQueryHandling, 'remote') !== false)
				{
					$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/'.str_replace('remote', '', $jQueryHandling).'/jquery.min.js');
				}
				else if ($jQueryHandling && JString::strpos($jQueryHandling, 'remote') === false)
				{
					$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-'.$jQueryHandling.'.min.js');
				}

				// Backend
				if ($backendJQueryHandling == 'remote')
				{
					if ($view == 'media')
					{
						$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
					}
					else
					{
						$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
					}
				}
				else
				{
					if ($view == 'media')
					{
						$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-1.12.4.min.js');
					}
					else
					{
						$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-1.8.3.min.js');
					}
				}
			}

			// jQueryUI
			if ($jQueryUI)
			{
				if ($view == 'media')
				{
					// Load latest version for the "media" view only
					if ($backendJQueryHandling == 'remote')
					{
						$document->addStyleSheet('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css');
						$document->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js');
					}
					else
					{
						$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/jquery-ui-1.11.4.min.css');
						$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-ui-1.11.4.min.js');
					}
				}
				else
				{
					// Load version 1.8.24 for any other view (until we kill it as a dependency there, for good)...
					if ($backendJQueryHandling == 'remote')
					{
						$document->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js');
					}
					else
					{
						$document->addScript(JURI::root(true).'/media/k2/assets/js/jquery-ui-1.8.24.min.js');
					}
				}
			}

			// Everything else...
			if ($application->isAdmin() || $adminHeadIncludes)
			{

				// CSS
				$document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css?v='.K2_CURRENT_VERSION);
				$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v='.K2_CURRENT_VERSION);
				if($adminModuleIncludes)
				{
					$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.modules.css?v='.K2_CURRENT_VERSION);
				}

				// JS
				$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.noconflict.js?v='.K2_CURRENT_VERSION);
				$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v='.K2_CURRENT_VERSION.'&amp;sitepath='.JURI::root(true).'/');
				if ($option="com_k2" && $view == 'item')
				{
					$document->addScript(JURI::root(true).'/media/k2/assets/js/nicEdit.js?v='.K2_CURRENT_VERSION);
				}

				// Media (elFinder)
				if ($view == 'media')
				{
					$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/elfinder.min.css?v='.K2_CURRENT_VERSION);
			        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/theme.css?v='.K2_CURRENT_VERSION);
					$document->addScript(JURI::root(true).'/media/k2/assets/js/elfinder.min.js?v='.K2_CURRENT_VERSION);
				}
				else
				{
					JHTML::_('behavior.tooltip');
					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						if ($view == 'item' && !$params->get('taggingSystem'))
						{
							JHtml::_('formbehavior.chosen', 'select:not(#selectedTags, #tags)');
						}
						else
						{
							JHtml::_('formbehavior.chosen', 'select');
						}
					}
				}

				$document->addScriptDeclaration('

					// Set K2 version as global JS variable
					K2JVersion = "'.K2_JVERSION.'";

					// Set Joomla version as body tag
					(function(){
						var addedClass = "isJ'.K2_JVERSION.' k2ViewIs'.ucfirst($view).' k2TaskIs'.ucfirst(JRequest::getCmd('task')).'";
						if (document.getElementsByTagName("html")[0].className !== "") {
							document.getElementsByTagName("html")[0].className += " "+addedClass;
						} else {
							document.getElementsByTagName("html")[0].className = addedClass;
						}
					})();

				');

			}
		}
	}

}
