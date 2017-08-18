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
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		$params = K2HelperUtilities::getParams('com_k2');

		$option = JRequest::getCmd('option');
		$view = strtolower(JRequest::getWord('view', 'items'));
		$task = JRequest::getCmd('task');

		$jQueryHandling = $params->get('jQueryHandling', '1.9.1');

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
				if ($application->isSite())
				{
					// B/C for saved old options
					if ($jQueryHandling == '1.7remote') $jQueryHandling = '1.7.2';
					if ($jQueryHandling == '1.8remote') $jQueryHandling = '1.8.3';
					if ($jQueryHandling == '1.9remote') $jQueryHandling = '1.9.1';
					if ($jQueryHandling == '1.10remote') $jQueryHandling = '1.10.2';
					if ($jQueryHandling == '1.11remote') $jQueryHandling = '1.11.3';
					if ($jQueryHandling == '1.12remote') $jQueryHandling = '1.12.4';
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/'.$jQueryHandling.'/jquery.min.js');
				}

				// Backend
				if ($application->isAdmin())
				{
					if (($option == 'com_k2' && ($view == 'item' || $view == 'category')) || $option == 'com_menus')
					{
						$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js');
					}
					else
					{
						$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js');
					}
				}
			}

			// jQueryUI
			if ($jQueryUI)
			{
				// Load version 1.8.24 for tabs & sortables (called the "old" way)...
				if (($option == 'com_k2' && ($view == 'item' || $view == 'category')) || $option == 'com_menus')
				{
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js');
				}

				// Load latest version for the "media" view & modules only
				if (($option == 'com_k2' && $view == 'media') || $option == 'com_modules' || $option == 'com_advancedmodules')
				{
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css');
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
				}
			}

			// Everything else...
			if ($application->isAdmin() || $adminHeadIncludes)
			{
				// JS
				$isBackend = ($application->isAdmin()) ? ' k2IsBackend' : '';
				$isTask = ($task) ? ' k2TaskIs'.ucfirst($task) : '';
				$cssClass = 'isJ'.K2_JVERSION.' k2ViewIs'.ucfirst($view).''.$isTask.''.$isBackend;
				$document->addScriptDeclaration("

					// Set K2 version as global JS variable
					K2JVersion = '".K2_JVERSION."';

					// Set Joomla version as class in the 'html' tag
					(function(){
						var addedClass = '".$cssClass."';
						if (document.getElementsByTagName('html')[0].className !== '') {
							document.getElementsByTagName('html')[0].className += ' '+addedClass;
						} else {
							document.getElementsByTagName('html')[0].className = addedClass;
						}
					})();

					// K2 Language Strings
		        	var K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST')."';
		        	var K2_REMOVE_THIS_ENTRY = '".JText::_('K2_REMOVE_THIS_ENTRY')."';
		        	var K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST')."';

				");
				$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.backend.js?v='.K2_CURRENT_VERSION.'&amp;sitepath='.JURI::root(true).'/');

				// NicEdit
				if ($option == 'com_k2' && $view == 'item')
				{
					$document->addScript(JURI::root(true).'/media/k2/assets/vendors/bkirchoff/nicedit/nicEdit.js?v='.K2_CURRENT_VERSION);
				}

				// Media (elFinder)
				if ($view == 'media')
				{
					$document->addStyleSheet(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/css/elfinder.min.css?v='.K2_CURRENT_VERSION);
			        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/css/theme.css?v='.K2_CURRENT_VERSION);
					$document->addScript(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/js/elfinder.min.js?v='.K2_CURRENT_VERSION);
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

				// Flatpickr
				if ($view == 'item' || $view == 'extrafield')
				{
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/2.6.3/flatpickr.min.css');
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/2.6.3/flatpickr.min.js');
					$document->addCustomTag('<!--[if IE 9]><link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/2.6.3/ie.css" /><![endif]-->');
				}

				// Magnific Popup
				$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css');
				$document->addStyleDeclaration('
					/* K2 - Magnific Popup Overrides */
					.mfp-iframe-holder {padding:10px;}
					.mfp-iframe-holder .mfp-content {max-width:100%;width:100%;height:100%;}
					.mfp-iframe-scaler iframe {background:#fff;padding:10px;box-sizing:border-box;box-shadow:none;}
				');
				$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js');

				// Fancybox
				if ($view == 'item' || $view == 'items' || $view == 'categories')
				{
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css');
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js');
				}

				// CSS
				if ($option == 'com_k2' || $adminModuleIncludes)
				{
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
				}
				if ($option == 'com_k2')
				{
					$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.backend.css?v='.K2_CURRENT_VERSION);
				}
				if($adminModuleIncludes)
				{
					$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.global.css?v='.K2_CURRENT_VERSION);
				}
			}

			// Frontend only
			if($application->isSite())
			{
				// Magnific Popup
				if (!$user->guest || ($option == 'com_k2' && $view == 'item') || defined('K2_JOOMLA_MODAL_REQUIRED')){
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css');
					$document->addStyleDeclaration('
						/* K2 - Magnific Popup Overrides */
						.mfp-iframe-holder {padding:10px;}
						.mfp-iframe-holder .mfp-content {max-width:100%;width:100%;height:100%;}
						.mfp-iframe-scaler iframe {background:#fff;padding:10px;box-sizing:border-box;box-shadow:none;}
					');
					$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js');
				}

				// JS
				$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.frontend.js?v='.K2_CURRENT_VERSION.'&amp;sitepath='.JURI::root(true).'/');

				// Google Search (deprecated - to remove)
				if ($task == 'search' && $params->get('googleSearch'))
				{
					$language = JFactory::getLanguage();
					$lang = $language->getTag();
					// Fallback to the new container ID without breaking things
					$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
					if($googleSearchContainerID == 'k2Container'){
						$googleSearchContainerID = 'k2GoogleSearchContainer';
					}
					$document->addScript('https://www.google.com/jsapi');
					$document->addScriptDeclaration('
						google.load("search", "1", {"language" : "'.$lang.'"});
						function OnLoad(){
							var searchControl = new google.search.SearchControl();
							var siteSearch = new google.search.WebSearch();
							siteSearch.setUserDefinedLabel("'.$application->getCfg('sitename').'");
							siteSearch.setUserDefinedClassSuffix("k2");
							options = new google.search.SearcherOptions();
							options.setExpandMode(google.search.SearchControl.EXPAND_MODE_OPEN);
							siteSearch.setSiteRestriction("'.JURI::root().'");
							searchControl.addSearcher(siteSearch, options);
							searchControl.setResultSetSize(google.search.Search.LARGE_RESULTSET);
							searchControl.setLinkTarget(google.search.Search.LINK_TARGET_SELF);
							searchControl.draw(document.getElementById("'.$googleSearchContainerID.'"));
							searchControl.execute("'.JRequest::getString('searchword').'");
						}
						google.setOnLoadCallback(OnLoad);
					');
				}

				// Add related CSS to the <head>
				if ($params->get('enable_css'))
				{
					jimport('joomla.filesystem.file');

					// Simple Line Icons
					$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css');

					// k2.css
					if (JFile::exists(JPATH_SITE.'/templates/'.$application->getTemplate().'/css/k2.css'))
					{
						$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.css?v='.K2_CURRENT_VERSION);
					}
					else
					{
						$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.css?v='.K2_CURRENT_VERSION);
					}

					// k2.print.css
					if (JRequest::getInt('print') == 1)
					{
						if (JFile::exists(JPATH_SITE.'/templates/'.$application->getTemplate().'/css/k2.print.css'))
						{
							$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.print.css?v='.K2_CURRENT_VERSION, 'text/css', 'print');
						}
						else
						{
							$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.print.css?v='.K2_CURRENT_VERSION, 'text/css', 'print');
						}
					}
				}
			}
		}
	}
}
