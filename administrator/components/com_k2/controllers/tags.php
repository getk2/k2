<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
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

	function publish()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('tags');
		$model->publish();
	}

	function unpublish()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('tags');
		$model->unpublish();
	}

	function remove()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('tags');
		$model->remove();
	}

	function add()
	{
		$application = JFactory::getApplication();
		$application->redirect('index.php?option=com_k2&view=tag');
	}

	function edit()
	{
		$application = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$application->redirect('index.php?option=com_k2&view=tag&cid='.$cid[0]);
	}

	function element()
	{
		JRequest::setVar('view', 'tags');
		JRequest::setVar('layout', 'element');
		parent::display();
	}

	function removeOrphans()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('tags');
		$model->removeOrphans();
	}

}
