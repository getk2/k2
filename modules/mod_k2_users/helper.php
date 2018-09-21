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

require_once(JPATH_SITE.'/components/com_k2/helpers/route.php');
require_once(JPATH_SITE.'/components/com_k2/helpers/utilities.php');

class modK2UsersHelper
{
    public static function getUsers(&$params)
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION != '15') ? $jnow->toSql() : $jnow->toMySQL();
        $nullDate = $db->getNullDate();

        // Get ACL
        $user = JFactory::getUser();
        if (K2_JVERSION != '15') {
            $userLevels = array_unique($user->getAuthorisedViewLevels());
            $aclCheck = 'IN('.implode(',', $userLevels).')';
        } else {
            $aid = $user->get('aid');
            $aclCheck = '<= '.$user->get('aid');
        }

        // Get language on Joomla 2.5+
        $languageFilter = '';
        if (K2_JVERSION != '15') {
            if ($app->getLanguageFilter()) {
                $languageTag = JFactory::getLanguage()->getTag();
                $languageFilter = $db->Quote($languageTag).", ".$db->Quote('*');
            }
        }

        $userObjects = array();

        if ($params->get('source') == 'specific' && $params->get('userIDs')) {
            $IDs = array();
            if (is_string($params->get('userIDs'))) {
                $IDs[] = $params->get('userIDs');
            } else {
                $IDs = $params->get('userIDs');
            }

            $query = "SELECT users.name, users.email, users.id AS UID, profiles.*
                FROM #__users AS users
                LEFT JOIN #__k2_users AS profiles ON users.id=profiles.userID
                WHERE users.block=0 AND users.id IN (".implode(',', $IDs).")";

            $db->setQuery($query);
            $userObjects = $db->loadObjectList();

            $newUserObjects = array();
            foreach ($IDs as $id) {
                foreach ($userObjects as $uO) {
                    if ($uO->UID == $id) {
                        $newUserObjects[] = $uO;
                        break;
                    }
                }
            }
            $userObjects = $newUserObjects;
        } else {
            switch ($params->get('filter', 0)) {

                // By K2 user group
                case 0:
                    $query = "SELECT users.name, users.email, users.id AS UID, profiles.*";

                    if ($params->get('ordering') == 'recent') {
                        $query .= ", MAX(i.created) AS counter";
                    }

                    $query .= " FROM #__users AS users
                        LEFT JOIN #__k2_users AS profiles ON users.id=profiles.userID";

                    if ($params->get('ordering') == 'recent') {
                        $query .= " LEFT JOIN #__k2_items AS i ON users.id=i.created_by LEFT JOIN #__k2_categories AS c ON i.catid=c.id";
                    }

                    $query .= " WHERE users.block=0 AND profiles.`group`=".(int)$params->get('K2UserGroup');

                    if ($params->get('ordering') == 'recent') {
                        $query .= " AND i.published = 1
                            AND i.trash = 0
                            AND i.access {$aclCheck}
                            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                            AND i.created_by_alias=''
                            AND c.published = 1
                            AND c.trash = 0
                            AND c.access {$aclCheck}";

                        if ($languageFilter) {
                            $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                        }
                    }

                    switch ($params->get('ordering')) {
                        case 'alpha':
                            $orderby = "users.name";
                            break;
                        case 'recent':
                            $orderby = "counter DESC";
                            break;
                        case 'random':
                            $orderby = "RAND()";
                            break;
                    }

                    $query .= " GROUP BY users.id ORDER BY {$orderby}";
                    break;

                // With most items
                case 1:
                    $query = "SELECT users.name, users.email, users.id AS UID, profiles.*, COUNT(i.id) AS counter
                        FROM #__users AS users
                        LEFT JOIN #__k2_users AS profiles ON users.id=profiles.userID
                        LEFT JOIN #__k2_items AS i ON users.id=i.created_by
                        LEFT JOIN #__k2_categories AS c ON i.catid=c.id
                        WHERE users.block=0
                            AND i.published = 1
                            AND i.trash = 0
                            AND i.access {$aclCheck}
                            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                            AND i.created_by_alias=''
                            AND c.published = 1
                            AND c.trash = 0
                            AND c.access {$aclCheck}";

                    if ($languageFilter) {
                        $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                    }

                    $query .= " GROUP BY users.id ORDER BY counter DESC";
                    break;

                // With most popular items
                case 2:
                    $query = "SELECT users.name, users.email, users.id AS UID, profiles.*, MAX(i.hits) AS counter
                        FROM #__users AS users
                        LEFT JOIN #__k2_users AS profiles ON users.id=profiles.userID
                        LEFT JOIN #__k2_items AS i ON users.id=i.created_by
                        LEFT JOIN #__k2_categories AS c ON i.catid=c.id
                        WHERE users.block=0
                            AND i.published = 1
                            AND i.trash = 0
                            AND i.access {$aclCheck}
                            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                            AND i.created_by_alias=''
                            AND c.published = 1
                            AND c.trash = 0
                            AND c.access {$aclCheck}";

                    if ($languageFilter) {
                        $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                    }

                    $query .= " GROUP BY users.id ORDER BY counter DESC";
                    break;

                // With most commented items
                case 3:
                    $query = "SELECT users.name, users.email, users.id AS UID, profiles.*, COUNT(comment.id) AS counter
                        FROM #__users AS users
                        LEFT JOIN #__k2_users AS profiles ON users.id=profiles.userID
                        LEFT JOIN #__k2_items AS i ON users.id=i.created_by
                        LEFT JOIN #__k2_categories AS c ON i.catid=c.id
                        LEFT JOIN #__k2_comments AS comment ON i.id=comment.itemID
                        WHERE users.block=0
                            AND i.published = 1
                            AND i.trash = 0
                            AND i.access {$aclCheck}
                            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                            AND i.created_by_alias=''
                            AND c.published = 1
                            AND c.trash = 0
                            AND c.access {$aclCheck}";

                    if ($languageFilter) {
                        $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                    }

                    $query .= " GROUP BY users.id ORDER BY counter DESC";
                    break;
            }

            $db->setQuery($query, 0, $params->get('limit', 4));
            $userObjects = $db->loadObjectList();
        }

        // Render the query results
        if (count($userObjects)) {
            foreach ($userObjects as $userObject) {
                $userObject->avatar = K2HelperUtilities::getAvatar($userObject->UID, $userObject->email, $params->get('userImageWidth'));
                $userObject->link = JRoute::_(K2HelperRoute::getUserRoute($userObject->UID));
                $userObject->feed = JRoute::_(K2HelperRoute::getUserRoute($userObject->UID).'&format=feed');
                $userObject->url = htmlspecialchars($userObject->url, ENT_QUOTES, 'UTF-8');

                if ($params->get('userItemCount')) {
                    $query = "SELECT i.*, c.name AS categoryname, c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams
                        FROM #__k2_items AS i
                        LEFT JOIN #__k2_categories AS c ON c.id = i.catid
                        WHERE i.published = 1
                            AND i.trash = 0
                            AND i.access {$aclCheck}
                            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                            AND i.created_by=".(int)$userObject->UID."
                            AND i.created_by_alias=''
                            AND c.published = 1
                            AND c.trash = 0
                            AND c.access {$aclCheck}";

                    if ($languageFilter) {
                        $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                    }

                    $query .= " GROUP BY i.id ORDER BY i.created DESC";

                    $db->setQuery($query, 0, $params->get('userItemCount'));
                    $userObject->items = $db->loadObjectList();

                    if (count($userObject->items)) {
                        foreach ($userObject->items as $item) {
                            $link = K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias));
                            $item->link = urldecode(JRoute::_($link));
                            $item->categoryLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias))));
                        }
                    }
                } else {
                    $userObject->items = null;
                }
            }
        }
        return $userObjects;
    }
}
