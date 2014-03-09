<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');

class K2ControllerComments extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {

        $user = JFactory::getUser();
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
            $application = JFactory::getApplication();
			$application->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
            $application->redirect(JRoute::_($url, false));
        }
        JRequest::setVar('tmpl', 'component');

        $params = JComponentHelper::getParams('com_k2');

        $document = JFactory::getDocument();

        if (version_compare(JVERSION, '1.6.0', 'ge'))
        {
            JHtml::_('behavior.framework');
        }
        else
        {
            JHTML::_('behavior.mootools');
        }

        // Language
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);

        // CSS
        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v=2.6.9');

        // JS
        K2HelperHTML::loadjQuery(true);
        $document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v=2.6.9&amp;sitepath='.JURI::root(true).'/');

        $this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
        $this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $view = $this->getView('comments', 'html');
        $view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'comments'.DS.'tmpl');
        $view->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
        $view->display();
    }

    function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest)
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->publish();
    }

    function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest)
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->unpublish();
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest)
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->remove();
    }

    function deleteUnpublished()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest)
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->deleteUnpublished();
    }

    function saveComment()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest)
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->save();
        $mainframe->close();
    }

    function report()
    {
        JRequest::setVar('tmpl', 'component');
        $view = $this->getView('comments', 'html');
        $view->setLayout('report');
        $view->report();
    }

    function sendReport()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $params = K2HelperUtilities::getParams('com_k2');
        $user = JFactory::getUser();
        if (!$params->get('comments') || !$params->get('commentsReporting') || ($params->get('commentsReporting') == '2' && $user->guest))
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        K2Model::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $model = K2Model::getInstance('Comments', 'K2Model');
        $model->setState('id', JRequest::getInt('id'));
        $model->setState('name', JRequest::getString('name'));
        $model->setState('reportReason', JRequest::getString('reportReason'));
        if (!$model->report())
        {
            echo $model->getError();
        }
        else
        {
            echo JText::_('K2_REPORT_SUBMITTED');
        }
        $mainframe = JFactory::getApplication();
        $mainframe->close();
    }

    function reportSpammer()
    {
        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $format = JRequest::getVar('format');
        $errors = array();
        if (K2_JVERSION != '15')
        {
            if (!$user->authorise('core.admin', 'com_k2'))
            {
                $format == 'raw' ? die(JText::_('K2_ALERTNOTAUTH')) : JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }
        else
        {
            if ($user->gid < 25)
            {
                $format == 'raw' ? die(JText::_('K2_ALERTNOTAUTH')) : JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }
        K2Model::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'models');
        $model = K2Model::getInstance('User', 'K2Model');
        $model->setState('id', JRequest::getInt('id'));
        $model->reportSpammer();
        if ($format == 'raw')
        {
            $response = '';
            $messages = $mainframe->getMessageQueue();
            foreach ($messages as $message)
            {
                $response .= $message['message']."\n";
            }
            die($response);

        }
        $this->setRedirect('index.php?option=com_k2&view=comments&tmpl=component');
    }

}
