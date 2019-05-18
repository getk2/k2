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

class K2ViewMedia extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $document = JFactory::getDocument();
        $type = JRequest::getCmd('type');
        $fieldID = JRequest::getCmd('fieldID');
        if ($type == 'video') {
            $mimes = "'video','audio'";
        } elseif ($type == 'image') {
            $mimes = "'image'";
        } else {
            $mimes = '';
        }
        $token = version_compare(JVERSION, '2.5', 'ge') ? JSession::getFormToken() : JUtility::getToken();

        $this->assignRef('mimes', $mimes);
        $this->assignRef('type', $type);
        $this->assignRef('fieldID', $fieldID);
        $this->assignRef('token', $token);

        if ($app->isAdmin()) {
            // Toolbar
            JToolBarHelper::title(JText::_('K2_MEDIA_MANAGER'), 'k2.png');
            if (K2_JVERSION != '15') {
                JToolBarHelper::preferences('com_k2', '(window.innerHeight) * 0.8', '(window.innerWidth) * 0.8', 'K2_SETTINGS');
            } else {
                $toolbar = JToolBar::getInstance('toolbar');
                $toolbar->appendButton('Popup', 'config', 'K2_SETTINGS', 'index.php?option=com_k2&view=settings', '(window.innerWidth) * 0.8', '(window.innerHeight) * 0.8');
            }

            $this->loadHelper('html');
            K2HelperHTML::subMenu();
        }

        parent::display($tpl);
    }
}
