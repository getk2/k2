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

class K2ControllerComments extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        require_once(JPATH_SITE.'/components/com_k2/helpers/route.php');
        JRequest::setVar('view', 'comments');
        parent::display();
    }

    public function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->publish();
    }

    public function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->unpublish();
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->remove();
    }

    public function deleteUnpublished()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->deleteUnpublished();
    }

    public function saveComment()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->save();
    }
}
