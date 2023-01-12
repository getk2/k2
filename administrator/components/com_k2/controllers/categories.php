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

jimport('joomla.application.component.controller');

class K2ControllerCategories extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'categories');
        parent::display();
    }

    public function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->publish();
    }

    public function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->unpublish();
    }

    public function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw') {
            echo '1';
            return $this;
        } else {
            $this->setRedirect('index.php?option=com_k2&view=categories', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    public function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->orderup();
    }

    public function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->orderdown();
    }

    public function accessregistered()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accessregistered();
    }

    public function accessspecial()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accessspecial();
    }

    public function accesspublic()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->accesspublic();
    }

    public function trash()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->trash();
    }

    public function restore()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->restore();
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->remove();
    }

    public function add()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=category');
    }

    public function edit()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $app->redirect('index.php?option=com_k2&view=category&cid='.$cid[0]);
    }

    public function move()
    {
        $view = $this->getView('categories', 'html');
        $view->setLayout('move');
        $view->move();
    }

    public function saveBatch()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->saveBatch();
    }

    public function saveMove()
    {
        $model = $this->getModel('categories');
        $model->move();
    }

    public function copy()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('categories');
        $model->copy();
    }
}
