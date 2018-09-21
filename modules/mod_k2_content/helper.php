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

class modK2ContentHelper
{
    public static function getItems(&$params, $format = 'html')
    {
        jimport('joomla.filesystem.file');

        $app = JFactory::getApplication();
        $db = JFactory::getDbo();

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION != '15') ? $jnow->toSql() : $jnow->toMySQL();
        $nullDate = $db->getNullDate();

        $componentParams = JComponentHelper::getParams('com_k2');

        $limit = $params->get('itemCount', 5);
        $cid = $params->get('category_id', null);
        $ordering = $params->get('itemsOrdering', '');
        $limitstart = JRequest::getInt('limitstart');

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

        // Sources (prepare the DB query)
        if ($params->get('source') == 'specific') {
            $value = $params->get('items');
            $current = array();
            if (is_string($value) && !empty($value)) {
                $current[] = $value;
            }
            if (is_array($value)) {
                $current = $value;
            }

            $items = array();

            foreach ($current as $id) {
                $query = "SELECT i.*, c.name AS categoryname, c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams
                    FROM #__k2_items AS i
                    LEFT JOIN #__k2_categories AS c ON c.id = i.catid
                    WHERE i.published = 1
                        AND i.access {$aclCheck}
                        AND i.trash = 0
                        AND c.published = 1
                        AND c.access {$aclCheck}
                        AND c.trash = 0
                        AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                        AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")
                        AND i.id={$id}";

                if ($languageFilter) {
                    $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
                }

                $db->setQuery($query);
                $item = $db->loadObject();

                if ($item) {
                    $items[] = $item;
                }
            }
        } else {
            $query = "SELECT i.*, ";

            if ($ordering == 'modified') {
                $query .= " CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END AS lastChanged, ";
            }

            $query .= "c.name AS categoryname, c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

            if ($ordering == 'best') {
                $query .= ", (r.rating_sum/r.rating_count) AS rating";
            }

            if ($ordering == 'comments') {
                $query .= ", COUNT(comments.id) AS numOfComments";
            }

            $query .= " FROM #__k2_items AS i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid";

            if ($ordering == 'best') {
                $query .= " LEFT JOIN #__k2_rating AS r ON r.itemID = i.id";
            }

            if ($ordering == 'comments') {
                $query .= " LEFT JOIN #__k2_comments AS comments ON comments.itemID = i.id";
            }

            $tagsFilter = $params->get('tags');
            if ($tagsFilter && is_array($tagsFilter) && count($tagsFilter)) {
                $query .= " INNER JOIN #__k2_tags_xref tags_xref ON tags_xref.itemID = i.id";
            }

            $query .= " WHERE i.published = 1
                AND i.access {$aclCheck}
                AND i.trash = 0
                AND c.published = 1
                AND c.access {$aclCheck}
                AND c.trash = 0
                AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")";

            if ($params->get('catfilter') && !is_null($cid)) {
                if ($params->get('getChildren')) {
                    $itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
                    $categories = $itemListModel->getCategoryTree($cid);
                    $sql = @implode(',', $categories);
                    $query .= " AND i.catid IN ({$sql})";
                } else {
                    if (is_array($cid)) {
                        $query .= " AND i.catid IN(".implode(',', $cid).")";
                    } else {
                        $query .= " AND i.catid = ".(int)$cid;
                    }
                }
            }

            $tagsFilter = $params->get('tags');
            if ($tagsFilter && is_array($tagsFilter) && count($tagsFilter)) {
                $query .= " AND tags_xref.tagID IN(".implode(',', $tagsFilter).")";
            }

            $usersFilter = $params->get('users');
            if ($usersFilter && is_array($usersFilter) && count($usersFilter)) {
                $query .= " AND i.created_by IN(".implode(',', $usersFilter).") AND i.created_by_alias = ''";
            }

            if ($params->get('FeaturedItems') == '0') {
                $query .= " AND i.featured != 1";
            }

            if ($params->get('FeaturedItems') == '2') {
                $query .= " AND i.featured = 1";
            }

            if ($params->get('videosOnly')) {
                $query .= " AND (i.video IS NOT NULL AND i.video!='')";
            }

            if ($languageFilter) {
                $query .= " AND i.language IN ({$languageFilter}) AND c.language IN ({$languageFilter})";
            }

            if ($ordering == 'comments') {
                $query .= " AND comments.published = 1";
            }

            switch ($ordering) {

                case 'date':
                    $orderby = 'i.created ASC';
                    break;

                case 'rdate':
                    $orderby = 'i.created DESC';
                    break;

                case 'alpha':
                    $orderby = 'i.title';
                    break;

                case 'ralpha':
                    $orderby = 'i.title DESC';
                    break;

                case 'order':
                    if ($params->get('FeaturedItems') == '2') {
                        $orderby = 'i.featured_ordering';
                    } else {
                        $orderby = 'i.ordering';
                    }
                    break;

                case 'rorder':
                    if ($params->get('FeaturedItems') == '2') {
                        $orderby = 'i.featured_ordering DESC';
                    } else {
                        $orderby = 'i.ordering DESC';
                    }
                    break;

                case 'hits':
                    if ($params->get('popularityRange')) {
                        $query .= " AND i.created > DATE_SUB('{$now}', INTERVAL ".$params->get('popularityRange')." DAY) ";
                    }
                    $orderby = 'i.hits DESC';
                    break;

                case 'rand':
                    $orderby = 'RAND()';
                    break;

                case 'best':
                    $orderby = 'rating DESC';
                    break;

                case 'comments':
                    if ($params->get('popularityRange')) {
                        $query .= " AND i.created > DATE_SUB('{$now}', INTERVAL ".$params->get('popularityRange')." DAY) ";
                    }
                    $orderby = 'numOfComments DESC';
                    break;

                case 'modified':
                    $orderby = 'lastChanged DESC';
                    break;

                case 'publishUp':
                    $orderby = 'i.publish_up DESC';
                    break;

                default:
                    $orderby = 'i.id DESC';
                    break;
            }

            $query .= " GROUP BY i.id ORDER BY ".$orderby;

            $db->setQuery($query, 0, $limit);
            $items = $db->loadObjectList();
        }

        // Render the query results
        $model = K2Model::getInstance('Item', 'K2Model');

        // Import plugins
        $dispatcher = JDispatcher::getInstance();
        if ($params->get('JPlugins', 1)) {
            JPluginHelper::importPlugin('content');
        }
        if ($params->get('K2Plugins', 1)) {
            JPluginHelper::importPlugin('k2');
        }

        if (count($items)) {
            foreach ($items as $item) {

                // Item (read more...) link
                $item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

                // Category link
                if ($params->get('itemCategory')) {
                    $item->categoryLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias))));
                }

                // Title cleanup
                $item->title = JFilterOutput::ampReplace($item->title);

                // Tags
                if ($params->get('itemTags')) {
                    $tags = $model->getItemTags($item->id);
                    for ($i = 0; $i < count($tags); $i++) {
                        $tags[$i]->link = JRoute::_(K2HelperRoute::getTagRoute($tags[$i]->name));
                    }
                    $item->tags = $tags;
                }

                // Introtext
                $item->text = '';
                if ($params->get('itemIntroText')) {
                    // Word limit
                    if ($params->get('itemIntroTextWordLimit')) {
                        $item->text .= K2HelperUtilities::wordLimit($item->introtext, $params->get('itemIntroTextWordLimit'));
                    } else {
                        $item->text .= $item->introtext;
                    }
                }

                // Item image
                if ($params->get('itemImage')) {
                    if ($componentParams->get('imageTimestamp')) {
                        $date = JFactory::getDate($item->modified);
                        $timestamp = '?t='.$date->toUnix();
                    } else {
                        $timestamp = '';
                    }

                    $imageFilenamePrefix = md5("Image".$item->id);
                    $imagePathPrefix = JUri::base(true).'/media/k2/items/cache/'.$imageFilenamePrefix;

                    // Do we have an image uploaded? (simply check one size)
                    if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$imageFilenamePrefix.'_Generic.jpg')) {
                        $item->imageGeneric = $imagePathPrefix.'_Generic.jpg'.$timestamp;
                        $item->imageXSmall  = $imagePathPrefix.'_XS.jpg'.$timestamp;
                        $item->imageSmall   = $imagePathPrefix.'_S.jpg'.$timestamp;
                        $item->imageMedium  = $imagePathPrefix.'_M.jpg'.$timestamp;
                        $item->imageLarge   = $imagePathPrefix.'_L.jpg'.$timestamp;
                        $item->imageXLarge  = $imagePathPrefix.'_XL.jpg'.$timestamp;
                    }

                    // Select the size to use
                    $image = 'image'.$params->get('itemImgSize', 'Small');
                    if (isset($item->$image)) {
                        $item->image = $item->$image;
                    }
                }

                // Video
                if ($params->get('itemVideo') && $format != 'feed') {
                    $params->set('vfolder', 'media/k2/videos');
                    $params->set('afolder', 'media/k2/audio');
                    $tmp = new stdClass;
                    $tmp->text = $item->video;
                    if ($params->get('JPlugins', 1)) {
                        if (K2_JVERSION != '15') {
                            $dispatcher->trigger('onContentPrepare', array('mod_k2_content.', &$tmp, &$params, $limitstart));
                        } else {
                            $dispatcher->trigger('onPrepareContent', array(&$tmp, &$params, $limitstart));
                        }
                    }
                    if ($params->get('K2Plugins', 1)) {
                        $dispatcher->trigger('onK2PrepareContent', array(&$tmp, &$params, $limitstart));
                    }
                    $item->video = $tmp->text;
                }

                // Extra fields
                if ($params->get('itemExtraFields')) {
                    $item->extra_fields = $model->getItemExtraFields($item->extra_fields, $item);

                    // Plugin rendering in extra fields
                    if (is_array($item->extra_fields)) {
                        foreach ($item->extra_fields as $key => $extraField) {
                            if ($extraField->type == 'textarea' || $extraField->type == 'textfield') {
                                $tmp = new stdClass;
                                $tmp->text = $extraField->value;
                                if ($params->get('JPlugins', 1)) {
                                    if (K2_JVERSION != '15') {
                                        $dispatcher->trigger('onContentPrepare', array('mod_k2_content', &$tmp, &$params, $limitstart));
                                    } else {
                                        $dispatcher->trigger('onPrepareContent', array(&$tmp, &$params, $limitstart));
                                    }
                                }
                                if ($params->get('K2Plugins', 1)) {
                                    $dispatcher->trigger('onK2PrepareContent', array(&$tmp, &$params, $limitstart));
                                }
                                $extraField->value = $tmp->text;
                            }
                        }
                    }
                }

                // Attachments
                if ($params->get('itemAttachments')) {
                    $item->attachments = $model->getItemAttachments($item->id);
                }

                // Comments counter
                if ($params->get('itemCommentsCounter')) {
                    $item->numOfComments = $model->countItemComments($item->id);
                }

                // Plugins
                if ($format != 'feed') {
                    $params->set('parsedInModule', 1); // for plugins to know when they are parsed inside this module

                    $item->event = new stdClass;

                    $item->event->BeforeDisplay = '';
                    $item->event->AfterDisplay = '';
                    $item->event->AfterDisplayTitle = '';
                    $item->event->BeforeDisplayContent = '';
                    $item->event->AfterDisplayContent = '';

                    // Joomla Plugins
                    if ($params->get('JPlugins', 1)) {
                        if (K2_JVERSION != '15') {
                            $item->event->BeforeDisplay = '';
                            $item->event->AfterDisplay = '';

                            $results = $dispatcher->trigger('onContentAfterTitle', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->AfterDisplayTitle = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onContentBeforeDisplay', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->BeforeDisplayContent = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onContentAfterDisplay', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->AfterDisplayContent = trim(implode("\n", $results));

                            $dispatcher->trigger('onContentPrepare', array('mod_k2_content', &$item, &$params, $limitstart));
                        } else {
                            $results = $dispatcher->trigger('onBeforeDisplay', array(&$item, &$params, $limitstart));
                            $item->event->BeforeDisplay = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplay', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplay = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplayTitle', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplayTitle = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onBeforeDisplayContent', array(&$item, &$params, $limitstart));
                            $item->event->BeforeDisplayContent = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplayContent', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplayContent = trim(implode("\n", $results));

                            $dispatcher->trigger('onPrepareContent', array(&$item, &$params, $limitstart));
                        }
                    }

                    // Initialize K2 plugin events
                    $item->event->K2BeforeDisplay = '';
                    $item->event->K2AfterDisplay = '';
                    $item->event->K2AfterDisplayTitle = '';
                    $item->event->K2BeforeDisplayContent = '';
                    $item->event->K2AfterDisplayContent = '';
                    $item->event->K2CommentsCounter = '';

                    // K2 Plugins
                    if ($params->get('K2Plugins', 1)) {
                        $results = $dispatcher->trigger('onK2BeforeDisplay', array(&$item, &$params, $limitstart));
                        $item->event->K2BeforeDisplay = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplay', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplay = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplayTitle', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplayTitle = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2BeforeDisplayContent', array(&$item, &$params, $limitstart));
                        $item->event->K2BeforeDisplayContent = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplayContent', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplayContent = trim(implode("\n", $results));

                        $dispatcher->trigger('onK2PrepareContent', array(&$item, &$params, $limitstart));

                        if ($params->get('itemCommentsCounter')) {
                            $results = $dispatcher->trigger('onK2CommentsCounter', array(&$item, &$params, $limitstart));
                            $item->event->K2CommentsCounter = trim(implode("\n", $results));
                        }
                    }
                }

                // Restore the intotext variable after plugins are executed
                $item->introtext = $item->text;

                // Remove the plugin tags
                $item->introtext = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $item->introtext);

                // Author (user)
                if ($params->get('itemAuthor')) {
                    if (!empty($item->created_by_alias)) {
                        $item->author = $item->created_by_alias;
                        $item->authorGender = null;
                        $item->authorDescription = null;
                        if ($params->get('itemAuthorAvatar')) {
                            $item->authorAvatar = K2HelperUtilities::getAvatar('alias');
                        }
                        $item->authorLink = JUri::root(true);
                    } else {
                        $author = JFactory::getUser($item->created_by);
                        $item->author = $author->name;

                        $query = "SELECT `description`, `gender` FROM #__k2_users WHERE userID=".(int)$author->id;
                        $db->setQuery($query, 0, 1);

                        $result = $db->loadObject();
                        if ($result) {
                            $item->authorGender = $result->gender;
                            $item->authorDescription = $result->description;
                        } else {
                            $item->authorGender = null;
                            $item->authorDescription = null;
                        }

                        if ($params->get('itemAuthorAvatar')) {
                            $item->authorAvatar = K2HelperUtilities::getAvatar($author->id, $author->email, $componentParams->get('userImageWidth'));
                        }

                        $item->authorLink = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
                    }
                }

                // Author (user) avatar
                if ($params->get('itemAuthorAvatar') && !isset($item->authorAvatar)) {
                    if (!empty($item->created_by_alias)) {
                        $item->authorAvatar = K2HelperUtilities::getAvatar('alias');
                        $item->authorLink = JUri::root(true);
                    } else {
                        $jAuthor = JFactory::getUser($item->created_by);
                        $item->authorAvatar = K2HelperUtilities::getAvatar($jAuthor->id, $jAuthor->email, $componentParams->get('userImageWidth'));
                        $item->authorLink = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
                    }
                }

                // Populate the output array
                $rows[] = $item;
            }

            return $rows;
        }
    }
}
