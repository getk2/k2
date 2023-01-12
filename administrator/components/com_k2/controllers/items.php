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

class K2ControllerItems extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'items');
        parent::display();
    }

    public function publish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->publish();
    }

    public function unpublish()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->unpublish();
    }

    public function saveorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $result = $model->saveorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw') {
            echo '1';
            return $this;
        } else {
            $this->setRedirect('index.php?option=com_k2&view=items', JText::_('K2_NEW_ORDERING_SAVED'));
        }
    }

    public function orderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->orderup();
    }

    public function orderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->orderdown();
    }

    public function savefeaturedorder()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $result = $model->savefeaturedorder();
        $document = JFactory::getDocument();
        if ($document->getType() == 'raw') {
            echo '1';
            return $this;
        } else {
            $this->setRedirect('index.php?option=com_k2&view=items', JText::_('K2_NEW_FEATURED_ORDERING_SAVED'));
        }
    }

    public function featuredorderup()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featuredorderup();
    }

    public function featuredorderdown()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featuredorderdown();
    }

    public function accessregistered()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accessregistered();
    }

    public function accessspecial()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accessspecial();
    }

    public function accesspublic()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->accesspublic();
    }

    public function featured()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->featured();
    }

    public function trash()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->trash();
    }

    public function restore()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->restore();
    }

    public function remove()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->remove();
    }

    public function add()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_k2&view=item');
    }

    public function edit()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $app->redirect('index.php?option=com_k2&view=item&cid='.$cid[0]);
    }

    public function copy()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->copy();
    }

    public function import()
    {
        $model = $this->getModel('items');
        if (K2_JVERSION != '15') {
            $model->importJ16();
        } else {
            $model->import();
        }
    }

    public function saveBatch()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('items');
        $model->saveBatch();
    }

    public function logStats()
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
