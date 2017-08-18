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

jimport('joomla.plugin.plugin');

class plgSystemK2 extends JPlugin
{

	function onAfterInitialise()
	{
		// Determine Joomla version
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

		// Define K2 version & build here
		define('K2_CURRENT_VERSION', '2.8.0');
		define('K2_BUILD', ''); // Use '' for stable or ' [Dev Build YYYYMMDD]' for the developer build

		// Define the DS constant (for backwards compatibility with old template overrides & 3rd party K2 extensions)
		if (!defined('DS'))
		{
			define('DS', DIRECTORY_SEPARATOR);
		}

		// Import Joomla classes
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.model');
		jimport('joomla.application.component.view');

		// Get application & K2 component params
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');

		// Load the K2 classes
		JLoader::register('K2Table', JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php');
		JLoader::register('K2Controller', JPATH_BASE.'/components/com_k2/controllers/controller.php');
		JLoader::register('K2Model', JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php');
		if ($application->isSite())
		{
			K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
		}
		else
		{
			// Fix warning under Joomla 1.5 caused by conflict in model names
			if (K2_JVERSION != '15' || (K2_JVERSION == '15' && JRequest::getCmd('option') != 'com_users'))
			{
				K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			}
		}
		JLoader::register('K2View', JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php');
		JLoader::register('K2HelperHTML', JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php');
		JLoader::register('K2HelperUtilities', JPATH_SITE.'/components/com_k2/helpers/utilities.php');

		// Define the default Itemid for users and tags (to be removed)
		//define('K2_USERS_ITEMID', $params->get('defaultUsersItemid'));
		//define('K2_TAGS_ITEMID', $params->get('defaultTagsItemid'));

		// Custom HTTP headers
		$user = JFactory::getUser();
		if (!$user->guest)
		{
			JResponse::setHeader('X-Logged-In', 'True', true);
		}
		else
		{
			JResponse::setHeader('X-Logged-In', 'False', true);
		}
		JResponse::setHeader('X-Content-Powered-By', 'K2 v'.K2_CURRENT_VERSION.' (by JoomlaWorks)', true);

		// Define JoomFish compatibility version.
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php'))
		{
			if (K2_JVERSION == '15')
			{
				$db = JFactory::getDbo();
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

		// Backend only
		if (!$application->isAdmin())
		{
			return;
		}

		// K2 Metrics
		if($application->isAdmin() && $params->get('gatherStatistics', 1))
		{
			$option = JRequest::getCmd('option');
			$view = JRequest::getCmd('view');
			$viewsToRun = array('items', 'categories', 'tags', 'comments', 'users', 'usergroups', 'extrafields', 'extrafieldsgroups', '');
			if($option == 'com_k2' && in_array($view, $viewsToRun))
			{
				require_once(JPATH_ADMINISTRATOR.'/components/com_k2/helpers/stats.php');
				if(K2HelperStats::shouldLog())
				{
					K2HelperStats::getScripts();
				}
			}
		}

		// --- JoomFish integration [start] ---
		if ((int)K2_JVERSION < 25) {
			$option = JRequest::getCmd('option');
			$task = JRequest::getCmd('task');
			$type = JRequest::getCmd('catid');
		} else {
			$option = JFactory::getApplication()->input->get('option');
			$task = JFactory::getApplication()->input->get('task');
			$type = JRequest::getCmd('catid');
		}
		if ($option == 'com_joomfish')
		{
			JPlugin::loadLanguage('com_k2', JPATH_ADMINISTRATOR);
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

			if (($task == 'translate.apply' || $task == 'translate.save') && $type == 'k2_items')
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

				$extra_fields = json_encode($objects);
				$extra_fields_search = '';

				foreach ($objects as $object)
				{
					$extra_fields_search .= $this->getSearchValue($object->id, $object->value);
					$extra_fields_search .= ' ';
				}

				$user = JFactory::getUser();

				$db = JFactory::getDbo();
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

			if (($task == 'translate.edit' || $task == 'translate.apply') && $type == 'k2_items')
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
				$db = JFactory::getDbo();
				$query = "SELECT * FROM #__k2_extra_fields WHERE `group`=".$db->Quote($group)." AND published=1 ORDER BY ordering";
				$db->setQuery($query);
				$extraFields = $db->loadObjectList();

				$output = '';
				if (count($extraFields))
				{
					$output .= '<h1>'.JText::_('K2_EXTRA_FIELDS').'</h1>';
					$output .= '<h2>'.JText::_('K2_ORIGINAL').'</h2>';
					foreach ($extraFields as $extrafield)
					{
						$extraField = json_decode($extrafield->value);
						$output .= trim($this->renderOriginal($extrafield, $reference_id));

					}
				}

				if (count($extraFields))
				{
					$output .= '<h2>'.JText::_('K2_TRANSLATION').'</h2>';
					foreach ($extraFields as $extrafield)
					{
						$extraField = json_decode($extrafield->value);
						$output .= trim($this->renderTranslated($extrafield, $reference_id));
					}
				}

				$pattern = '/\r\n|\r|\n/';

				// Load CSS & JS
				if (K2_JVERSION == '15')
				{
					JHTML::_('behavior.mootools');
				}
				else
				{
					JHTML::_('behavior.framework');
				}
				$document = JFactory::getDocument();
				$document->addScriptDeclaration("
					window.addEvent('domready', function(){
						var target = $$('table.adminform');
						target.setProperty('id', 'adminform');
						var div = new Element('div', {'id': 'K2ExtraFields'}).setHTML('".preg_replace($pattern, '', $output)."').injectInside($('adminform'));
					});
				");
			}

			if (($task == 'translate.apply' || $task == 'translate.save') && $type == 'k2_extra_fields')
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

				$value = json_encode($objects);

				$user = JFactory::getUser();

				$db = JFactory::getDbo();
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

			if (($task == 'translate.edit' || $task == 'translate.apply') && $type == 'k2_extra_fields')
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

				$objects = json_decode($extraField->value);
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

				$db = JFactory::getDbo();
				$query = "SELECT `value` FROM #__jf_content WHERE reference_field = 'value' AND language_id = {$language_id} AND reference_id = {$reference_id} AND reference_table='k2_extra_fields'";
				$db->setQuery($query);
				$result = $db->loadResult();

				$translatedObjects = json_decode($result);

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

				// Load CSS & JS
				if (K2_JVERSION == '15')
				{
					JHTML::_('behavior.mootools');
				}
				else
				{
					JHtml::_('behavior.framework');
				}
				$document = JFactory::getDocument();
				$document->addScriptDeclaration("
					window.addEvent('domready', function(){
						var target = $$('table.adminform');
						target.setProperty('id', 'adminform');
						var div = new Element('div', {'id': 'K2ExtraFields'}).setHTML('".preg_replace($pattern, '', $output)."').injectInside($('adminform'));
					});
				");
			}
		}
		// --- JoomFish integration [finish] ---

		return;
	}

	function onAfterRoute()
	{
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		$params = JComponentHelper::getParams('com_k2');

		$basepath = ($application->isSite()) ? JPATH_SITE : JPATH_ADMINISTRATOR;
		JPlugin::loadLanguage('com_k2', $basepath);
		if (K2_JVERSION != '15')
		{
			JPlugin::loadLanguage('com_k2.dates', JPATH_ADMINISTRATOR, null, true);
		}

		if ($application->isAdmin() || (JRequest::getCmd('option') == 'com_k2' && (JRequest::getCmd('task') == 'add' || JRequest::getCmd('task') == 'edit')))
		{
			return;
		}

		// Load required CSS & JS
		K2HelperHTML::loadHeadIncludes();
	}

	function onAfterDispatch()
	{
		$application = JFactory::getApplication();

		if ($application->isAdmin()) return;

		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('K2UserProfile')) return;

		$document = JFactory::getDocument();

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

		// Extend user forms with K2 fields
		if (($option == 'com_user' && $view == 'register') || ($option == 'com_users' && $view == 'registration'))
		{
			if ($params->get('recaptchaOnRegistration') && $params->get('recaptcha_public_key'))
			{
				if($params->get('recaptchaV2')) {
					$document->addScript('https://www.google.com/recaptcha/api.js?onload=onK2RecaptchaLoaded&render=explicit');
					$document->addScriptDeclaration('
					/* K2 - Google reCAPTCHA */
					function onK2RecaptchaLoaded(){
						grecaptcha.render("recaptcha", {"sitekey": "'.$params->get('recaptcha_public_key').'"});
					}
					');
					$recaptchaClass = 'k2-recaptcha-v2';
				}
				else
				{
					$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
					$document->addScriptDeclaration('
						function showRecaptcha(){
							Recaptcha.create("'.$params->get('recaptcha_public_key').'", "recaptcha", {
								theme: "'.$params->get('recaptcha_theme', 'clean').'"
							});
						}
						$K2(document).ready(function() {
							showRecaptcha();
						});
					');
					$recaptchaClass = 'k2-recaptcha-v1';
				}
			}

			if (!$user->guest)
			{
				$application->enqueueMessage(JText::_('K2_YOU_ARE_ALREADY_REGISTERED_AS_A_MEMBER'), 'notice');
				$application->redirect(JURI::root());
				$application->close();
			}
			if (K2_JVERSION != '15')
			{
				require_once(JPATH_SITE.'/components/com_users/controller.php');
				$controller = new UsersController;
			}
			else
			{
				require_once(JPATH_SITE.'/components/com_user/controller.php');
				$controller = new UserController;
			}
			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.'/components/com_k2/templates');
			$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
			$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');
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
			$view->assignRef('recaptchaClass', $recaptchaClass);

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
			$pathway = $application->getPathway();
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
				$application->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
				$application->redirect(JRoute::_($url, false));
			}

			if (K2_JVERSION != '15')
			{
				require_once(JPATH_SITE.'/components/com_users/controller.php');
				$controller = new UsersController;
			}
			else
			{
				require_once(JPATH_SITE.'/components/com_user/controller.php');
				$controller = new UserController;
			}

			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.'/components/com_k2/templates');
			$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
			$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');
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

			// Asssign some variables depending on Joomla version
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
			$document->setBuffer($contents, 'component');
		}
	}

	function onAfterRender()
	{
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');

		// Fix OpenGraph meta tags
		if($application->isSite() && $params->get('facebookMetatags', 1))
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
	}

	/* ============================================ */
	/* ============= Helper Functions ============= */
	/* ============================================ */
	function getSearchValue($id, $currentValue)
	{

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
		$row = JTable::getInstance('K2ExtraField', 'Table');
		$row->load($id);
		$jsonObject = json_decode($row->value);
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

		$application = JFactory::getApplication();
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load($itemID);

		$defaultValues = json_decode($extraField->value);

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
			$currentValues = json_decode($item->extra_fields);
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
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input type="text" disabled="disabled" name="OriginalK2ExtraField_'.$extraField->id.'" value="'.$active.'" /></div><br /><br />';
				break;

			case 'textarea' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><textarea disabled="disabled" name="OriginalK2ExtraField_'.$extraField->id.'" rows="10" cols="40">'.$active.'</textarea></div><br /><br />';
				break;

			case 'link' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input disabled="disabled" type="text" name="OriginalK2ExtraField_'.$extraField->id.'[]" value="'.$active[0].'" /></div><br /><br />';
				break;
		}

		return $output;

	}

	function renderTranslated($extraField, $itemID)
	{

		$application = JFactory::getApplication();
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load($itemID);

		$defaultValues = json_decode($extraField->value);

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
			$currentValues = json_decode($item->extra_fields);
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
		$db = JFactory::getDbo();
		$query = "SELECT `value` FROM #__jf_content WHERE reference_field = 'extra_fields' AND language_id = {$language_id} AND reference_id = {$itemID} AND reference_table='k2_items'";
		$db->setQuery($query);
		$result = $db->loadResult();
		$currentValues = json_decode($result);
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
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input type="text" name="K2ExtraField_'.$extraField->id.'" value="'.$active.'" /></div><br /><br />';
				break;

			case 'textarea' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><textarea name="K2ExtraField_'.$extraField->id.'" rows="10" cols="40">'.$active.'</textarea></div><br /><br />';
				break;

			case 'select' :
				$output = '<div style="display:none;">'.JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id, '', 'value', 'name', $active).'</div>';
				break;

			case 'multipleSelect' :
				$output = '<div style="display:none;">'.JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id.'[]', 'multiple="multiple"', 'value', 'name', $active).'</div>';
				break;

			case 'radio' :
				$output = '<div style="display:none;">'.JHTML::_('select.radiolist', $defaultValues, 'K2ExtraField_'.$extraField->id, '', 'value', 'name', $active).'</div>';
				break;

			case 'link' :
				$output = '<div><strong>'.$extraField->name.'</strong><br /><input type="text" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[0].'" /><br /><input type="hidden" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[1].'" /><br /><input type="hidden" name="K2ExtraField_'.$extraField->id.'[]" value="'.$active[2].'" /></div><br /><br />';
				break;
		}

		return $output;

	}

}
