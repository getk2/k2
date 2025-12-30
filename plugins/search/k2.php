<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class plgSearchK2 extends JPlugin
{
    public function onContentSearchAreas()
    {
        return $this->onSearchAreas();
    }

    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        return $this->onSearch($text, $phrase, $ordering, $areas);
    }

    public function onSearchAreas()
    {
        JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
        static $areas = ['k2' => 'K2_ITEMS'];
        return $areas;
    }

    public function onSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
        jimport('joomla.html.parameter');
        $app  = JFactory::getApplication();
        $db   = JFactory::getDbo();
        $jnow = JFactory::getDate();
        $now  = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

        $nullDate = $db->getNullDate();
        $user     = JFactory::getUser();
        if (K2_JVERSION != '15') {
            $accessCheck = " IN(" . implode(',', $user->getAuthorisedViewLevels()) . ") ";
        } else {
            $aid         = $user->get('aid');
            $accessCheck = " <= {$aid} ";
        }
        $tagIDs  = [];
        $itemIDs = [];

        require_once JPATH_SITE . '/administrator/components/com_search/helpers/search.php';
        require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
        require_once JPATH_SITE . '/media/k2/assets/helpers/global.php';

        $searchText = $text;
        $where      = '';
        $rows       = [];
        $results    = [];

        if (is_array($areas)) {
            if (! array_intersect($areas, array_keys($this->onSearchAreas()))) {
                return [];
            }
        }

        $plugin       = JPluginHelper::getPlugin('search', 'k2');
        $pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);

        $limit = $pluginParams->def('search_limit', 50);
        if ($limit > 0) {
            $text = JString::trim($text);
            if ($text == '') {
                return [];
            }

            $where .= K2GlobalHelper::search($search, [
                'i.title',
                'i.alias',
                'i.introtext',
                'i.`fulltext`',
                'i.extra_fields_search',
                'i.image_caption',
                'i.image_credits',
                'i.video_caption',
                'i.video_credits',
                'i.metadesc',
                'i.metakey',
            ]);

            if ($pluginParams->get('search_tags')) {
                $tagQuery = JString::strtolower($text);
                $escaped  = (K2_JVERSION == '15') ? $db->getEscaped($tagQuery, true) : $db->escape($tagQuery, true);
                $quoted   = $db->Quote('%' . $escaped . '%', false);
                $query    = "SELECT id FROM #__k2_tags WHERE published = 1 AND LOWER(name) LIKE " . $quoted;
                $db->setQuery($query);
                $tagIDs = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();
                if (is_array($tagIDs) && count($tagIDs)) {
                    sort($tagIDs);
                    $query = "SELECT itemID FROM #__k2_tags_xref WHERE tagID IN (" . implode(',', $tagIDs) . ")";
                    $db->setQuery($query);
                    $itemIDs = (K2_JVERSION == '30') ? $db->loadColumn() : $db->loadResultArray();
                    $itemIDs = array_unique($itemIDs);
                    if (is_array($itemIDs) && count($itemIDs)) {
                        sort($itemIDs);
                        // Trim the last closing parenthesis and add OR condition for items with matching tags to close the AND (...) clause
                        $where = substr($where, 0, strlen($where) - 1) . " OR i.id IN (" . implode(',', $itemIDs) . "))";
                    }
                }
            }

            $query = "SELECT i.title AS title,
                    i.alias,
                    i.metadesc,
                    i.metakey,
                    c.name as section,
                    i.image_caption,
                    i.image_credits,
                    i.video_caption,
                    i.video_credits,
                    i.extra_fields_search,
                    i.created,
                    CONCAT(i.introtext, i.fulltext) AS text,
                    CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(':', i.id, i.alias) ELSE i.id END as slug,
                    CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(':', c.id, c.alias) ELSE c.id END as catslug
                FROM #__k2_items AS i
                INNER JOIN #__k2_categories AS c ON c.id = i.catid
                WHERE i.trash = 0
                    AND i.published = 1
                    AND i.access {$accessCheck}
                    AND c.published = 1
                    AND c.access {$accessCheck}
                    AND c.trash = 0
                    AND (i.publish_up = " . $db->Quote($nullDate) . " OR i.publish_up <= " . $db->Quote($now) . ")
                    AND (i.publish_down = " . $db->Quote($nullDate) . " OR i.publish_down >= " . $db->Quote($now) . ")
                    {$where}
            ";

            if (K2_JVERSION != '15' && $app->isSite() && $app->getLanguageFilter()) {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (" . $db->Quote($languageTag) . ", " . $db->Quote('*') . ") AND i.language IN (" . $db->Quote($languageTag) . ", " . $db->Quote('*') . ")";
            }

            switch ($ordering) {
                case 'oldest':
                    $query .= ' ORDER BY i.created ASC';
                    break;

                case 'popular':
                    $query .= ' ORDER BY i.hits DESC';
                    break;

                case 'alpha':
                    $query .= ' ORDER BY i.title ASC';
                    break;

                case 'category':
                    $query .= ' ORDER BY c.name ASC, i.title ASC';
                    break;

                case 'newest':
                default:
                    $query .= ' ORDER BY i.created DESC';
                    break;
            }

            $db->setQuery($query, 0, $limit);
            $list = $db->loadObjectList();

            if (is_array($list)) {
                $limit -= count($list);
                foreach ($list as $key => $item) {
                    $list[$key]->href = JRoute::_(K2HelperRoute::getItemRoute($item->slug, $item->catslug));
                }
                $rows[] = $list;
            }
        }

        $results = [];

        if (is_array($rows) && count($rows)) {
            foreach ($rows as $row) {
                $new_row = [];
                foreach ($row as $key => $item) {
                    $item->browsernav = '';
                    $item->tag        = $searchText;
                    if (searchHelper::checkNoHTML($item, $searchText, ['text', 'title', 'metakey', 'metadesc', 'section', 'image_caption', 'image_credits', 'video_caption', 'video_credits', 'extra_fields_search', 'tag'])) {
                        $new_row[] = $item;
                    }
                }
                $results = array_merge($results, (array) $new_row);
            }
        }

        return $results;
    }
}
