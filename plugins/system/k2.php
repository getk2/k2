<?php
/**
 * @version		$Id: k2.php 1978 2013-05-15 19:34:16Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');

class plgSystemK2 extends JPlugin
{

	function plgSystemK2(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onAfterRoute()
	{

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$basepath = ($mainframe->isSite()) ? JPATH_SITE : JPATH_ADMINISTRATOR;

		JPlugin::loadLanguage('com_k2', $basepath);

		if (K2_JVERSION != '15')
		{
			JPlugin::loadLanguage('com_k2.j16', JPATH_ADMINISTRATOR, null, true);
		}
		if ($mainframe->isAdmin())
		{
			return;
		}
		if ((JRequest::getCmd('task') == 'add' || JRequest::getCmd('task') == 'edit') && JRequest::getCmd('option') == 'com_k2')
		{
			return;
		}

		// Joomla! modal trigger
		if ( !$user->guest || (JRequest::getCmd('option') == 'com_k2' && JRequest::getCmd('view') == 'item') || defined('K2_JOOMLA_MODAL_REQUIRED') ){
			JHTML::_('behavior.modal');
		}

		$params = JComponentHelper::getParams('com_k2');

		$document = JFactory::getDocument();

		// jQuery and K2 JS loading
		K2HelperHTML::loadjQuery();

		$document->addScript(JURI::root(true).'/components/com_k2/js/k2.js?v2.6.7&amp;sitepath='.JURI::root(true).'/');
		//$document->addScriptDeclaration("var K2SitePath = '".JURI::root(true)."/';");

		if (JRequest::getCmd('task') == 'search' && $params->get('googleSearch'))
		{
			$language = JFactory::getLanguage();
			$lang = $language->getTag();
			// Fallback to the new container ID without breaking things
			$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
			if($googleSearchContainerID=='k2Container'){
				$googleSearchContainerID = 'k2GoogleSearchContainer';
			}
			$document->addScript('http://www.google.com/jsapi');
			$js = '
			//<![CDATA[
			google.load("search", "1", {"language" : "'.$lang.'"});

			function OnLoad(){
				var searchControl = new google.search.SearchControl();
				var siteSearch = new google.search.WebSearch();
				siteSearch.setUserDefinedLabel("'.$mainframe->getCfg('sitename').'");
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
			//]]>
 			';
			$document->addScriptDeclaration($js);
		}

		// Add related CSS to the <head>
		if ($document->getType() == 'html' && $params->get('enable_css'))
		{

			jimport('joomla.filesystem.file');

			// k2.css
			if (JFile::exists(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'css'.DS.'k2.css'))
				$document->addStyleSheet(JURI::root(true).'/templates/'.$mainframe->getTemplate().'/css/k2.css');
			else
				$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.css');

			// k2.print.css
			if (JRequest::getInt('print') == 1)
			{
				if (JFile::exists(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'css'.DS.'k2.print.css'))
					$document->addStyleSheet(JURI::root(true).'/templates/'.$mainframe->getTemplate().'/css/k2.print.css', 'text/css', 'print');
				else
					$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.print.css', 'text/css', 'print');
			}

		}

	}

	// Extend user forms with K2 fields
	function onAfterDispatch()
	{

		$mainframe = JFactory::getApplication();

		if ($mainframe->isAdmin())
			return;

		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('K2UserProfile'))
			return;
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');
		$layout = JRequest::getCmd('layout');
		$user = JFactory::getUser();

		if (K2_JVERSION != '15')
		{
			$active = JFactory::getApplication()->getMenu()->getActive();
			if (isset($active->query['layout']))
			{
				$layout = $active->query['layout'];
			}
		}

		if (($option == 'com_user' && $view == 'register') || ($option == 'com_users' && $view == 'registration'))
		{

			if ($params->get('recaptchaOnRegistration') && $params->get('recaptcha_public_key'))
			{
				$document = JFactory::getDocument();
				$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
				$js = '
				function showRecaptcha(){
					Recaptcha.create("'.$params->get('recaptcha_public_key').'", "recaptcha", {
						theme: "'.$params->get('recaptcha_theme', 'clean').'"
					});
				}
				$K2(document).ready(function() {
					showRecaptcha();
				});
				';
				$document->addScriptDeclaration($js);
			}

			if (!$user->guest)
			{
				$mainframe->redirect(JURI::root(), JText::_('K2_YOU_ARE_ALREADY_REGISTERED_AS_A_MEMBER'), 'notice');
				$mainframe->close();
			}
			if (K2_JVERSION != '15')
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_users'.DS.'controller.php');
				$controller = new UsersController;

			}
			else
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
				$controller = new UserController;
			}
			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2');
			$view->setLayout('register');

			$K2User = new JObject;

			$K2User->description = '';
			$K2User->gender = 'm';
			$K2User->image = '';
			$K2User->url = '';
			$K2User->plugins = '';

			$wysiwyg = JFactory::getEditor();
			$editor = $wysiwyg->display('description', $K2User->description, '100%', '250px', '', '', false);
			$view->assignRef('editor', $editor);

			$lists = array();
			$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
			$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
			$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'gender', '', 'value', 'text', $K2User->gender);

			$view->assignRef('lists', $lists);
			$view->assignRef('K2Params', $params);

			JPluginHelper::importPlugin('k2');
			$dispatcher = JDispatcher::getInstance();
			$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(
				&$K2User,
				'user'
			));
			$view->assignRef('K2Plugins', $K2Plugins);

			$view->assignRef('K2User', $K2User);
			if (K2_JVERSION != '15')
			{
				$view->assignRef('user', $user);
			}
			$pathway = $mainframe->getPathway();
			$pathway->setPathway(NULL);

			$nameFieldName = K2_JVERSION != '15' ? 'jform[name]' : 'name';
			$view->assignRef('nameFieldName', $nameFieldName);
			$usernameFieldName = K2_JVERSION != '15' ? 'jform[username]' : 'username';
			$view->assignRef('usernameFieldName', $usernameFieldName);
			$emailFieldName = K2_JVERSION != '15' ? 'jform[email1]' : 'email';
			$view->assignRef('emailFieldName', $emailFieldName);
			$passwordFieldName = K2_JVERSION != '15' ? 'jform[password1]' : 'password';
			$view->assignRef('passwordFieldName', $passwordFieldName);
			$passwordVerifyFieldName = K2_JVERSION != '15' ? 'jform[password2]' : 'password2';
			$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
			$optionValue = K2_JVERSION != '15' ? 'com_users' : 'com_user';
			$view->assignRef('optionValue', $optionValue);
			$taskValue = K2_JVERSION != '15' ? 'registration.register' : 'register_save';
			$view->assignRef('taskValue', $taskValue);
			ob_start();
			$view->display();
			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($contents, 'component');

		}

		if (($option == 'com_user' && $view == 'user' && ($task == 'edit' || $layout == 'form')) || ($option == 'com_users' && $view == 'profile' && ($layout == 'edit' || $task == 'profile.edit')))
		{

			if ($user->guest)
			{
				$uri = JFactory::getURI();

				if (K2_JVERSION != '15')
				{
					$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());

				}
				else
				{
					$url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
				}
				$mainframe->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
			}

			if (K2_JVERSION != '15')
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_users'.DS.'controller.php');
				$controller = new UsersController;
			}
			else
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
				$controller = new UserController;
			}

			/*
			// TO DO - We open the profile editing page in a modal, so let's define some CSS
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.frontend.css?v=2.6.7');
			$document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
			$document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');
			if(K2_JVERSION != '15') {
			$document->addStyleSheet(JURI::root(true).'/administrator/templates/bluestork/css/template.css');
			$document->addStyleSheet(JURI::root(true).'/media/system/css/system.css');
			} else {
			$document->addStyleSheet(JURI::root(true).'/administrator/templates/khepri/css/general.css');
			}
			*/

			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2');
			$view->setLayout('profile');

			$model = K2Model::getInstance('Itemlist', 'K2Model');
			$K2User = $model->getUserProfile($user->id);
			if (!is_object($K2User))
			{
				$K2User = new Jobject;
				$K2User->description = '';
				$K2User->gender = 'm';
				$K2User->url = '';
				$K2User->image = NULL;
			}
			if (K2_JVERSION == '15')
			{
				JFilterOutput::objectHTMLSafe($K2User);
			}
			else
			{
				JFilterOutput::objectHTMLSafe($K2User, ENT_QUOTES, array(
					'params',
					'plugins'
				));
			}
			$wysiwyg = JFactory::getEditor();
			$editor = $wysiwyg->display('description', $K2User->description, '100%', '250px', '', '', false);
			$view->assignRef('editor', $editor);

			$lists = array();
			$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
			$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
			$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'gender', '', 'value', 'text', $K2User->gender);

			$view->assignRef('lists', $lists);

			JPluginHelper::importPlugin('k2');
			$dispatcher = JDispatcher::getInstance();
			$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(
				&$K2User,
				'user'
			));
			$view->assignRef('K2Plugins', $K2Plugins);

			$view->assignRef('K2User', $K2User);

			// Asssign some variables depending on Joomla! version
			$nameFieldName = K2_JVERSION != '15' ? 'jform[name]' : 'name';
			$view->assignRef('nameFieldName', $nameFieldName);
			$emailFieldName = K2_JVERSION != '15' ? 'jform[email1]' : 'email';
			$view->assignRef('emailFieldName', $emailFieldName);
			$passwordFieldName = K2_JVERSION != '15' ? 'jform[password1]' : 'password';
			$view->assignRef('passwordFieldName', $passwordFieldName);
			$passwordVerifyFieldName = K2_JVERSION != '15' ? 'jform[password2]' : 'password2';
			$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
			$usernameFieldName = K2_JVERSION != '15' ? 'jform[username]' : 'username';
			$view->assignRef('usernameFieldName', $usernameFieldName);
			$idFieldName = K2_JVERSION != '15' ? 'jform[id]' : 'id';
			$view->assignRef('idFieldName', $idFieldName);
			$optionValue = K2_JVERSION != '15' ? 'com_users' : 'com_user';
			$view->assignRef('optionValue', $optionValue);
			$taskValue = K2_JVERSION != '15' ? 'profile.save' : 'save';
			$view->assignRef('taskValue', $taskValue);

			ob_start();
			if (K2_JVERSION != '15')
			{
				$active = JFactory::getApplication()->getMenu()->getActive();
				if (isset($active->query['layout']) && $active->query['layout'] != 'profile')
				{
					$active->query['layout'] = 'profile';
				}
				$view->assignRef('user', $user);
				$view->display();
			}
			else
			{
				$view->_displayForm();
			}

			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($contents, 'component');

		}

	}

	function onAfterInitialise()
	{
		// Determine Joomla! version
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			define('K2_JVERSION', '30');
		}
		else if (version_compare(JVERSION, '2.5', 'ge'))
		{
			define('K2_JVERSION', '25');
		}
		else
		{
			define('K2_JVERSION', '15');
		}

		// Define the DS constant under Joomla! 3.0
		if (!defined('DS'))
		{
			define('DS', DIRECTORY_SEPARATOR);
		}

		// Import Joomla! classes
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.model');
		jimport('joomla.application.component.view');

		// Get application
		$mainframe = JFactory::getApplication();

		// Load the K2 classes
		JLoader::register('K2Table', JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php');
		JLoader::register('K2Controller', JPATH_BASE.'/components/com_k2/controllers/controller.php');
		JLoader::register('K2Model', JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php');
		if ($mainframe->isSite())
		{
			K2Model::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models');
		}
		else
		{
			// Fix warning under Joomla! 1.5 caused by conflict in model names
			if (K2_JVERSION != '15' || (K2_JVERSION == '15' && JRequest::getCmd('option') != 'com_users'))
			{
				K2Model::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'models');
			}
		}
		JLoader::register('K2View', JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php');
		JLoader::register('K2HelperHTML', JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php');

		// Community Builder integration
		$componentParams = JComponentHelper::getParams('com_k2');
		if ($componentParams->get('cbIntegration') && JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php'))
		{
			define('K2_CB', true);
			global $_CB_framework;
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php');
			cbimport('cb.html');
			cbimport('language.front');
		}
		else
		{
			define('K2_CB', false);
		}

		// Define the default Itemid for users and tags. Defined here instead of the K2HelperRoute for performance reasons.
		// UPDATE : Removed in K2 2.6.7. All K2 links without Itemid now use the anyK2Link defined in the router helper.
		// define('K2_USERS_ITEMID', $componentParams->get('defaultUsersItemid'));
		// define('K2_TAGS_ITEMID', $componentParams->get('defaultTagsItemid'));

		// Define JoomFish compatibility version.
		if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php'))
		{
			if (K2_JVERSION == '15')
			{
				$db = JFactory::getDBO();
				$config = JFactory::getConfig();
				$prefix = $config->getValue('config.dbprefix');
				if (array_key_exists($prefix.'_jf_languages_ext', $db->getTableList()))
				{
					define('K2_JF_ID', 'lang_id');

				}
				else
				{
					define('K2_JF_ID', 'id');
				}
			}
			else
			{
				define('K2_JF_ID', 'lang_id');
			}
		}
		/*
		if(JRequest::getCmd('option')=='com_k2' && JRequest::getCmd('task')=='save' && !$mainframe->isAdmin()){
			$dispatcher = JDispatcher::getInstance();
			foreach($dispatcher->_observers as $observer){
				if($observer->_name=='jfdatabase' || $observer->_name=='jfrouter' || $observer->_name=='missing_translation'){
					$dispatcher->detach($observer);
				}
			}
		}
		*/

		// Use K2 to make Joomla! Varnish-friendly
		// For more checkout: https://snipt.net/fevangelou/the-perfect-varnish-configuration-for-joomla-websites/
		$user = JFactory::getUser();
		if (!$user->guest)
		{
			JResponse::setHeader('X-Logged-In', 'True', true);
		}
		else
		{
			JResponse::setHeader('X-Logged-In', 'False', true);
		}

		if (!$mainframe->isAdmin())
		{
			return;
		}

		$option = JRequest::getCmd('option');
		$task = JRequest::getCmd('task');
		$type = JRequest::getCmd('catid');

		if ($option != 'com_joomfish')
			return;

		if (!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php'))
		{
			return;
		}

		JPlugin::loadLanguage('com_k2', JPATH_ADMINISTRATOR);

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');

		// Joom!Fish
		if ($option == 'com_joomfish' && ($task == 'translate.apply' || $task == 'translate.save') && $type == 'k2_items')
		{

			$language_id = JRequest::getInt('select_language_id');
			$reference_id = JRequest::getInt('reference_id');
			$objects = array();
			$variables = JRequest::get('post');

			foreach ($variables as $key => $value)
			{
				if (( bool )JString::stristr($key, 'K2ExtraField_'))
				{
					$object = new JObject;
					$object->set('id', JString::substr($key, 13));
					$object->set('value', $value);
					unset($object->_errors);
					$objects[] = $object;
				}
			}

			$json = new Services_JSON;
			$extra_fields = $json->encode($objects);

			$extra_fields_search = '';

			foreach ($objects as $object)
			{
				$extra_fields_search .= $this->getSearchValue($object->id, $object->value);
				$extra_fields_search .= ' ';
			}

			$user = JFactory::getUser();

			$db = JFactory::getDBO();

			$query = "SELECT COUNT(*) FROM #__jf_content WHERE reference_field = 'extra_fields' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_items'";
			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result > 0)
			{
				$query = "UPDATE #__jf_content SET value=".$db->Quote($extra_fields)." WHERE reference_field = 'extra_fields' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_items'";
				$db->setQuery($query);
				$db->query();
			}
			else
			{
				$modified = date("Y-m-d H:i:s");
				$modified_by = $user->id;
				$published = JRequest::getVar('published', 0);
				$query = "INSERT INTO #__jf_content (`id`, `language_id`, `reference_id`, `reference_table`, `reference_field` ,`value`, `original_value`, `original_text`, `modified`, `modified_by`, `published`) VALUES (NULL, {$language_id}, {$reference_id}, 'k2_items', 'extra_fields', ".$db->Quote($extra_fields).", '','', ".$db->Quote($modified).", {$modified_by}, {$published} )";
				$db->setQuery($query);
				$db->query();
			}

			$query = "SELECT COUNT(*) FROM #__jf_content WHERE reference_field = 'extra_fields_search' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_items'";
			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result > 0)
			{
				$query = "UPDATE #__jf_content SET value=".$db->Quote($extra_fields_search)." WHERE reference_field = 'extra_fields_search' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_items'";
				$db->setQuery($query);
				$db->query();
			}
			else
			{
				$modified = date("Y-m-d H:i:s");
				$modified_by = $user->id;
				$published = JRequest::getVar('published', 0);
				$query = "INSERT INTO #__jf_content (`id`, `language_id`, `reference_id`, `reference_table`, `reference_field` ,`value`, `original_value`, `original_text`, `modified`, `modified_by`, `published`) VALUES (NULL, {$language_id}, {$reference_id}, 'k2_items', 'extra_fields_search', ".$db->Quote($extra_fields_search).", '','', ".$db->Quote($modified).", {$modified_by}, {$published} )";
				$db->setQuery($query);
				$db->query();
			}

		}

		if ($option == 'com_joomfish' && ($task == 'translate.edit' || $task == 'translate.apply') && $type == 'k2_items')
		{

			if ($task == 'translate.edit')
			{
				$cid = JRequest::getVar('cid');
				$array = explode('|', $cid[0]);
				$reference_id = $array[1];
			}

			if ($task == 'translate.apply')
			{
				$reference_id = JRequest::getInt('reference_id');
			}

			$item = JTable::getInstance('K2Item', 'Table');
			$item->load($reference_id);
			$category_id = $item->catid;
			$language_id = JRequest::getInt('select_language_id');

			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($category_id);
			$group = $category->extraFieldsGroup;
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__k2_extra_fields WHERE `group`=".$db->Quote($group)." AND published=1 ORDER BY ordering";
			$db->setQuery($query);
			$extraFields = $db->loadObjectList();

			$json = new Services_JSON;
			$output = '';
			if (count($extraFields))
			{
				$output .= '<h1>'.JText::_('K2_EXTRA_FIELDS').'</h1>';
				$output .= '<h2>'.JText::_('K2_ORIGINAL').'</h2>';
				foreach ($extraFields as $extrafield)
				{
					$extraField = $json->decode($extrafield->value);
					$output .= trim($this->renderOriginal($extrafield, $reference_id));

				}
			}

			if (count($extraFields))
			{
				$output .= '<h2>'.JText::_('K2_TRANSLATION').'</h2>';
				foreach ($extraFields as $extrafield)
				{
					$extraField = $json->decode($extrafield->value);
					$output .= trim($this->renderTranslated($extrafield, $reference_id));
				}
			}

			$pattern = '/\r\n|\r|\n/';

			// *** Mootools Snippet ***
			$js = "
			window.addEvent('domready', function(){
				var target = $$('table.adminform');
				target.setProperty('id', 'adminform');
				var div = new Element('div', {'id': 'K2ExtraFields'}).setHTML('".preg_replace($pattern, '', $output)."').injectInside($('adminform'));
			});
			";

			if (K2_JVERSION == '15')
			{
				JHTML::_('behavior.mootools');
			}
			else
			{
				JHTML::_('behavior.framework');

			}

			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);

			// *** Embedded CSS Snippet ***
			$document->addCustomTag('
			<style type="text/css" media="all">
				#K2ExtraFields { color:#000; font-size:11px; padding:6px 2px 4px 4px; text-align:left; }
				#K2ExtraFields h1 { font-size:16px; height:25px; }
				#K2ExtraFields h2 { font-size:14px; }
				#K2ExtraFields strong { font-style:italic; }
			</style>
			');
		}

		if ($option == 'com_joomfish' && ($task == 'translate.apply' || $task == 'translate.save') && $type == 'k2_extra_fields')
		{

			$language_id = JRequest::getInt('select_language_id');
			$reference_id = JRequest::getInt('reference_id');
			$extraFieldType = JRequest::getVar('extraFieldType');

			$objects = array();
			$values = JRequest::getVar('option_value');
			$names = JRequest::getVar('option_name');
			$target = JRequest::getVar('option_target');

			for ($i = 0; $i < sizeof($values); $i++)
			{
				$object = new JObject;
				$object->set('name', $names[$i]);

				if ($extraFieldType == 'select' || $extraFieldType == 'multipleSelect' || $extraFieldType == 'radio')
				{
					$object->set('value', $i + 1);
				}
				elseif ($extraFieldType == 'link')
				{
					if (substr($values[$i], 0, 7) == 'http://')
					{
						$values[$i] = $values[$i];
					}
					else
					{
						$values[$i] = 'http://'.$values[$i];
					}
					$object->set('value', $values[$i]);
				}
				else
				{
					$object->set('value', $values[$i]);
				}

				$object->set('target', $target[$i]);
				unset($object->_errors);
				$objects[] = $object;
			}

			$json = new Services_JSON;
			$value = $json->encode($objects);

			$user = JFactory::getUser();

			$db = JFactory::getDBO();

			$query = "SELECT COUNT(*) FROM #__jf_content WHERE reference_field = 'value' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_extra_fields'";
			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result > 0)
			{
				$query = "UPDATE #__jf_content SET value=".$db->Quote($value)." WHERE reference_field = 'value' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_extra_fields'";
				$db->setQuery($query);
				$db->query();
			}
			else
			{
				$modified = date("Y-m-d H:i:s");
				$modified_by = $user->id;
				$published = JRequest::getVar('published', 0);
				$query = "INSERT INTO #__jf_content (`id`, `language_id`, `reference_id`, `reference_table`, `reference_field` ,`value`, `original_value`, `original_text`, `modified`, `modified_by`, `published`) VALUES (NULL, {$language_id}, {$reference_id}, 'k2_extra_fields', 'value', ".$db->Quote($value).", '','', ".$db->Quote($modified).", {$modified_by}, {$published} )";
				$db->setQuery($query);
				$db->query();
			}

		}

		if ($option == 'com_joomfish' && ($task == 'translate.edit' || $task == 'translate.apply') && $type == 'k2_extra_fields')
		{

			if ($task == 'translate.edit')
			{
				$cid = JRequest::getVar('cid');
				$array = explode('|', $cid[0]);
				$reference_id = $array[1];
			}

			if ($task == 'translate.apply')
			{
				$reference_id = JRequest::getInt('reference_id');
			}

			$extraField = JTable::getInstance('K2ExtraField', 'Table');
			$extraField->load($reference_id);
			$language_id = JRequest::getInt('select_language_id');

			if ($extraField->type == 'multipleSelect' || $extraField->type == 'select' || $extraField->type == 'radio')
			{
				$subheader = '<strong>'.JText::_('K2_OPTIONS').'</strong>';
			}
			else
			{
				$subheader = '<strong>'.JText::_('K2_DEFAULT_VALUE').'</strong>';
			}

			$json = new Services_JSON;
			$objects = $json->decode($extraField->value);
			$output = '<input type="hidden" value="'.$extraField->type.'" name="extraFieldType" />';
			if (count($objects))
			{
				$output .= '<h1>'.JText::_('K2_EXTRA_FIELDS').'</h1>';
				$output .= '<h2>'.JText::_('K2_ORIGINAL').'</h2>';
				$output .= $subheader.'<br />';

				foreach ($objects as $object)
				{
					$output .= '<p>'.$object->name.'</p>';
					if ($extraField->type == 'textfield' || $extraField->type == 'textarea')
						$output .= '<p>'.$object->value.'</p>';
				}
			}

			$db = JFactory::getDBO();
			$query = "SELECT `value` FROM #__jf_content WHERE reference_field = 'value' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_extra_fields'";
			$db->setQuery($query);
			$result = $db->loadResult();

			$translatedObjects = $json->decode($result);

			if (count($objects))
			{
				$output .= '<h2>'.JText::_('K2_TRANSLATION').'</h2>';
				$output .= $subheader.'<br />';
				foreach ($objects as $key => $value)
				{
					if (isset($translatedObjects[$key]))
						$value = $translatedObjects[$key];

					if ($extraField->type == 'textarea')
						$output .= '<p><textarea name="option_name[]" cols="30" rows="15"> '.$value->name.'</textarea></p>';
					else
						$output .= '<p><input type="text" name="option_name[]" value="'.$value->name.'" /></p>';
					$output .= '<p><input type="hidden" name="option_value[]" value="'.$value->value.'" /></p>';
					$output .= '<p><input type="hidden" name="option_target[]" value="'.$value->target.'" /></p>';
				}
			}

			$pattern = '/\r\n|\r|\n/';

			// *** Mootools Snippet ***
			$js = "
			window.addEvent('domready', function(){
				var target = $$('table.adminform');
				target.setProperty('id', 'adminform');
				var div = new Element('div', {'id': 'K2ExtraFields'}).setHTML('".preg_replace($pattern, '', $output)."').injectInside($('adminform'));
			});
			";

			JHTML::_('behavior.mootools');
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
		}
		return;
	}

	function onAfterRender()
	{
		$response = JResponse::getBody();
		$searches = array(
			'<meta name="og:url"',
			'<meta name="og:title"',
			'<meta name="og:type"',
			'<meta name="og:image"',
			'<meta name="og:description"'
		);
		$replacements = array(
			'<meta property="og:url"',
			'<meta property="og:title"',
			'<meta property="og:type"',
			'<meta property="og:image"',
			'<meta property="og:description"'
		);
		if (JString::strpos($response, 'prefix="og: http://ogp.me/ns#"') === false)
		{
			$searches[] = '<html ';
			$searches[] = '<html>';
			$replacements[] = '<html prefix="og: http://ogp.me/ns#" ';
			$replacements[] = '<html prefix="og: http://ogp.me/ns#">';
		}
		$response = JString::str_ireplace($searches, $replacements, $response);
		JResponse::setBody($response);
	}

	function getSearchValue($id, $currentValue)
	{

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		$row = JTable::getInstance('K2ExtraField', 'Table');
		$row->load($id);

		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		$jsonObject = $json->decode($row->value);

		$value = '';
		if ($row->type == 'textfield' || $row->type == 'textarea')
		{
			$value = $currentValue;
		}
		else if ($row->type == 'multipleSelect' || $row->type == 'link')
		{
			foreach ($jsonObject as $option)
			{
				if (@in_array($option->value, $currentValue))
					$value .= $option->name.' ';
			}
		}
		else
		{
			foreach ($jsonObject as $option)
			{
				if ($option->value == $currentValue)
					$value .= $option->name;
			}
		}
		return $value;

	}

	function renderOriginal($extraField, $itemID)
	{

		$mainframe = JFactory::getApplication();
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load($itemID);

		$defaultValues = $json->decode($extraField->value);

		foreach ($defaultValues as $value)
		{
			if ($extraField->type == 'textfield' || $extraField->type == 'textarea')
				$active = $value->value;
			else if ($extraField->type == 'link')
			{
				$active[0] = $value->name;
				$active[1] = $value->value;
				$active[2] = $value->target;
			}
			else
				$active = '';
		}

		if (isset($item))
		{
			$currentValues = $json->decode($item->extra_fields);
			if (count($currentValues))
			{
				foreach ($currentValues as $value)
				{
					if ($value->id == $extraField->id)
					{
						$active = $value->value;
					}

				}
			}

		}

		$output = '';

		switch ($extraField->type)
		{
			case 'textfield' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input type="text" disabled="disabled" name="OriginalK2ExtraField_'.$extraField->id.'" value="'.$active.'"/></div><br /><br />';
				break;

			case 'textarea' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><textarea disabled="disabled" name="OriginalK2ExtraField_'.$extraField->id.'" rows="10" cols="40">'.$active.'</textarea></div><br /><br />';
				break;

			case 'link' :
				$output = '<div><strong>'.$extraField->name.'</strong><br />';
				$output .= '&nbsp;<input disabled="disabled"	type="text" name="OriginalK2ExtraField_'.$extraField->id.'[]" value="'.$active[0].'"/><br />';
				$output .= '<br /><br /></div>';
				break;
		}

		return $output;

	}

	function renderTranslated($extraField, $itemID)
	{

		$mainframe = JFactory::getApplication();
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load($itemID);

		$defaultValues = $json->decode($extraField->value);

		foreach ($defaultValues as $value)
		{
			if ($extraField->type == 'textfield' || $extraField->type == 'textarea')
				$active = $value->value;
			else if ($extraField->type == 'link')
			{
				$active[0] = $value->name;
				$active[1] = $value->value;
				$active[2] = $value->target;
			}
			else
				$active = '';
		}

		if (isset($item))
		{
			$currentValues = $json->decode($item->extra_fields);
			if (count($currentValues))
			{
				foreach ($currentValues as $value)
				{
					if ($value->id == $extraField->id)
					{
						$active = $value->value;
					}

				}
			}

		}

		$language_id = JRequest::getInt('select_language_id');
		$db = JFactory::getDBO();
		$query = "SELECT `value` FROM #__jf_content WHERE reference_field = 'extra_fields' AND language_id = {$language_id} AND reference_id = {$itemID} AND reference_table='k2_items'";
		$db->setQuery($query);
		$result = $db->loadResult();
		$currentValues = $json->decode($result);
		if (count($currentValues))
		{
			foreach ($currentValues as $value)
			{
				if ($value->id == $extraField->id)
				{
					$active = $value->value;
				}

			}
		}

		$output = '';

		switch ($extraField->type)
		{

			case 'textfield' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input type="text" name="K2ExtraField_'.$extraField->id.'" value="'.$active.'"/></div><br /><br />';
				break;

			case 'textarea' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><textarea name="K2ExtraField_'.$extraField->id.'" rows="10" cols="40">'.$active.'</textarea></div><br /><br />';
				break;

			case 'select' :
				$output = '<div style="display:none">'.JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id, '', 'value', 'name', $active).'</div>';
				break;

			case 'multipleSelect' :
				$output = '<div style="display:none">'.JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id.'[]', 'multiple="multiple"', 'value', 'name', $active).'</div>';
				break;

			case 'radio' :
				$output = '<div style="display:none">'.JHTML::_('select.radiolist', $defaultValues, 'K2ExtraField_'.$extraField->id, '', 'value', 'name', $active).'</div>';
				break;

			case 'link' :
				$output = '<div><strong>'.$extraField->name.'</strong><br />';
				$output .= '<input type="text" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[0].'"/><br />';
				$output .= '<input type="hidden" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[1].'"/><br />';
				$output .= '<input type="hidden" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[2].'"/><br />';
				$output .= '<br /><br /></div>';
				break;
		}

		return $output;

	}

}
