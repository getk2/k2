<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2024 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_k2');

if ($params->get('k2Sef')) {
    function k2BuildRoute(&$query)
    {
        // Initialize
        $segments = array();

        // Get params
        $params = JComponentHelper::getParams('com_k2');

        // Get the menu
        $menu = JFactory::getApplication()->getMenu();

        // Detect the active menu item
        if (empty($query['Itemid'])) {
            $menuItem = $menu->getActive();
        } else {
            $menuItem = $menu->getItem($query['Itemid']);
        }

        // Load data from the current menu item
        $mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
        $mTask = (empty($menuItem->query['task'])) ? null : $menuItem->query['task'];
        $mId = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
        $mTag = (empty($menuItem->query['tag'])) ? null : $menuItem->query['tag'];

        if (isset($query['layout'])) {
            unset($query['layout']);
        }

        if ($mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) && @intval($query['id']) > 0) {
            unset($query['view']);
            unset($query['task']);
            unset($query['id']);
        }

        if ($mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag'])) {
            unset($query['view']);
            unset($query['task']);
            unset($query['tag']);
        }

        if (isset($query['view'])) {
            $segments[] = $query['view'];
            unset($query['view']);
        }

        if (isset($query['task'])) {
            $segments[] = $query['task'];
            unset($query['task']);
        }

        if (isset($query['id'])) {
            $segments[] = $query['id'];
            unset($query['id']);
        }

        if (isset($query['cid'])) {
            $segments[] = $query['cid'];
            unset($query['cid']);
        }

        if (isset($query['tag'])) {
            $segments[] = $query['tag'];
            unset($query['tag']);
        }

        if (isset($query['year'])) {
            $segments[] = $query['year'];
            unset($query['year']);
        }

        if (isset($query['month'])) {
            $segments[] = $query['month'];
            unset($query['month']);
        }

        if (isset($query['day'])) {
            $segments[] = $query['day'];
            unset($query['day']);
        }

        // Item view
        if (isset($segments[0]) && $segments[0] == 'item' && @$segments[1] != 'add') {
            // Enabled category prefix for items
            if ($params->get('k2SefLabelItem')) {
                // Tasks available for an item
                $itemTasks = array('edit', 'download');

                // If it's a task pick the next key
                if (in_array($segments[1], $itemTasks)) {
                    $itemID = $segments[2];
                } else {
                    $itemID = $segments[1];
                }

                // Get the item ID
                $parts = explode(':', $itemID);
                $id = (int) $parts[0];

                // Replace "item" with the category slug
                if ($params->get('k2SefLabelItem') == '1') {
                    if ($params->get('k2SefInsertCatId') == '0') {
                        // Single category path
                        $segments[0] = getItemProps($id, true)->slug;

                        /*
                        // Full category path
                        $slugs = array();
                        $categories = getCategoryPath(getCategoryProps($slug)->id);
                        if (count($categories)) {
                            $slugs[] = $slug;
                            foreach ($categories as $category) {
                                $slugs[] = $category['alias'];
                            }
                            $segments[0] = implode('/', $slugs);
                        }
                        */
                    } else {
                        // Single category path
                        if ($params->get('k2SefUseCatTitleAlias')) {
                            if ($params->get('k2SefCatIdTitleAliasSep') == 'slash') {
                                $segments[0] = getItemProps($id, true)->catid.'/'.getItemProps($id, true)->slug;
                            } else {
                                $segments[0] = getItemProps($id, true)->catid.'-'.getItemProps($id, true)->slug;
                            }
                        } else {
                            $segments[0] = getItemProps($id, true)->catid;
                        }

                        /*
                        // Full category path
                        $slugs = array();
                        $categories = getCategoryPath(getCategoryProps($slug)->id);
                        if (count($categories)) {
                            $slugs[] = $slug;
                            foreach ($categories as $category) {
                                $slugs[] = $category['id'].'-'.$category['alias'];
                            }
                            $segments[0] = implode('/', $slugs);
                        }
                        */
                    }
                } else {
                    $segments[0] = $params->get('k2SefLabelItemCustomPrefix');
                }
            }
            // Remove "item" from the URL
            else {
                unset($segments[0]);
            }

            // Handle item id and alias
            if ($params->get('k2SefInsertItemId')) {
                if ($params->get('k2SefUseItemTitleAlias')) {
                    if ($params->get('k2SefItemIdTitleAliasSep') == 'slash') {
                        $segments[1] = str_replace(':', '/', $segments[1]);
                    }
                } else {
                    $temp = @explode(':', $segments[1]);
                    $segments[1] = $temp[0];
                }
            } else {
                if (isset($segments[1]) && $segments[1] != 'download') {
                    // Try to split the slug
                    $temp = @explode(':', $segments[1]);

                    // If the slug contained an item id do not use it
                    if (count($temp) > 1) {
                        $segments[1] = $temp[1];
                    }
                }
            }
        }
        // Itemlist view (check for prefix segments)
        elseif (isset($segments[0]) && $segments[0] == 'itemlist') {
            if (isset($segments[1])) {
                switch ($segments[1]) {
                    case 'category':
                        $k2SefLabelCat_fallback = 'category';
                        if ($params->get('k2SefUseCatTitleAlias')) {
                            $k2SefLabelCat_fallback = '';
                        }
                        $segments[0] = $params->get('k2SefLabelCat', $k2SefLabelCat_fallback);
                        unset($segments[1]);

                        $parts = @explode(':', $segments[2]);
                        $catid = (!empty($parts[0])) ? (int) $parts[0] : '';
                        $slug = (!empty($parts[1])) ? $parts[1] : '';

                        $slugs = array();
                        $categories = getCategoryPath($catid);
                        if (count($categories)) {
                            foreach ($categories as $category) {
                                $slugs[] = $category['alias'];
                            }
                            // Single category path
                            $slug = end($slugs);
                            // Full category path
                            //$slug = implode('/', $slugs);
                        }

                        // Handle category id and alias
                        if ($params->get('k2SefInsertCatId')) {
                            if ($params->get('k2SefUseCatTitleAlias')) {
                                if ($params->get('k2SefCatIdTitleAliasSep') == 'slash') {
                                    $segments[2] = str_replace(':', '/', $segments[2]);
                                }
                            } else {
                                $segments[2] = $catid;
                            }
                        } else {
                            unset($segments[2]);
                            if ($segments[0] == '') {
                                unset($segments[1]);
                                $segments[0] = $slug;
                            } else {
                                $segments[1] = $slug;
                            }
                        }

                        break;
                    case 'tag':
                        $segments[0] = $params->get('k2SefLabelTag', 'tag');
                        unset($segments[1]);
                        break;
                    case 'user':
                        $segments[0] = $params->get('k2SefLabelUser', 'author');
                        unset($segments[1]);
                        break;
                    case 'date':
                        $segments[0] = $params->get('k2SefLabelDate', 'date');
                        unset($segments[1]);
                        break;
                    case 'search':
                        $segments[0] = $params->get('k2SefLabelSearch', 'search');
                        unset($segments[1]);
                        break;
                    default:
                        $segments[0] = 'itemlist';
                        break;
                }
            }
        }

        // Return reordered segments array
        return array_values($segments);
    }

    function k2ParseRoute($segments)
    {
        // Initialize
        $vars = array();

        $params = JComponentHelper::getParams('com_k2');

        $request_url_parts = [];
        foreach ($segments as $segment) {
            $request_url_parts[] = str_replace(':', '-', $segment);
        }
        $lastSegment = end($request_url_parts);
        $lastSegmentParts = explode('-', $lastSegment);
        $request_url = implode('/', $request_url_parts);

        $reservedViews = array('item', 'itemlist', 'media', 'users', 'comments', 'latest');
        $categoryPath = '';
        if (!in_array($request_url_parts[0], $reservedViews)) {
            // Category view
            if ($request_url_parts[0] == $params->get('k2SefLabelCat')) {
                $request_url_parts[0] = 'itemlist';
                if (count($request_url_parts) > 1) {
                    $categoryPath = implode('/', $request_url_parts);
                } else {
                    $categoryPath = $request_url_parts[0];
                }
                array_splice($request_url_parts, 1, 0, 'category');
            }
            // Tag view
            elseif ($request_url_parts[0] == $params->get('k2SefLabelTag', 'tag')) {
                $request_url_parts[0] = 'itemlist';
                array_splice($request_url_parts, 1, 0, 'tag');
            }
            // User view
            elseif ($request_url_parts[0] == $params->get('k2SefLabelUser', 'author')) {
                $request_url_parts[0] = 'itemlist';
                array_splice($request_url_parts, 1, 0, 'user');
            }
            // Date view
            elseif ($request_url_parts[0] == $params->get('k2SefLabelDate', 'date')) {
                $request_url_parts[0] = 'itemlist';
                array_splice($request_url_parts, 1, 0, 'date');
            }
            // Search view
            elseif ($request_url_parts[0] == $params->get('k2SefLabelSearch', 'search')) {
                $request_url_parts[0] = 'itemlist';
                array_splice($request_url_parts, 1, 0, 'search');
            }
            // Category path, without a prefix
            elseif (
                isset(getCategoryProps($request_url_parts[0])->alias) &&
                $request_url_parts[0] == getCategoryProps($request_url_parts[0])->alias &&
                (
                    array_reverse($request_url_parts)[0] != @getItemProps(array_reverse($request_url_parts)[0])->alias &&
                    array_reverse($request_url_parts)[0] != @getItemProps((int) array_reverse($request_url_parts)[0])->id
                )
            ) {
                if (count($request_url_parts) > 1) {
                    $categoryPath = implode('/', $request_url_parts);
                } else {
                    $categoryPath = $request_url_parts[0];
                }
                $request_url_parts[0] = 'itemlist';
                array_splice($request_url_parts, 1, 0, 'category');
            }
            // Item view
            else {
                // Replace the category prefix with item
                if ($params->get('k2SefLabelItem')) {
                    $request_url_parts[0] = 'item';
                }
                // Reinsert the removed item segment
                else {
                    array_splice($request_url_parts, 0, 0, 'item');
                }
                // Reinsert item id to the item alias
                if (!$params->get('k2SefInsertItemId') && @$request_url_parts[1] != 'download' && @$request_url_parts[1] != 'edit') {
                    $alias = array_reverse($request_url_parts)[0];
                    $id = getItemProps($alias)->id;
                    $request_url_parts[1] = $id.':'.$alias;
                }
            }
        }

        $vars['view'] = $request_url_parts[0];

        if (!isset($request_url_parts[1])) {
            $request_url_parts[1] = '';
        }

        $vars['task'] = $request_url_parts[1];

        if ($request_url_parts[0] == 'itemlist') {
            switch ($request_url_parts[1]) {
                case 'category':
                    if (isset($request_url_parts[2]) && empty($request_url_parts[3])) {
                        // Re-insert category id to the category slug
                        if (!$params->get('k2SefInsertCatId')) {
                            $request_url_parts[2] = $request_url_parts[2];
                            $catId = getCategoryProps($request_url_parts[2])->id;
                            $request_url_parts[2] = $catId.':'.$request_url_parts[2];
                        }
                        $vars['id'] = $request_url_parts[2];
                    } else {
                        if (strpos($categoryPath, '/') !== false) {
                            // Nested category path
                            $categoryPath = str_replace('-', ':', $categoryPath);
                            $categories = explode('/', $categoryPath);
                            $last = array_reverse($categories)[0];
                            $last = str_replace(':', '-', $last);
                            $vars['id'] = getCategoryProps($last)->id.':'.$last;
                        } else {
                            // Single category path
                            $vars['id'] = ($categoryPath) ? getCategoryProps($categoryPath)->id.':'.$categoryPath : null;
                        }
                    }
                    break;

                case 'tag':
                    if (isset($request_url_parts[2])) {
                        $vars['tag'] = $request_url_parts[2];
                    }
                    break;

                case 'user':
                    if (isset($request_url_parts[2])) {
                        $vars['id'] = $request_url_parts[2];
                    }
                    break;

                case 'date':
                    if (isset($request_url_parts[2])) {
                        $vars['year'] = $request_url_parts[2];
                    }
                    if (isset($request_url_parts[3])) {
                        $vars['month'] = $request_url_parts[3];
                    }
                    if (isset($request_url_parts[4])) {
                        $vars['day'] = $request_url_parts[4];
                    }
                    break;
            }
        } elseif ($request_url_parts[0] == 'item') {
            switch ($request_url_parts[1]) {
                case 'add':
                case 'edit':
                    if (isset($request_url_parts[2])) {
                        $vars['cid'] = $request_url_parts[2];
                    }
                    break;

                case 'download':
                    if (isset($request_url_parts[2])) {
                        $vars['id'] = $request_url_parts[2];
                    }
                    break;

                default:
                    $vars['id'] = $request_url_parts[1];
                    if (isset($request_url_parts[2])) {
                        $vars['id'] .= ':'.$request_url_parts[2];
                    }
                    unset($vars['task']);
                    break;
            }
        }

        if ($request_url_parts[0] == 'comments' && isset($request_url_parts[1]) && $request_url_parts[1] == 'reportSpammer') {
            $vars['id'] = $request_url_parts[2];
        }

        return $vars;
    }

    /* --- Helpers --- */
    function getItemProps($id_or_slug = null, $getCategoryProps = false)
    {
        $db = JFactory::getDbo();

        $item = null;

        if ($getCategoryProps) {
            if (is_int($id_or_slug)) {
                $query = "SELECT i.id AS id, i.alias AS alias, c.id AS catid, c.alias AS slug
                    FROM #__k2_items AS i
                    INNER JOIN #__k2_categories AS c
                        ON i.catid = c.id
                    WHERE i.id = {$id_or_slug} AND i.published = 1";
            } else {
                $escaped = (K2_JVERSION == '15') ? $db->getEscaped($id_or_slug, true) : $db->escape($id_or_slug, true);
                $quoted = $db->Quote($escaped, false);
                $query = "SELECT i.id AS id, i.alias AS alias, c.id AS catid, c.alias AS slug
                    FROM #__k2_items AS i
                    INNER JOIN #__k2_categories AS c
                        ON i.catid = c.id
                    WHERE i.alias = {$quoted} AND i.published = 1";
            }
        } else {
            if (is_int($id_or_slug)) {
                $query = "SELECT id, alias FROM #__k2_items WHERE published = 1 AND id = {$id_or_slug}";
            } else {
                $escaped = (K2_JVERSION == '15') ? $db->getEscaped($id_or_slug, true) : $db->escape($id_or_slug, true);
                $quoted = $db->Quote($escaped, false);
                $query = "SELECT id, alias FROM #__k2_items WHERE published = 1 AND alias = {$quoted}";
            }
        }
        $db->setQuery($query);
        if ($result = $db->loadObject()) {
            $item = $result;
        }

        return $item;
    }

    function getCategoryProps($id_or_slug = null)
    {
        $db = JFactory::getDbo();

        $category = null;

        if (is_numeric($id_or_slug)) {
            $query = "SELECT id, alias, parent FROM #__k2_categories WHERE published = 1 AND id = {$id_or_slug}";
        } else {
            $escaped = (K2_JVERSION == '15') ? $db->getEscaped($id_or_slug, true) : $db->escape($id_or_slug, true);
            $quoted = $db->Quote($escaped, false);
            $query = "SELECT id, alias, parent FROM #__k2_categories WHERE published = 1 AND alias = {$quoted}";
        }

        $db->setQuery($query);

        if ($result = $db->loadObject()) {
            $category = $result;
        }
        return $category;
    }

    function getCategoryPath($id, $path = array())
    {
        $category = getCategoryProps($id);
        if ($category->parent) {
            $path[] = [
                'id' => $id,
                'alias' => $category->alias
            ];
            return getCategoryPath($category->parent, $path);
        } else {
            $path[] = [
                'id' => $id,
                'alias' => $category->alias
            ];
        }
        return array_reverse($path);
    }
} else {
    function K2BuildRoute(&$query)
    {
        $segments = array();
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        if (empty($query['Itemid'])) {
            $menuItem = $menu->getActive();
        } else {
            $menuItem = $menu->getItem($query['Itemid']);
        }
        $mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
        $mTask = (empty($menuItem->query['task'])) ? null : $menuItem->query['task'];
        $mId = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
        $mTag = (empty($menuItem->query['tag'])) ? null : $menuItem->query['tag'];

        if (isset($query['layout'])) {
            unset($query['layout']);
        }

        if ($mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) && @intval($query['id']) > 0) {
            unset($query['view']);
            unset($query['task']);
            unset($query['id']);
        }

        if ($mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag'])) {
            unset($query['view']);
            unset($query['task']);
            unset($query['tag']);
        }

        if (isset($query['view'])) {
            $view = $query['view'];
            $segments[] = $view;
            unset($query['view']);
        }

        if (@ isset($query['task'])) {
            $task = $query['task'];
            $segments[] = $task;
            unset($query['task']);
        }

        if (isset($query['id'])) {
            $id = $query['id'];
            $segments[] = $id;
            unset($query['id']);
        }

        if (isset($query['cid'])) {
            $cid = $query['cid'];
            $segments[] = $cid;
            unset($query['cid']);
        }

        if (isset($query['tag'])) {
            $tag = $query['tag'];
            $segments[] = $tag;
            unset($query['tag']);
        }

        if (isset($query['year'])) {
            $year = $query['year'];
            $segments[] = $year;
            unset($query['year']);
        }

        if (isset($query['month'])) {
            $month = $query['month'];
            $segments[] = $month;
            unset($query['month']);
        }

        if (isset($query['day'])) {
            $day = $query['day'];
            $segments[] = $day;
            unset($query['day']);
        }

        if (isset($query['task'])) {
            $task = $query['task'];
            $segments[] = $task;
            unset($query['task']);
        }

        return $segments;
    }

    function K2ParseRoute($segments)
    {
        $vars = array();
        $vars['view'] = $segments[0];
        if (!isset($segments[1])) {
            $segments[1] = '';
        }
        $vars['task'] = $segments[1];

        if ($segments[0] == 'itemlist') {
            switch ($segments[1]) {
                case 'category':
                    if (isset($segments[2])) {
                        $vars['id'] = $segments[2];
                    }
                    break;

                case 'tag':
                    if (isset($segments[2])) {
                        $vars['tag'] = $segments[2];
                    }
                    break;

                case 'user':
                    if (isset($segments[2])) {
                        $vars['id'] = $segments[2];
                    }
                    break;

                case 'date':
                    if (isset($segments[2])) {
                        $vars['year'] = $segments[2];
                    }
                    if (isset($segments[3])) {
                        $vars['month'] = $segments[3];
                    }
                    if (isset($segments[4])) {
                        $vars['day'] = $segments[4];
                    }
                    break;
            }
        } elseif ($segments[0] == 'item') {
            switch ($segments[1]) {
                case 'add':
                case 'edit':
                    if (isset($segments[2])) {
                        $vars['cid'] = $segments[2];
                    }
                    break;

                case 'download':
                    if (isset($segments[2])) {
                        $vars['id'] = $segments[2];
                    }
                    break;

                default:
                    $vars['id'] = $segments[1];
                    unset($vars['task']);
                    break;
            }
        }

        if ($segments[0] == 'comments' && isset($segments[1]) && $segments[1] == 'reportSpammer') {
            $vars['id'] = $segments[2];
        }

        return $vars;
    }
}
