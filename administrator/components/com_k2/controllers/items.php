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
		$application = JFactory::getApplication();
		$application->redirect('index.php?option=com_k2&view=item');
	}

	function edit()
	{
		$application = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$application->redirect('index.php?option=com_k2&view=item&cid='.$cid[0]);
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

	function saveBatch()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('items');
		$model->saveBatch();
	}

	function logStats()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		$status = JRequest::getInt('status');
		$response = JRequest::getString('response');
		$date = JFactory::getDate();
		$now = version_compare(JVERSION, '2.5', 'ge') ? $date->toSql() : $date->toMySQL();
		$db = JFactory::getDbo();

		$query = 'DELETE FROM #__k2_log';
		$db->setQuery($query);
		$db->query();

		$query = 'INSERT INTO #__k2_log VALUES('.$status.', '.$db->quote($response).', '.$db->quote($now).')';
		$db->setQuery($query);
		$db->query();

		exit;
	}
}
