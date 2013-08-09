<?php
/**
 * @version		$Id: items.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');

class K2ControllerItems extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'items');
        parent::display();
    }

    function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->publish();
    }

    function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->unpublish();
    }

    function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $result = $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw')
        {
            echo '1';
            return $this;
        }
        else
        {
            $this->setRedirect('index.php?option=com_k2&view=items', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->orderup();
    }

    function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->orderdown();
    }

    function savefeaturedorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $result = $model->savefeaturedorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw')
        {
            echo '1';
            return $this;
        }
        else
        {
            $this->setRedirect('index.php?option=com_k2&view=items', JText::_('K2_NEW_FEATURED_ORDERING_SAVED'));
        }
    }

    function featuredorderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featuredorderup();
    }

    function featuredorderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featuredorderdown();
    }

    function accessregistered()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accessregistered();
    }

    function accessspecial()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accessspecial();
    }

    function accesspublic()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accesspublic();
    }

    function featured()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featured();
    }

    function trash()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->trash();
    }

    function restore()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->restore();
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->remove();
    }

    function add()
    {
        $mainframe = JFactory::getApplication();
        $mainframe->redirect('index.php?option=com_k2&view=item');
    }

    function edit()
    {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $mainframe->redirect('index.php?option=com_k2&view=item&cid='.$cid[0]);
    }

    function copy()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->copy();
    }

    function element()
    {
        JRequest::setVar('view', 'items');
        JRequest::setVar('layout', 'element');
        parent::display();
    }

    function import()
    {
        $model = $this->getModel('items');
        if (K2_JVERSION != '15')
        {
            $model->importJ16();
        }
        else
        {
            $model->import();
        }
    }

    function move()
    {
        $view = $this->getView('items', 'html');
        $view->setLayout('move');
        $view->move();
    }

    function saveMove()
    {
        $model = $this->getModel('items');
        $model->move();
    }

}
