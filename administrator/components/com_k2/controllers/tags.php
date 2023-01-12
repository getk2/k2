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

class K2ControllerTags extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'tags');
        parent::display();
    }

    public function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('tags');
        $model->publish();
    }

    public function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('tags');
        $model->unpublish();
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('tags');
        $model->remove();
    }

    public function add()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=tag');
    }

    public function edit()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $app->redirect('index.php?option=com_k2&view=tag&cid='.$cid[0]);
    }

    public function removeOrphans()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('tags');
        $model->removeOrphans();
    }
}
