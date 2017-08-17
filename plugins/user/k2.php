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

class plgUserK2 extends JPlugin
{
	function onUserAfterSave($user, $isnew, $success, $msg)
	{
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}

	function onUserLogin($user, $options)
	{
		return $this->onLoginUser($user, $options);
	}

	function onUserLogout($user)
	{
		return $this->onLogoutUser($user);
	}

	function onUserAfterDelete($user, $success, $msg)
	{
		return $this->onAfterDeleteUser($user, $success, $msg);
	}

	function onUserBeforeSave($user, $isNew)
	{
		return $this->onBeforeStoreUser($user, $isNew);
	}

	function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		jimport('joomla.filesystem.file');
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$task = JRequest::getCmd('task');

		if ($application->isSite() && ($task == 'activate' || $isnew) && $params->get('stopForumSpam'))
		{
			$this->checkSpammer($user);
		}

		if ($application->isSite() && $task != 'activate' && JRequest::getInt('K2UserForm'))
		{
			JPlugin::loadLanguage('com_k2');
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('K2User', 'Table');
			$k2id = $this->getK2UserID($user['id']);
			JRequest::setVar('id', $k2id, 'post');
			$row->bind(JRequest::get('post'));
			$row->set('userID', $user['id']);
			$row->set('userName', $user['name']);
			$row->set('ip', $_SERVER['REMOTE_ADDR']);
			$row->set('hostname', gethostbyaddr($_SERVER['REMOTE_ADDR']));
			if (isset($user['notes']))
			{
				$row->set('notes', $user['notes']);
			}
			if ($isnew)
			{
				$row->set('group', $params->get('K2UserGroup', 1));
			}
			else
			{
				$row->set('group', NULL);
				$row->set('gender', JRequest::getVar('gender'));
				$row->set('url', JRequest::getString('url'));
			}
			if ($row->gender != 'm' && $row->gender != 'f')
			{
				$row->gender = 'm';
			}
			$row->url = JString::str_ireplace(' ', '', $row->url);
			$row->url = JString::str_ireplace('"', '', $row->url);
			$row->url = JString::str_ireplace('<', '', $row->url);
			$row->url = JString::str_ireplace('>', '', $row->url);
			$row->url = JString::str_ireplace('\'', '', $row->url);
			$row->set('description', JRequest::getVar('description', '', 'post', 'string', 4));
			if ($params->get('xssFiltering'))
			{
				$filter = new JFilterInput( array(), array(), 1, 1, 0);
				$row->description = $filter->clean($row->description);
			}
			$row->store();

			$file = JRequest::get('files');

			require_once(JPATH_SITE.'/media/k2/assets/vendors/verot/class.upload.php/src/class.upload.php');
			$savepath = JPATH_ROOT.'/media/k2/users/';

			if (isset($file['image']) && $file['image']['error'] == 0 && !JRequest::getBool('del_image'))
			{
				$handle = new Upload($file['image']);
				$handle->allowed = array('image/*');
				if ($handle->uploaded)
				{
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $row->id;
					$handle->image_resize = true;
					$handle->image_ratio_y = true;
					$handle->image_x = $params->get('userImageWidth', '100');
					$handle->Process($savepath);
					$handle->Clean();
				}
				else
				{
					$application->enqueueMessage(JText::_('K2_COULD_NOT_UPLOAD_YOUR_IMAGE').$handle->error, 'notice');
				}
				$image = $handle->file_dst_name;
			}

			if (JRequest::getBool('del_image'))
			{

				if (JFile::exists(JPATH_ROOT.'/media/k2/users/'.$row->image))
				{
					JFile::delete(JPATH_ROOT.'/media/k2/users/'.$row->image);
				}
				$image = '';
			}
			if (isset($image))
			{
				$row->image = $image;
				$row->store();
			}

			$itemid = $params->get('redirect');
			if (!$isnew && $itemid)
			{
				$menu = JSite::getMenu();
				$item = $menu->getItem($itemid);
				$url = JRoute::_($item->link.'&Itemid='.$itemid, false);

				if (K2_JVERSION == '15')
				{
					if (JURI::isInternal($url))
					{
						$application->enqueueMessage(JText::_('K2_YOUR_SETTINGS_HAVE_BEEN_SAVED'));
						$application->redirect($url);
					}
				}
				else
				{
					$application->setUserState('com_users.edit.profile.redirect', $url);
				}
			}
		}
	}

	function onLoginUser($user, $options)
	{
		$params = JComponentHelper::getParams('com_k2');
		$application = JFactory::getApplication();
		if ($application->isSite())
		{
			// Get the user id
			$db = JFactory::getDbo();
			$db->setQuery("SELECT id FROM #__users WHERE username = ".$db->Quote($user['username']));
			$id = $db->loadResult();

			// If K2 profiles are enabled assign non-existing K2 users to the default K2 group. Update user info for existing K2 users.
			if ($params->get('K2UserProfile') && $id)
			{
				$k2id = $this->getK2UserID($id);
				JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
				$row = JTable::getInstance('K2User', 'Table');
				if ($k2id)
				{
					$row->load($k2id);
				}
				else
				{
					$row->set('userID', $id);
					$row->set('userName', $user['fullname']);
					$row->set('group', $params->get('K2UserGroup', 1));
				}
				$row->ip = $_SERVER['REMOTE_ADDR'];
				$row->hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
				$row->store();
			}

			// Set the Cookie domain for user based on K2 parameters
			if ($params->get('cookieDomain') && $id)
			{
				setcookie("userID", $id, 0, '/', $params->get('cookieDomain'), 0);
			}
		}
		return true;
	}

	function onLogoutUser($user)
	{
		$params = JComponentHelper::getParams('com_k2');
		$application = JFactory::getApplication();
		if ($application->isSite() && $params->get('cookieDomain'))
		{
			setcookie("userID", "", time() - 3600, '/', $params->get('cookieDomain'), 0);
		}
		return true;
	}

	function onAfterDeleteUser($user, $succes, $msg)
	{
		$application = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = "DELETE FROM #__k2_users WHERE userID={$user['id']}";
		$db->setQuery($query);
		$db->query();
	}

	function onBeforeStoreUser($user, $isNew)
	{
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$session = JFactory::getSession();
		if ($params->get('K2UserProfile') && $isNew && $params->get('recaptchaOnRegistration') && $application->isSite() && !$session->get('socialConnectData'))
		{
			if($params->get('recaptchaV2'))
			{
				require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
				if (!K2HelperUtilities::verifyRecaptcha())
				{
					if (K2_JVERSION != '15')
					{
						$url = 'index.php?option=com_users&view=registration';
					}
					else
					{
						$url = 'index.php?option=com_user&view=register';
					}
					$application->enqueueMessage(JText::_('K2_COULD_NOT_VERIFY_THAT_YOU_ARE_NOT_A_ROBOT'), 'error');
					$application->redirect($url);
				}
			}
			else
			{
				if (!function_exists('_recaptcha_qsencode'))
				{
					require_once(JPATH_SITE.'/media/k2/assets/vendors/google/recaptcha_legacy/recaptcha.php');
				}
				$privatekey = $params->get('recaptcha_private_key');
				$recaptcha_challenge_field = isset($_POST["recaptcha_challenge_field"]) ? $_POST["recaptcha_challenge_field"] : '';
				$recaptcha_response_field = isset($_POST["recaptcha_response_field"]) ? $_POST["recaptcha_response_field"] : '';
				$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge_field, $recaptcha_response_field);
				if (!$resp->is_valid)
				{
					if (K2_JVERSION != '15')
					{
						$url = 'index.php?option=com_users&view=registration';
					}
					else
					{
						$url = 'index.php?option=com_user&view=register';
					}
					$application->enqueueMessage(JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN'), 'error');
					$application->redirect($url);
				}
			}
		}
	}

	function getK2UserID($id)
	{
		$db = JFactory::getDbo();
		$query = "SELECT id FROM #__k2_users WHERE userID={$id}";
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	function checkSpammer(&$user)
	{
		if (!$user['block'])
		{
			$ip = $_SERVER['REMOTE_ADDR'];
			$email = urlencode($user['email']);
			$username = urlencode($user['username']);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://www.stopforumspam.com/api?ip='.$ip.'&email='.$email.'&username='.$username.'&f=json');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($httpCode == 200)
			{
				$response = json_decode($response);
				if ($response->ip->appears || $response->email->appears || $response->username->appears)
				{
					$db = JFactory::getDbo();
					$db->setQuery("UPDATE #__users SET block = 1 WHERE id = ".$user['id']);
					$db->query();
					$user['notes'] = JText::_('K2_POSSIBLE_SPAMMER_DETECTED_BY_STOPFORUMSPAM');
				}
			}
		}
	}
}
