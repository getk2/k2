<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewComments extends K2View
{
    public function report($tpl = null)
    {
        $params = K2HelperUtilities::getParams('com_k2');
        $document = JFactory::getDocument();
        $user = JFactory::getUser();

        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        $row = JTable::getInstance('K2Comment', 'Table');
        $row->load(JRequest::getInt('commentID'));
        if (!$row->published) {
            JError::raiseError(404, JText::_('K2_NOT_FOUND'));
        }

        if (!$params->get('comments') || !$params->get('commentsReporting') || ($params->get('commentsReporting') == '2' && $user->guest)) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }

        // B/C code for reCAPTCHA
        if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') {
            $params->set('recaptcha', true);
        } else {
            $params->set('recaptcha', false);
        }
        $params->set('recaptchaV2', true);

        // Load reCAPTCHA
        if ($params->get('recaptcha') && ($user->guest || $params->get('recaptchaForRegistered', 1))) {
            $document->addScript('https://www.google.com/recaptcha/api.js?onload=onK2RecaptchaLoaded&render=explicit');
            $document->addScriptDeclaration('
                function onK2RecaptchaLoaded() {
                    grecaptcha.render("recaptcha", {
                        "sitekey": "'.$item->params->get('recaptcha_public_key').'",
                        "theme": "'.$item->params->get('recaptcha_theme', 'light').'"
                    });
                }
            ');
            $this->recaptchaClass = 'k2-recaptcha-v2';
        }

        $this->assignRef('row', $row);
        $this->assignRef('user', $user);
        $this->assignRef('params', $params);

        parent::display($tpl);
    }
}
