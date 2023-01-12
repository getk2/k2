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

class K2ModelUserGroups extends K2Model
{

    function getData()
    {

        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', '', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        $query = "SELECT userGroup.*, (SELECT COUNT(DISTINCT userID) FROM #__k2_users WHERE `group`=userGroup.id) AS numOfUsers FROM #__k2_user_groups AS userGroup";

        if (!$filter_order)
        {
            $filter_order = "name";
        }

        $query .= " ORDER BY {$filter_order} {$filter_order_Dir}";

        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        return $rows;
    }

    function getTotal()
    {

        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();

        $query = "SELECT COUNT(*) FROM #__k2_user_groups";

        $db->setQuery($query);
        $total = $db->loadresult();
        return $total;
    }

    function remove()
    {

        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2UserGroup', 'Table');
            $row->load($id);
            $row->delete($id);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		$app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $app->redirect('index.php?option=com_k2&view=usergroups');
    }

}
