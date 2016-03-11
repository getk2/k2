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

jimport('joomla.application.component.view');

class K2ViewComments extends K2View
{

	function report($tpl = null)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		$row = JTable::getInstance('K2Comment', 'Table');
		$row->load(JRequest::getInt('commentID'));
		if (!$row->published)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}
		$this->assignRef('row', $row);
		$user = JFactory::getUser();
		$this->assignRef('user', $user);
		$params = K2HelperUtilities::getParams('com_k2');
		if (!$params->get('comments') || !$params->get('commentsReporting') || ($params->get('commentsReporting') == '2' && $user->guest))
		{
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}
		// Pass the old parameter to the view in order to avoid layout changes
		if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both')
		{
			$params->set('recaptcha', true);
		}
		else
		{
			$params->set('recaptcha', false);
		}

		$this->assignRef('params', $params);
		if ($params->get('recaptcha') && $user->guest)
		{
			$document = JFactory::getDocument();
			if($params->get('recaptchaV2')) {
				$document->addScript('https://www.google.com/recaptcha/api.js?onload=onK2RecaptchaLoaded&render=explicit');
				$js = 'function onK2RecaptchaLoaded(){grecaptcha.render("recaptcha", {"sitekey" : "'.$params->get('recaptcha_public_key').'"});}';
				$document->addScriptDeclaration($js);
				$this->recaptchaClass = 'k2-recaptcha-v2';
			}
			else
			{
				$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
				$js = '
				function showRecaptcha(){
					Recaptcha.create("'.$params->get('recaptcha_public_key').'", "recaptcha", {
						theme: "'.$params->get('recaptcha_theme', 'clean').'"
					});
				}
				$K2(window).load(function() {
					showRecaptcha();
				});
				';
				$document->addScriptDeclaration($js);
				$this->recaptchaClass = 'k2-recaptcha-v1';
			}

		}

		parent::display($tpl);
	}

}
