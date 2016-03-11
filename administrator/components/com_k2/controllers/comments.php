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

class K2ControllerComments extends K2Controller
{

    public function display($cachable = false, $urlparams = array())
    {
        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
        JRequest::setVar('view', 'comments');
        parent::display();
    }

    function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->publish();
    }

    function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->unpublish();
    }

    function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->remove();
    }

    function deleteUnpublished()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->deleteUnpublished();
    }

    function saveComment()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('comments');
        $model->save();
    }

}
