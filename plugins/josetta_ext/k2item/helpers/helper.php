<?php
/**
 * @version     2.6.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

abstract class JosettaK2ItemHelper
{

    protected static $_categoriesOptionsPerLanguage = array();
    protected static $_categoriesDataPerLanguage = array();

    public static function getCategoryOptionsPerLanguage($config = array('filter.published' => array(0, 1), 'filter.languages' => array()))
    {

        $hash = md5(serialize($config));

        if (!isset(self::$_categoriesOptionsPerLanguage[$hash]))
        {

            $config = (array)$config;

            // read categories from db
            $items = self::getCategoriesPerLanguage($config);

            // B/C compat.
            foreach ($items as &$item)
            {
                $item->title = $item->name;
                $item->parent_id = $item->parent;
            }

            // indent cat list, for easier reading
            $items = self::indentCategories($items);

            self::$_categoriesOptionsPerLanguage[$hash] = array();
            foreach ($items as &$item)
            {
                self::$_categoriesOptionsPerLanguage[$hash][] = JHtml::_('select.option', $item->id, JString::str_ireplace('<sup>|_</sup>', '', $item->treename));
            }

        }

        return self::$_categoriesOptionsPerLanguage[$hash];
    }

    public static function getCategoriesPerLanguage($config = array('filter.published' => array(0, 1), 'filter.languages' => array()), $index = null)
    {

        $hash = md5(serialize($config));

        if (!isset(self::$_categoriesDataPerLanguage[$hash]))
        {
            $config = (array)$config;
            $db = JFactory::getDbo();

            $query = "SELECT c.*, g.title AS groupname, exfg.name as extra_fields_group FROM #__k2_categories as c LEFT JOIN #__viewlevels AS g ON g.id = c.access"." LEFT JOIN #__k2_extra_fields_groups AS exfg ON exfg.id = c.extraFieldsGroup WHERE c.id>0";

            if (!empty($config['filter.published']))
            {
                $query .= ' and c.published in ('.ShlDbHelper::arrayToIntvalList($config['filter.published']).')';
            }
            if (!empty($config['filter.languages']))
            {
                $query .= ' and c.language in ('.ShlDbHelper::arrayToQuotedList($config['filter.languages']).')';
            }

            $db->setQuery($query);
            $items = $db->loadObjectList($index);

            foreach ($items as &$item)
            {
                $item->title = $item->name;
                $item->parent_id = $item->parent;
            }

            self::$_categoriesDataPerLanguage[$hash] = $items;
        }

        return self::$_categoriesDataPerLanguage[$hash];
    }

    public static function indentCategories(&$rows, $root = 0)
    {

        $children = array();
        if (count($rows))
        {
            foreach ($rows as $v)
            {
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        $categories = JHTML::_('menu.treerecurse', $root, '', array(), $children);

        return $categories;
    }

}
