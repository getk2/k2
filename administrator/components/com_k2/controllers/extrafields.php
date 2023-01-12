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

class K2ControllerExtraFields extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'extrafields');
        parent::display();
    }

    public function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->publish();
    }

    public function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->unpublish();
    }

    public function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw') {
            echo '1';
            return $this;
        } else {
            $this->setRedirect('index.php?option=com_k2&view=extrafields', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    public function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->orderup();
    }

    public function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->orderdown();
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('extraFields');
        $model->remove();
    }

    public function add()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=extrafield');
    }

    public function edit()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $app->redirect('index.php?option=com_k2&view=extrafield&cid='.$cid[0]);
    }
}
