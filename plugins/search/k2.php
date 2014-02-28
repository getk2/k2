<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class plgSearchK2 extends JPlugin
{

    function onContentSearchAreas()
    {
        return $this->onSearchAreas();
    }

    function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        return $this->onSearch($text, $phrase, $ordering, $areas);
    }

    function onSearchAreas()
    {
        JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
        static $areas = array('k2' => 'K2_ITEMS');
        return $areas;
    }

    function onSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
        jimport('joomla.html.parameter');
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

        $nullDate = $db->getNullDate();
        $user = JFactory::getUser();
        if (K2_JVERSION != '15')
        {
            $accessCheck = " IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
        }
        else
        {
            $aid = $user->get('aid');
            $accessCheck = " <= {$aid} ";
        }
        $tagIDs = array();
        $itemIDs = array();

        require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

        $searchText = $text;
        if (is_array($areas))
        {
            if (!array_intersect($areas, array_keys($this->onSearchAreas())))
            {
                return array();
            }
        }

        $plugin = JPluginHelper::getPlugin('search', 'k2');
        $pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);

        $limit = $pluginParams->def('search_limit', 50);

        $text = JString::trim($text);
        if ($text == '')
        {
            return array();
        }

        $rows = array();

        if ($limit > 0)
        {

            if ($pluginParams->get('search_tags'))
            {
                $tagQuery = JString::strtolower($text);
                $escaped = K2_JVERSION == '15' ? $db->getEscaped($tagQuery, true) : $db->escape($tagQuery, true);
                $tagQuery = $db->Quote('%'.$escaped.'%', false);
                $query = "SELECT id FROM #__k2_tags WHERE LOWER(name) LIKE ".$quoted." AND published=1";
                $db->setQuery($query);
                $tagIDs = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();

                if (count($tagIDs))
                {
                    JArrayHelper::toInteger($tagIDs);
                    $query = "SELECT itemID FROM #__k2_tags_xref WHERE tagID IN (".implode(',', $tagIDs).")";
                    $db->setQuery($query);
                    $itemIDs = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
                }
            }
            if ($phrase == 'exact')
            {
                $text = JString::trim($text, '"');
                $escaped = K2_JVERSION == '15' ? $db->getEscaped($text, true) : $db->escape($text, true);
				$quoted = $db->Quote($escaped);
				$where = " ( LOWER(i.title) = ".$quoted." OR LOWER(i.introtext) = ".$quoted." OR LOWER(i.`fulltext`) = ".$quoted." OR LOWER(i.extra_fields_search) = ".$quoted." OR LOWER(i.image_caption) = ".$quoted." OR LOWER(i.image_credits) = ".$quoted." OR LOWER(i.video_caption) = ".$quoted." OR LOWER(i.video_credits) = ".$quoted." OR LOWER(i.metadesc) = ".$quoted." OR LOWER(i.metakey) = ".$quoted.") ";
            }
            else
            {
                $escaped = K2_JVERSION == '15' ? $db->getEscaped($text, true) : $db->escape($text, true);
				$quoted = $db->Quote('%'.$escaped.'%', false);
				$where = " ( LOWER(i.title) LIKE ".$quoted." OR LOWER(i.introtext) LIKE ".$quoted." OR LOWER(i.`fulltext`) LIKE ".$quoted." OR LOWER(i.extra_fields_search) LIKE ".$quoted." OR LOWER(i.image_caption) LIKE ".$quoted." OR LOWER(i.image_credits) LIKE ".$quoted." OR LOWER(i.video_caption) LIKE ".$quoted." OR LOWER(i.video_credits) LIKE ".$quoted." OR LOWER(i.metadesc) LIKE ".$quoted." OR LOWER(i.metakey) LIKE ".$quoted.") ";
            }

            if ($pluginParams->get('search_tags') && count($itemIDs))
            {
                JArrayHelper::toInteger($itemIDs);
                $where .= " OR i.id IN (".implode(',', $itemIDs);
            }
            $query = "
		SELECT i.title AS title,
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
    	INNER JOIN #__k2_categories AS c ON c.id=i.catid AND c.access {$accessCheck}
		WHERE (".$where.")
		AND i.trash = 0
	    AND i.published = 1
	    AND i.access {$accessCheck}
	    AND c.published = 1
	    AND c.access {$accessCheck}
	    AND c.trash = 0
	    AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
        AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
            if (K2_JVERSION != '15' && $mainframe->isSite() && $mainframe->getLanguageFilter())
            {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
            }
            $query .= " GROUP BY i.id ";

            switch ($ordering)
            {
                case 'oldest' :
                    $query .= 'ORDER BY i.created ASC';
                    break;

                case 'popular' :
                    $query .= 'ORDER BY i.hits DESC';
                    break;

                case 'alpha' :
                    $query .= 'ORDER BY i.title ASC';
                    break;

                case 'category' :
                    $query .= 'ORDER BY c.name ASC, i.title ASC';
                    break;

                case 'newest' :
                default :
                    $query .= 'ORDER BY i.created DESC';
                    break;
            }

            $db->setQuery($query, 0, $limit);
            $list = $db->loadObjectList();
            $limit -= count($list);
            if (isset($list))
            {
                foreach ($list as $key => $item)
                {
                    $list[$key]->href = JRoute::_(K2HelperRoute::getItemRoute($item->slug, $item->catslug));

                }
            }
            $rows[] = $list;
        }

        $results = array();
        if (count($rows))
        {
            foreach ($rows as $row)
            {
                $new_row = array();
                foreach ($row as $key => $item)
                {
                    $item->browsernav = '';
                    $item->tag = $searchText;
                    if (searchHelper::checkNoHTML($item, $searchText, array('text', 'title', 'metakey', 'metadesc', 'section', 'image_caption', 'image_credits', 'video_caption', 'video_credits', 'extra_fields_search', 'tag')))
                    {
                        $new_row[] = $item;
                    }
                }
                $results = array_merge($results, (array)$new_row);
            }
        }

        return $results;
    }

}
