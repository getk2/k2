<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class K2ControllerUsers extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'users');
        parent::display();
    }

    function edit()
    {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $mainframe->redirect('index.php?option=com_k2&view=user&cid='.$cid[0]);
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('users');
        $model->remove();
    }

    function element()
    {
        JRequest::setVar('view', 'users');
        JRequest::setVar('layout', 'element');
        parent::display();
    }

    function enable()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('users');
        $model->enable();
    }

    function disable()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('users');
        $model->disable();
    }

    function delete()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('users');
        $model->delete();
    }

    function move()
    {
        $view = $this->getView('users', 'html');
        $view->setLayout('move');
        $model = $this->getModel('users');
        $view->setModel($model);
        $view->move();
    }

    function saveMove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('users');
        $model->saveMove();
    }

    function import()
    {
        $model = $this->getModel('users');
        $model->import();
    }
	
	function search()
	{
		$mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $word = JRequest::getString('q', null);
        if (K2_JVERSION == '15')
        {
            $word = $db->Quote($db->getEscaped($word, true).'%', false);
        }
        else
        {
            $word = $db->Quote($db->escape($word, true).'%', false);
        }
		
		$query = "SELECT id,name FROM #__users WHERE name LIKE ".$word." OR username LIKE ".$word." OR email LIKE ".$word;
        $db->setQuery($query);
        $result = $db->loadObjectList();
		
        require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
        $json = new Services_JSON;
        echo $json->encode($result);
        $mainframe->close();
	}

}
