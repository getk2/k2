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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelTag extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Tag', 'Table');
        $row->load($cid);
        return $row;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        $row = JTable::getInstance('K2Tag', 'Table');

        if (!$row->bind(JRequest::get('post'))) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=tags');
        }

        if (!$row->check()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=tag&cid='.$row->id);
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=tags');
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_TAG_SAVED');
                $link = 'index.php?option=com_k2&view=tag&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_TAG_SAVED');
                $link = 'index.php?option=com_k2&view=tag';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_TAG_SAVED');
                $link = 'index.php?option=com_k2&view=tags';
                break;
        }
        $app->enqueueMessage($msg);
        $app->redirect($link);
    }

    public function addTag()
    {
        $app = JFactory::getApplication();

        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_k2');
        if ($user->gid < 24 && $params->get('lockTags')) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }

        $tag = JRequest::getString('tag');
        $tag = str_replace('-', '', $tag);
        $tag = str_replace('.', '', $tag);

        $response = new stdClass;
        $response->name = $tag;

        if (empty($tag)) {
            $response->msg = JText::_('K2_YOU_NEED_TO_ENTER_A_TAG', true);
            echo json_encode($response);
            $app->close();
        }

        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_tags WHERE name=".$db->Quote($tag);
        $db->setQuery($query);
        $result = $db->loadResult();

        if ($result > 0) {
            $response->msg = JText::_('K2_TAG_ALREADY_EXISTS', true);
            echo json_encode($response);
            $app->close();
        }

        $row = JTable::getInstance('K2Tag', 'Table');
        $row->name = $tag;
        $row->published = 1;
        $row->store();

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        $response->id = $row->id;
        $response->status = 'success';
        $response->msg = JText::_('K2_TAG_ADDED_TO_AVAILABLE_TAGS_LIST', true);
        echo json_encode($response);

        $app->close();
    }

    public function tags()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $word = JRequest::getString('q', null);
        $id = JRequest::getInt('id');
        if (K2_JVERSION == '15') {
            $word = $db->Quote($db->getEscaped($word, true).'%', false);
        } else {
            $word = $db->Quote($db->escape($word, true).'%', false);
        }

        if ($id) {
            $query = "SELECT id,name FROM #__k2_tags WHERE name LIKE ".$word;
            $db->setQuery($query);
            $result = $db->loadObjectList();
        } else {
            $query = "SELECT name FROM #__k2_tags WHERE name LIKE ".$word;
            $db->setQuery($query);
            $result = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
        }

        echo json_encode($result);
        $app->close();
    }
}
