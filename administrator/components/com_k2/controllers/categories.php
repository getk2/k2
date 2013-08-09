<?php
/**
 * @version		$Id: categories.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class K2ControllerCategories extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'categories');
        parent::display();
    }

    function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->publish();
    }

    function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->unpublish();
    }

    function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw')
        {
            echo '1';
            return $this;
        }
        else
        {
            $this->setRedirect('index.php?option=com_k2&view=categories', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->orderup();
    }

    function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->orderdown();
    }

    function accessregistered()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accessregistered();
    }

    function accessspecial()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accessspecial();
    }

    function accesspublic()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accesspublic();
    }

    function trash()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->trash();
    }

    function restore()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->restore();
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->remove();
    }

    function add()
    {
        $mainframe = JFactory::getApplication();
        $mainframe->redirect('index.php?option=com_k2&view=category');
    }

    function edit()
    {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $mainframe->redirect('index.php?option=com_k2&view=category&cid='.$cid[0]);
    }

    function element()
    {
        JRequest::setVar('view', 'categories');
        JRequest::setVar('layout', 'element');
        parent::display();
    }

    function move()
    {
        $view = $this->getView('categories', 'html');
        $view->setLayout('move');
        $view->move();
    }

    function saveMove()
    {
        $model = $this->getModel('categories');
        $model->move();
    }

    function copy()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->copy();
    }

}
