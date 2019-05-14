<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
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

        // B/C code for reCaptcha
        if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') {
            $params->set('recaptcha', true);
        } else {
            $params->set('recaptcha', false);
        }

        // Load reCaptcha
        if ($params->get('recaptcha') && ($user->guest || $params->get('recaptchaForRegistered', 1))) {
            if ($params->get('recaptchaV2')) {
                $document->addScript('https://www.google.com/recaptcha/api.js?onload=onK2RecaptchaLoaded&render=explicit');
                $document->addScriptDeclaration('
                    /* K2: reCaptcha v2 */
                    function onK2RecaptchaLoaded(){
                        grecaptcha.render("recaptcha", {
                            "sitekey": "'.$item->params->get('recaptcha_public_key').'"
                        });
                    }
                ');
                $this->recaptchaClass = 'k2-recaptcha-v2';
            } else {
                $document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
                $document->addScriptDeclaration('
                    /* K2: reCaptcha v1 */
                    function showRecaptcha(){
                        Recaptcha.create("'.$item->params->get('recaptcha_public_key').'", "recaptcha", {
                            theme: "'.$item->params->get('recaptcha_theme', 'clean').'"
                        });
                    }
                    $K2(window).load(function() {
                        showRecaptcha();
                    });
                ');
                $this->recaptchaClass = 'k2-recaptcha-v1';
            }
        }

        $this->assignRef('row', $row);
        $this->assignRef('user', $user);
        $this->assignRef('params', $params);

        parent::display($tpl);
    }
}
