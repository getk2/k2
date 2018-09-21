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

class modK2StatsHelper
{
    public static function getLatestItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT i.*, v.name AS author FROM #__k2_items as i
        LEFT JOIN #__k2_categories AS c ON c.id = i.catid
        LEFT JOIN #__users AS v ON v.id = i.created_by
        WHERE i.trash = 0  AND c.trash = 0
        ORDER BY i.created DESC";
        if (K2_JVERSION != '15') {
            $query = JString::str_ireplace('#__groups', '#__viewlevels', $query);
            $query = JString::str_ireplace('g.name', 'g.title', $query);
        }
        $db->setQuery($query, 0, 10);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public static function getPopularItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT i.*, v.name AS author FROM #__k2_items as i
        LEFT JOIN #__k2_categories AS c ON c.id = i.catid
        LEFT JOIN #__users AS v ON v.id = i.created_by
        WHERE i.trash = 0  AND c.trash = 0
        ORDER BY i.hits DESC";
        $db->setQuery($query, 0, 10);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public static function getMostCommentedItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT i.*, v.name AS author, (SELECT COUNT(*) FROM #__k2_comments WHERE itemID = i.id) AS numOfComments FROM #__k2_items as i
        LEFT JOIN #__k2_categories AS c ON c.id = i.catid
        LEFT JOIN #__users AS v ON v.id = i.created_by
        WHERE i.trash = 0  AND c.trash = 0
        ORDER BY numOfComments DESC";
        $db->setQuery($query, 0, 10);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public static function getLatestComments()
    {
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_comments ORDER BY commentDate DESC";
        $db->setQuery($query, 0, 10);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public static function getStatistics()
    {
        $statistics = new stdClass;
        $statistics->numOfItems = self::countItems();
        $statistics->numOfTrashedItems = self::countTrashedItems();
        $statistics->numOfFeaturedItems = self::countFeaturedItems();
        $statistics->numOfComments = self::countComments();
        $statistics->numOfCategories = self::countCategories();
        $statistics->numOfTrashedCategories = self::countTrashedCategories();
        $statistics->numOfUsers = self::countUsers();
        $statistics->numOfUserGroups = self::countUserGroups();
        $statistics->numOfTags = self::countTags();
        return $statistics;
    }

    public static function countItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_items";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countTrashedItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE trash=1";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countFeaturedItems()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE featured=1";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countComments()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_comments";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countCategories()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_categories";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countTrashedCategories()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_categories WHERE trash=1";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countUsers()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_users";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countUserGroups()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_user_groups";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    public static function countTags()
    {
        $db = JFactory::getDbo();
        $query = "SELECT COUNT(*) FROM #__k2_tags";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }
}
