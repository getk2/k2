<?php
/**
 * @version		$Id: extrafields.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');

class K2ControllerExtraFields extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'extrafields');
        parent::display();
    }

    function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->publish();
    }

    function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->unpublish();
    }

    function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw')
        {
            echo '1';
            return $this;
        }
        else
        {
            $this->setRedirect('index.php?option=com_k2&view=extrafields', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->orderup();
    }

    function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->orderdown();
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->remove();
    }

    function add()
    {
        $mainframe = JFactory::getApplication();
        $mainframe->redirect('index.php?option=com_k2&view=extrafield');
    }

    function edit()
    {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $mainframe->redirect('index.php?option=com_k2&view=extrafield&cid='.$cid[0]);
    }

}
