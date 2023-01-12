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

jimport('joomla.application.component.helper');

class K2HelperRoute
{
    private static $cache = array(
        'item' => array(),
        'category' => array(),
        'date' => array(),
        'tag' => array(),
        'user' => array(),
        'menu_items' => null,
        'fallback_menu_items' => array(),
        'multicat_menu_items' => array(),
        'category_tree' => null,
        'itemlist_model' => null
    );

    public static function getItemRoute($id, $catid = 0)
    {
        $key = (int) $id;
        if (isset(self::$cache['item'][$key])) {
            return self::$cache['item'][$key];
        }
        $needles = array(
            'item' => (int) $id,
            'category' => (int) $catid,
        );
        $link = 'index.php?option=com_k2&view=item&id='.$id;
        if ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        self::$cache['item'][$key] = $link;
        return $link;
    }

    public static function getCategoryRoute($catid)
    {
        $key = (int) $catid;
        if (isset(self::$cache['category'][$key])) {
            return self::$cache['category'][$key];
        }
        $needles = array('category' => (int) $catid);
        $link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$catid;
        if ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        self::$cache['category'][$key] = $link;
        return $link;
    }

    public static function getTagRoute($tag)
    {
        $key = hash('md5', $tag);
        if (isset(self::$cache['tag'][$key])) {
            return self::$cache['tag'][$key];
        }
        $needles = array('tag' => $tag);
        $link = 'index.php?option=com_k2&view=itemlist&task=tag&tag='.urlencode($tag);
        if ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        self::$cache['tag'][$key] = $link;
        return $link;
    }

    public static function getUserRoute($userID)
    {
        $key = (int) $userID;
        if (isset(self::$cache['user'][$key])) {
            return self::$cache['user'][$key];
        }
        $needles = array('user' => (int) $userID);
        $user = JFactory::getUser($userID);
        if (K2_JVERSION != '15' && JFactory::getConfig()->get('unicodeslugs') == 1) {
            $alias = JApplication::stringURLSafe($user->name);
        } elseif (JPluginHelper::isEnabled('system', 'unicodeslug') || JPluginHelper::isEnabled('system', 'jw_unicodeSlugsExtended')) {
            $alias = JFilterOutput::stringURLSafe($user->name);
        } else {
            $alias = preg_replace('/[^\p{L}\p{N}]/u', '', trim($user->name));
            $alias = mb_strtolower($alias, 'UTF-8');
            $params = K2HelperUtilities::getParams('com_k2');
            $processedSEFReplacements = array();
            $SEFReplacements = explode(',', $params->get('SEFReplacements', null));
            foreach ($SEFReplacements as $pair) {
                if (!empty($pair)) {
                    @list($src, $dst) = explode('|', trim($pair));
                    $processedSEFReplacements[trim($src)] = trim($dst);
                }
            }
            foreach ($processedSEFReplacements as $key => $value) {
                $alias = str_replace($key, $value, $alias);
            }
            $alias = preg_replace('/[^\p{L}\p{N}]/u', '', $alias);
            if (trim($alias) == '') {
                // I mean, what are the freaking odds, right?
                $alias = hash('md5', $user->name);
            }
        }
        $link = 'index.php?option=com_k2&view=itemlist&task=user&id='.$userID.':'.$alias;
        if ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        self::$cache['user'][$key] = $link;
        return $link;
    }

    public static function getDateRoute($year, $month, $day = 0, $catid = 0)
    {
        $key = (int) $year.$month.$day.$catid;
        if (isset(self::$cache['date'][$key])) {
            return self::$cache['date'][$key];
        }
        $needles = array('date' => (int) $year.$month.$day);
        $link = 'index.php?option=com_k2&view=itemlist&task=date&year='.$year.'&month='.$month;
        if ($day) {
            $link .= '&day='.$day;
        }
        if ($catid) {
            $link .= '&catid='.$catid;
        }
        if ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        self::$cache['date'][$key] = $link;
        return $link;
    }

    public static function getSearchRoute($Itemid = '')
    {
        $needles = array('search' => 'search');
        $link = 'index.php?option=com_k2&view=itemlist&task=search';
        if ($Itemid) {
            $link .= '&Itemid='.$Itemid;
        } elseif ($item = self::findMenuItem($needles)) {
            $link .= '&Itemid='.$item->id;
        }
        return $link;
    }

    private static function findMenuItem($needles)
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu('site', array());
        $component = JComponentHelper::getComponent('com_k2');

        if (!is_null(self::$cache['menu_items'])) {
            $items = self::$cache['menu_items'];
        } else {
            if (K2_JVERSION == '15') {
                $items = $menu->getItems('componentid', $component->id);
            } else {
                $items = $menu->getItems('component_id', $component->id);
            }
            self::$cache['menu_items'] = $items;
        }

        $parsedItems = array();

        if (count($items)) {
            foreach ($items as $item) {
                // Find K2 menu items pointing to multiple K2 categories
                if (@$item->query['view'] == 'itemlist' && @$item->query['task'] == '') {
                    if (!isset(self::$cache['multicat_menu_items'][$item->id])) {
                        if (K2_JVERSION == '15') {
                            $menuparams = explode("\n", $item->params);
                            foreach ($menuparams as $param) {
                                if (strpos($param, 'categories=') === 0) {
                                    $array = explode('categories=', $param);
                                    $item->K2Categories = explode('|', $array[1]);
                                }
                            }
                            if (!isset($item->K2Categories)) {
                                $item->K2Categories = array();
                            }
                        } else {
                            $menuparams = json_decode($item->params);
                            $item->K2Categories = isset($menuparams->categories) ? $menuparams->categories : array();
                        }

                        self::$cache['multicat_menu_items'][$item->id] = $item->K2Categories;

                        if (count($item->K2Categories) === 0) {
                            // Push all K2 itemlist menu items without specific categories assigned into an array
                            // Later we pick the one with the highest menu item ID [TBC with static selection under SEO settings]
                            self::$cache['fallback_menu_items'][$item->id] = $item;
                        }
                    }
                }
                $parsedItems[] = $item;
            }
        }

        $match = null;

        foreach ($needles as $needle => $id) {
            if (count($parsedItems)) {
                foreach ($parsedItems as $item) {
                    if ($needle == 'category' || $needle == 'user') {
                        if ((@$item->query['task'] == $needle) && (@$item->query['id'] == $id)) {
                            $match = $item;
                            break;
                        }
                    } elseif ($needle == 'tag') {
                        if ((@$item->query['task'] == $needle) && (@$item->query['tag'] == $id)) {
                            $match = $item;
                            break;
                        }
                    } else {
                        if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
                            $match = $item;
                            break;
                        }
                    }
                    if (!is_null($match)) {
                        break;
                    }
                }

                // Second pass for K2 menu items pointing to multiple K2 categories - attempt to find menu item that includes a given category's ID
                if (is_null($match) && $needle == 'category') {
                    foreach ($parsedItems as $item) {
                        if (@$item->query['view'] == 'itemlist' && @$item->query['task'] == '') {
                            if (isset(self::$cache['multicat_menu_items'][$item->id]) && count(self::$cache['multicat_menu_items'][$item->id])) {
                                foreach (self::$cache['multicat_menu_items'][$item->id] as $catid) {
                                    if ($id == (int) $catid) {
                                        $match = $item;
                                        break;
                                    }
                                }
                            }
                            if (!is_null($match)) {
                                break;
                            }
                        }
                    }
                }
            }
            if (!is_null($match)) {
                break;
            }
        }

        if (is_null($match)) {
            // Try to detect any parent category menu item
            if ($needle == 'category') {
                if (is_null(self::$cache['category_tree'])) {
                    K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
                    $model = K2Model::getInstance('Itemlist', 'K2Model');
                    self::$cache['category_tree'] = $model->getCategoriesTree();
                    self::$cache['itemlist_model'] = $model;
                }
                $parents = self::$cache['itemlist_model']->getTreePath(self::$cache['category_tree'], $id);
                if (is_array($parents) && count($parents)) {
                    foreach ($parents as $categoryID) {
                        if ($categoryID != $id) {
                            // Recursively check if a menu item exists with the parent category ID
                            $match = self::findMenuItem(array('category' => $categoryID));
                            if (!is_null($match)) {
                                break;
                            }
                        }
                    }
                }
            }
            if (is_null($match) && count(self::$cache['fallback_menu_items'])) {
                // We can't find any match so we pick the K2 itemlist menu item with the highest ID that points to no specific categories
                rsort(self::$cache['fallback_menu_items']);
                $match = self::$cache['fallback_menu_items'][0];
            }
        }

        return $match;
    }
}
