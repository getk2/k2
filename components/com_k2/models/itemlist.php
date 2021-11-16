<?php
/**
 * @version    2.11.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

class K2ModelItemlist extends K2Model
{
    private $getTotal;

    public function getData($ordering = null)
    {
        $user = JFactory::getUser();
        $aid = $user->get('aid');
        $db = JFactory::getDbo();
        $task = JRequest::getCmd('task');
        $limitstart = JRequest::getInt('limitstart', 0);
        $limit = JRequest::getInt('limit', 10);
        $config = JFactory::getConfig();

        $params = K2HelperUtilities::getParams('com_k2');

        if ($task == 'search') {
            $params->set('googleSearch', 0);
        }

        // For Falang
        $falang_driver = JPluginHelper::getPlugin('system', 'falangdriver');

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION == '15') ? $jnow->toMySQL() : $jnow->toSql();
        /*
        if (version_compare(JVERSION, '3.3', 'ge')) {
            $now = $jnow->format('%Y-%m-%d %H:%M:00');
        } else {
            $now = $jnow->toFormat('%Y-%m-%d %H:%M:00');
        }
        */
        $nullDate = $db->getNullDate();

        $query = "/* Frontend */ SELECT SQL_CALC_FOUND_ROWS i.*,";

        if ($ordering == 'modified') {
            $query .= " CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END AS lastChanged,";
        }

        $query .= " c.name AS categoryname, c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

        if ($ordering == 'best') {
            $query .= ", (r.rating_sum/r.rating_count) AS rating";
        }

        $query .= " FROM #__k2_items AS i";

        // Enforce certain INDEX when filtering by dates
        if ($ordering == 'date' || $ordering == 'rdate') {
            $query .= " USE INDEX (idx_item)";
        }

        $query .= " INNER JOIN #__k2_categories AS c ON c.id = i.catid";

        if ($ordering == 'best') {
            $query .= " LEFT JOIN #__k2_rating AS r ON r.itemID = i.id";
        }

        /*
        // Changed the query for the tag case for better performance
        if ($task == 'tag') {
            $query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags AS tags ON tags.id = tags_xref.tagID";
        }
        */

        if ($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id')) {
            $query .= " WHERE";
        } else {
            $query .= " WHERE i.published = 1 AND";
        }

        if (K2_JVERSION != '15') {
            $userACL = array_unique($user->getAuthorisedViewLevels());
            $query .= " i.access IN(".implode(',', $userACL).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $userACL).") AND c.trash = 0";

            $app = JFactory::getApplication();
            $languageFilter = $app->getLanguageFilter();
            if ($languageFilter) {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN(".$db->quote($languageTag).", ".$db->quote('*').") AND i.language IN(".$db->quote($languageTag).", ".$db->quote('*').")";
            }
        } else {
            $query .= " i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
        }

        if (!($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id'))) {
            $query .= " AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")";
            $query .= " AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")";
            /*
            $query .= " AND (i.publish_up IS NULL OR i.publish_up <= NOW()) AND (i.publish_down IS NULL OR i.publish_down >= NOW())";
            */
        }

        // Build query depending on task
        switch ($task) {
            case 'category':
                $id = JRequest::getInt('id');

                $category = JTable::getInstance('K2Category', 'Table');
                $category->load($id);
                $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

                if ($cparams->get('inheritFrom')) {
                    $parent = JTable::getInstance('K2Category', 'Table');
                    $parent->load($cparams->get('inheritFrom'));
                    $cparams = class_exists('JParameter') ? new JParameter($parent->params) : new JRegistry($parent->params);
                }

                if ($cparams->get('catCatalogMode')) {
                    $query .= " AND c.id={$id} ";
                } else {
                    $categories = $this->getCategoryTree($id);
                    sort($categories);
                    $sql = @implode(',', $categories);
                    $query .= " AND c.id IN({$sql})";
                }

                break;

            case 'user':
                $id = JRequest::getInt('id');
                $query .= " AND i.created_by={$id} AND i.created_by_alias=''";
                $categories = $params->get('userCategoriesFilter', null);
                if (is_array($categories)) {
                    if (count($categories)) {
                        sort($categories);
                        $query .= " AND c.id IN(".implode(',', $categories).")";
                    }
                }
                if (is_string($categories) && $categories > 0) {
                    $query .= " AND c.id = {$categories}";
                }
                break;

            case 'search':
                $badchars = array(
                    '#',
                    '>',
                    '<',
                    '\\'
                );
                $search = JString::trim(JString::str_ireplace($badchars, '', JRequest::getString('searchword', null)));
                $sql = $this->prepareSearch($search);
                if (!empty($sql)) {
                    $query .= $sql;
                } else {
                    $rows = array();
                    return $rows;
                }
                break;

            case 'date':
                if ((JRequest::getInt('month')) && (JRequest::getInt('year'))) {
                    $month = JRequest::getInt('month');
                    $year = JRequest::getInt('year');
                    $query .= " AND MONTH(i.created) = {$month} AND YEAR(i.created)={$year}";
                    if (JRequest::getInt('day')) {
                        $day = JRequest::getInt('day');
                        $query .= " AND DAY(i.created) = {$day}";
                    }

                    if (JRequest::getInt('catid')) {
                        $catid = JRequest::getInt('catid');
                        $query .= " AND c.id={$catid}";
                    }
                }
                break;

            case 'tag':
                $tag = JRequest::getString('tag');

                jimport('joomla.filesystem.file');

                if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php') && $task == 'tag') {
                    $lang = (K2_JVERSION == '30') ? $config->get('jflang') : $config->getValue('config.jflang');

                    $sql = "SELECT reference_id
                        FROM #__jf_content AS jfc
                        LEFT JOIN #__languages AS jfl ON jfc.language_id = jfl.".K2_JF_ID."
                        WHERE jfc.value = ".$db->Quote($tag)."
                            AND jfc.reference_table = 'k2_tags'
                            AND jfc.reference_field = 'name'
                            AND jfc.published=1";
                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();
                }

                if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_falang/falang.php') && $task == 'tag') {
                    $lang = (K2_JVERSION == '30') ? $config->get('jflang') : $config->getValue('config.jflang');

                    $sql = "SELECT reference_id
                        FROM #__falang_content AS fc
                        LEFT JOIN #__languages AS fl ON fc.language_id = fl.lang_id
                        WHERE fc.value = ".$db->Quote($tag)."
                            AND fc.reference_table = 'k2_tags'
                            AND fc.reference_field = 'name'
                            AND fc.published=1";
                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();
                }

                if (!isset($result) || $result < 1) {
                    $sql = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($tag);
                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();
                }

                $query .= " AND i.id IN(SELECT itemID FROM #__k2_tags_xref WHERE tagID=".(int)$result.")";

                /*
                if (isset($result) && $result > 0) {
                    $query .= " AND (tags.id) = {$result}";
                } else {
                    $query .= " AND (tags.name) = ".$db->Quote($tag);
                }
                */

                $categories = $params->get('categoriesFilter', null);
                if (is_array($categories)) {
                    sort($categories);
                    $query .= " AND c.id IN(".implode(',', $categories).")";
                }
                if (is_string($categories)) {
                    $query .= " AND c.id = {$categories}";
                }
                break;

            default:
                $searchIDs = $params->get('categories');
                if (is_array($searchIDs) && count($searchIDs)) {
                    if ($params->get('catCatalogMode')) {
                        sort($searchIDs);
                        $sql = @implode(',', $searchIDs);
                        $query .= " AND c.id IN({$sql})";
                    } else {
                        $result = $this->getCategoryTree($searchIDs);
                        if (count($result)) {
                            sort($result);
                            $sql = @implode(',', $result);
                            $query .= " AND c.id IN({$sql})";
                        }
                    }
                }
                break;
        }

        // Set featured flag
        if ($task == 'category' || empty($task)) {
            if (JRequest::getInt('featured') == '0') {
                $query .= " AND i.featured != 1";
            } elseif (JRequest::getInt('featured') == '2') {
                $query .= " AND i.featured = 1";
            }
        }

        // Set ordering
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
                if (JRequest::getInt('featured') == '2') {
                    $orderby = 'i.featured_ordering';
                } else {
                    $orderby = 'c.ordering, i.ordering';
                }
                break;

            case 'rorder':
                if (JRequest::getInt('featured') == '2') {
                    $orderby = 'i.featured_ordering DESC';
                } else {
                    $orderby = 'c.ordering DESC, i.ordering DESC';
                }
                break;

            case 'featured':
                $orderby = 'i.featured DESC, i.created DESC';
                break;

            case 'hits':
                $orderby = 'i.hits DESC';
                break;

            case 'rand':
                $orderby = 'RAND()';
                break;

            case 'best':
                $orderby = 'rating DESC';
                break;

            case 'modified':
                $orderby = 'lastChanged DESC';
                break;

            case 'publishUp':
                $orderby = 'i.publish_up DESC';
                break;

            case 'id':
            default:
                $orderby = 'i.id DESC';
                break;
        }

        if ($task == 'tag') {
            $query .= ' GROUP BY i.id';
        }

        $query .= ' ORDER BY '.$orderby;

        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onK2BeforeSetQuery', array(&$query));

        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();

        if (count($rows)) {
            // For Falang
            if (!empty($falang_driver)) {
                $db->setQuery($query, $limitstart, $limit);
                $db->loadResult(false);
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->getTotal = $db->loadResult(false);
                return $rows;
            }

            $db->setQuery('SELECT FOUND_ROWS();');
            $this->getTotal = $db->loadResult();
        }

        return $rows;
    }

    public function getTotal()
    {
        return $this->getTotal;
    }

    public function getCategoryTree($categories, $associations = false)
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        if (!is_array($categories)) {
            $categories = (array)$categories;
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        sort($categories);
        $key = implode('|', $categories);
        $clientID = $app->getClientId();
        static $K2CategoryTreeInstances = array();
        if (isset($K2CategoryTreeInstances[$clientID]) && array_key_exists($key, $K2CategoryTreeInstances[$clientID])) {
            return $K2CategoryTreeInstances[$clientID][$key];
        }
        $array = $categories;
        while (count($array)) {
            $query = "SELECT id
                        FROM #__k2_categories
                        WHERE parent IN(".implode(',', $array).")
                            AND id NOT IN(".implode(',', $array).")";
            if ($app->isSite()) {
                $query .= " AND published=1 AND trash=0";
                if (K2_JVERSION != '15') {
                    $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
                    if ($app->getLanguageFilter()) {
                        $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
                    }
                } else {
                    $query .= " AND access<={$aid}";
                }
            }
            $db->setQuery($query);
            $array = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();
            $categories = array_merge($categories, $array);
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        $K2CategoryTreeInstances[$clientID][$key] = $categories;
        return $categories;
    }

    public function getCategoryFirstChildren($catid, $ordering = null)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = $user->get('aid');
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0";

        if (K2_JVERSION != '15') {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($app->getLanguageFilter()) {
                $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND access<={$aid} ";
        }

        switch ($ordering) {

            case 'order':
                $order = " ordering ASC";
                break;

            case 'alpha':
                $order = " name ASC";
                break;

            case 'ralpha':
                $order = " name DESC";
                break;

            case 'reversedefault':
                $order = " id DESC";
                break;

            default:
                $order = " id ASC";
                break;
        }

        $query .= " ORDER BY {$order}";

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
        return $rows;
    }

    public function countCategoryItems($id)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $id = (int)$id;
        $db = JFactory::getDbo();

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION == '15') ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $categories = $this->getCategoryTree($id);
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid IN(".implode(',', $categories).") AND published=1 AND trash=0";

        if (K2_JVERSION != '15') {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($app->getLanguageFilter()) {
                $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND access<=".$aid;
        }

        $query .= " AND (publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now).") AND (publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now).")";
        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }

    public function getUserProfile($id = null)
    {
        $db = JFactory::getDbo();
        if (is_null($id)) {
            $id = JRequest::getInt('id');
        } else {
            $id = (int)$id;
        }
        $query = "SELECT id, gender, description, image, url, `group`, plugins FROM #__k2_users WHERE userID={$id}";
        $db->setQuery($query);
        $row = $db->loadObject();
        return $row;
    }

    public function getAuthorLatest($itemID, $limit, $userID)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $itemID = (int)$itemID;
        $userID = (int)$userID;
        $limit = (int)$limit;
        $db = JFactory::getDbo();

        $params = K2HelperUtilities::getParams('com_k2');

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION == '15') ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $query = "SELECT i.*, c.alias AS categoryalias
            FROM #__k2_items AS i
            LEFT JOIN #__k2_categories c ON c.id = i.catid
            WHERE i.id != {$itemID}
                AND i.published = 1
                AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")";

        if (K2_JVERSION != '15') {
            $query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($app->getLanguageFilter()) {
                $query .= " AND i.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND i.access <= {$aid}";
        }

        $query .= " AND i.trash = 0
            AND i.created_by = {$userID}
            AND i.created_by_alias=''
            AND c.published = 1";

        if (K2_JVERSION != '15') {
            $query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($app->getLanguageFilter()) {
                $query .= " AND c.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND c.access <= {$aid}";
        }

        $query .= " AND c.trash = 0
            ORDER BY i.created DESC";

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();

        foreach ($rows as $item) {
            // Image
            $item->imageXSmall = '';
            $item->imageSmall = '';
            $item->imageMedium = '';
            $item->imageLarge = '';
            $item->imageXLarge = '';

            $imageTimestamp = '';
            $dateModified = ((int) $item->modified) ? $item->modified : '';
            if ($params->get('imageTimestamp', 1) && $dateModified) {
                $imageTimestamp = '?t='.strftime("%Y%m%d_%H%M%S", strtotime($dateModified));
            }

            $imageFilenamePrefix = md5("Image".$item->id);
            $imagePathPrefix = JUri::base(true).'/media/k2/items/cache/'.$imageFilenamePrefix;

            // Check if the "generic" variant exists
            if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$imageFilenamePrefix.'_Generic.jpg')) {
                $item->imageGeneric = $imagePathPrefix.'_Generic.jpg'.$imageTimestamp;
                $item->imageXSmall  = $imagePathPrefix.'_XS.jpg'.$imageTimestamp;
                $item->imageSmall   = $imagePathPrefix.'_S.jpg'.$imageTimestamp;
                $item->imageMedium  = $imagePathPrefix.'_M.jpg'.$imageTimestamp;
                $item->imageLarge   = $imagePathPrefix.'_L.jpg'.$imageTimestamp;
                $item->imageXLarge  = $imagePathPrefix.'_XL.jpg'.$imageTimestamp;

                $item->imageProperties = new stdClass;
                $item->imageProperties->filenamePrefix = $imageFilenamePrefix;
                $item->imageProperties->pathPrefix = $imagePathPrefix;
            }
        }
        return $rows;
    }

    public function getRelatedItems($itemID, $tags, $params)
    {
        $app = JFactory::getApplication();
        $limit = $params->get('itemRelatedLimit', 10);
        $itemID = (int)$itemID;

        foreach ($tags as $tag) {
            $tagIDs[] = $tag->id;
        }
        JArrayHelper::toInteger($tagIDs);
        sort($tagIDs);
        $sql = implode(',', $tagIDs);

        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $db = JFactory::getDbo();

        $jnow = JFactory::getDate();
        $now = (K2_JVERSION == '15') ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $query = "SELECT itemID
            FROM #__k2_tags_xref
            WHERE tagID IN ({$sql})
                AND itemID != {$itemID}
            GROUP BY itemID";
        $db->setQuery($query);

        $itemsIDs = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();

        if (!count($itemsIDs)) {
            return array();
        }
        sort($itemsIDs);
        $sql = implode(',', $itemsIDs);

        $query = "SELECT i.*, c.alias AS categoryalias
            FROM #__k2_items AS i
            LEFT JOIN #__k2_categories AS c ON c.id = i.catid
            WHERE i.published = 1
                AND i.trash = 0
                AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
                AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")";

        if (K2_JVERSION != '15') {
            $query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($app->getLanguageFilter()) {
                $query .= " AND i.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND i.access <= {$aid}";
        }

        if (K2_JVERSION != '15') {
            $query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($app->getLanguageFilter()) {
                $query .= " AND c.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND c.access <= {$aid}";
        }

        $query .= " AND c.published = 1 AND c.trash = 0 AND i.id IN({$sql}) ORDER BY i.id DESC";

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();
        K2Model::addIncludePath(JPATH_COMPONENT.'/models');
        $model = K2Model::getInstance('Item', 'K2Model');
        for ($key = 0; $key < count($rows); $key++) {
            if (!$params->get('itemRelatedMedia')) {
                $rows[$key]->video = null;
            }
            if (!$params->get('itemRelatedImageGallery')) {
                $rows[$key]->gallery = null;
            }
            $rows[$key] = $model->prepareItem($rows[$key], 'relatedByTag', '');
            $rows[$key] = $model->execPlugins($rows[$key], 'relatedByTag', '');
            K2HelperUtilities::setDefaultImage($rows[$key], 'relatedByTag', $params);
        }
        return $rows;
    }

    public function prepareSearch($search)
    {
        jimport('joomla.filesystem.file');
        $db = JFactory::getDbo();
        $language = JFactory::getLanguage();
        $defaultLang = $language->getDefault();
        $currentLang = $language->getTag();

        $search = trim($search);
        $length = JString::strlen($search);

        $sql = '';

        if (JRequest::getVar('categories')) {
            $categories = @explode(',', JRequest::getVar('categories'));
            JArrayHelper::toInteger($categories);
            sort($categories);
            $sql .= " AND c.id IN(".@implode(',', $categories).")";
        }

        if (empty($search)) {
            return $sql;
        }

        if (JString::substr($search, 0, 1) == '"' && JString::substr($search, $length - 1, 1) == '"') {
            $type = 'exact';
        } else {
            $type = 'any';
        }

        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php') && $currentLang != $defaultLang) {
            $conditions = array();
            $search_ignore = array();

            $ignoreFile = $language->getLanguagePath().'/'.$currentLang.'/'.$currentLang.'.ignore.php';

            if (JFile::exists($ignoreFile)) {
                include $ignoreFile;
            }

            if ($type == 'exact') {
                $word = JString::substr($search, 1, $length - 2);

                if (JString::strlen($word) > 3 && !in_array($word, $search_ignore)) {
                    $escaped = (K2_JVERSION == '15') ? $db->getEscaped($word, true) : $db->escape($word, true);
                    $langField = (K2_JVERSION == '15') ? 'code' : 'lang_code';
                    $word = $db->Quote('%'.$escaped.'%', false);

                    $jfQuery = "SELECT reference_id
                        FROM #__jf_content AS jfc
                        LEFT JOIN #__languages AS jfl ON jfc.language_id = jfl.".K2_JF_ID."
                        WHERE jfc.reference_table = 'k2_items'
                            AND jfl.".$langField." = ".$db->Quote($currentLang)."
                            AND jfc.published = 1
                            AND jfc.value LIKE ".$word."
                            AND (
                                jfc.reference_field = 'title'
                                OR jfc.reference_field = 'introtext'
                                OR jfc.reference_field = 'fulltext'
                                OR jfc.reference_field = 'image_caption'
                                OR jfc.reference_field = 'image_credits'
                                OR jfc.reference_field = 'video_caption'
                                OR jfc.reference_field = 'video_credits'
                                OR jfc.reference_field = 'extra_fields_search'
                                OR jfc.reference_field = 'metadesc'
                                OR jfc.reference_field = 'metakey'
                            )";
                    $db->setQuery($jfQuery);
                    $result = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();
                    $result = @array_unique($result);
                    JArrayHelper::toInteger($result);
                    if (count($result)) {
                        $conditions[] = "i.id IN(".implode(',', $result).")";
                    }
                }
            } else {
                $search = explode(' ', JString::strtolower($search));
                foreach ($search as $searchword) {
                    if (JString::strlen($searchword) > 3 && !in_array($searchword, $search_ignore)) {
                        $escaped = (K2_JVERSION == '15') ? $db->getEscaped($searchword, true) : $db->escape($searchword, true);
                        $word = $db->Quote('%'.$escaped.'%', false);
                        $langField = (K2_JVERSION == '15') ? 'code' : 'lang_code';

                        $jfQuery = "SELECT reference_id
                            FROM #__jf_content AS jfc
                            LEFT JOIN #__languages AS jfl ON jfc.language_id = jfl.".K2_JF_ID."
                            WHERE jfc.reference_table = 'k2_items'
                                AND jfl.".$langField." = ".$db->Quote($currentLang)."
                                AND jfc.published = 1
                                AND jfc.value LIKE ".$word."
                                AND (
                                    jfc.reference_field = 'title'
                                    OR jfc.reference_field = 'introtext'
                                    OR jfc.reference_field = 'fulltext'
                                    OR jfc.reference_field = 'image_caption'
                                    OR jfc.reference_field = 'image_credits'
                                    OR jfc.reference_field = 'video_caption'
                                    OR jfc.reference_field = 'video_credits'
                                    OR jfc.reference_field = 'extra_fields_search'
                                    OR jfc.reference_field = 'metadesc'
                                    OR jfc.reference_field = 'metakey'
                                )";
                        $db->setQuery($jfQuery);
                        $result = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();
                        $result = @array_unique($result);
                        foreach ($result as $id) {
                            $allIDs[] = $id;
                        }

                        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php') && $currentLang != $defaultLang) {
                            if (isset($allIDs) && count($allIDs)) {
                                JArrayHelper::toInteger($allIDs);
                                $conditions[] = "i.id IN(".implode(',', $allIDs).")";
                            }
                        }
                    }
                }
            }

            if (count($conditions)) {
                $sql .= " AND (".implode(" OR ", $conditions).")";
            }
        } else {
            if ($type == 'exact') {
                $search = JString::trim($search, '"');

                $escaped = (K2_JVERSION == '15') ? $db->getEscaped($search, true) : $db->escape($search, true);
                $quoted = $db->Quote('%'.$escaped.'%', false);

                $sql .= " AND (
                    LOWER(i.title) LIKE ".$quoted." OR
                    LOWER(i.introtext) LIKE ".$quoted." OR
                    LOWER(i.`fulltext`) LIKE ".$quoted." OR
                    LOWER(i.extra_fields_search) LIKE ".$quoted." OR
                    LOWER(i.image_caption) LIKE ".$quoted." OR
                    LOWER(i.image_credits) LIKE ".$quoted." OR
                    LOWER(i.video_caption) LIKE ".$quoted." OR
                    LOWER(i.video_credits) LIKE ".$quoted." OR
                    LOWER(i.metadesc) LIKE ".$quoted." OR
                    LOWER(i.metakey) LIKE ".$quoted."
                )";
            } else {
                $search = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search)));

                $searchwords = explode(' ', $search);
                if (count($searchwords)) {
                } else {
                    $searchwords = [$search];
                }

                foreach ($searchwords as $searchword) {
                    if (strlen($searchword) > 2) {
                        $escaped = (K2_JVERSION == '15') ? $db->getEscaped($searchword, true) : $db->escape($searchword, true);
                        $quoted = $db->Quote('%'.$escaped.'%', false);

                        $sql .= " AND (
                            LOWER(i.title) LIKE ".$quoted." OR
                            LOWER(i.introtext) LIKE ".$quoted." OR
                            LOWER(i.`fulltext`) LIKE ".$quoted." OR
                            LOWER(i.extra_fields_search) LIKE ".$quoted." OR
                            LOWER(i.image_caption) LIKE ".$quoted." OR
                            LOWER(i.image_credits) LIKE ".$quoted." OR
                            LOWER(i.video_caption) LIKE ".$quoted." OR
                            LOWER(i.video_credits) LIKE ".$quoted." OR
                            LOWER(i.metadesc) LIKE ".$quoted." OR
                            LOWER(i.metakey) LIKE ".$quoted."
                        )";
                    }
                }
            }
        }

        return $sql;
    }

    public function getModuleItems($moduleID)
    {
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__modules WHERE id={$moduleID} AND published=1 AND client_id=0";
        $db->setQuery($query, 0, 1);
        $module = $db->loadObject();
        $format = JRequest::getWord('format');
        if (is_null($module)) {
            JError::raiseError(404, JText::_('K2_NOT_FOUND'));
        } else {
            $params = class_exists('JParameter') ? new JParameter($module->params) : new JRegistry($module->params);
            switch ($module->module) {

                case 'mod_k2_content':
                    require_once(JPATH_SITE.'/modules/mod_k2_content/helper.php');
                    $helper = new modK2ContentHelper;
                    $items = $helper->getItems($params, $format);
                    break;

                case 'mod_k2_comments':
                    if ($params->get('module_usage') == 1) {
                        JError::raiseError(404, JText::_('K2_NOT_FOUND'));
                    }

                    require_once(JPATH_SITE.'/modules/mod_k2_comments/helper.php');
                    $helper = new modK2CommentsHelper;
                    $items = $helper->getLatestComments($params);

                    foreach ($items as $item) {
                        $item->title = $item->userName.' '.JText::_('K2_COMMENTED_ON').' '.$item->title;
                        $item->introtext = $item->commentText;
                        $item->created = $item->commentDate;
                        $item->id = $item->itemID;
                    }
                    break;

                default:
                    JError::raiseError(404, JText::_('K2_NOT_FOUND'));
            }

            $result = new JObject;
            $result->items = $items;
            $result->title = $module->title;
            $result->module = $module->module;
            $result->params = $module->params;
            return $result;
        }
    }

    public function getCategoriesTree()
    {
        $app = JFactory::getApplication();
        $clientID = $app->getClientId();
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');

        $query = "SELECT id, name, parent FROM #__k2_categories";
        if ($app->isSite()) {
            $query .= " WHERE published=1 AND trash=0";
            if (K2_JVERSION != '15') {
                $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
                if ($app->getLanguageFilter()) {
                    $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
                }
            } else {
                $query .= " AND access<={$aid}";
            }
        }
        $query .= " ORDER BY parent";
        $db->setQuery($query);

        $categories = $db->loadObjectList();
        $tree = array();
        return $this->buildTree($categories);
    }

    public function buildTree(array &$categories, $parent = 0)
    {
        $branch = array();
        foreach ($categories as &$category) {
            if ($category->parent == $parent) {
                $children = $this->buildTree($categories, $category->id);
                if ($children) {
                    $category->children = $children;
                }
                $branch[$category->id] = $category;
            }
        }
        return $branch;
    }

    public function getTreePath($tree, $id)
    {
        if (array_key_exists($id, $tree)) {
            return array($id);
        } else {
            foreach ($tree as $key => $root) {
                if (isset($root->children) && is_array($root->children)) {
                    $retry = $this->getTreePath($root->children, $id);

                    if ($retry) {
                        $retry[] = $key;
                        return $retry;
                    }
                }
            }
        }
        return null;
    }

    // Deprecated function, left for compatibility reasons
    public function getCategoryChildren($catid, $clear = false)
    {
        static $array = array();
        if ($clear) {
            $array = array();
        }
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $catid = (int)$catid;
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row) {
            array_push($array, $row->id);
            if ($this->hasChildren($row->id)) {
                $this->getCategoryChildren($row->id);
            }
        }
        return $array;
    }

    // Deprecated function, left for compatibility reasons
    public function hasChildren($id)
    {
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $id = (int)$id;
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 AND access<={$aid} ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows)) {
            return true;
        } else {
            return false;
        }
    }
}
