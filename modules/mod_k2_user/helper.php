<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die ;

JLoader::register('K2HelperRoute', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
JLoader::register('K2HelperUtilities', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

class modK2UserHelper
{

    public static function getReturnURL($params, $type)
    {

        if ($itemid = $params->get($type))
        {
            $application = JFactory::getApplication();
            $menu = $application->getMenu();
            $item = $menu->getItem($itemid);
            $url = JRoute::_($item->link.'&Itemid='.$itemid, false);
        }
        else
        {
            // stay on the same page
            $uri = JFactory::getURI();
            $url = $uri->toString(array('path', 'query', 'fragment'));
        }

        return base64_encode($url);
    }

    public static function getType()
    {

        $user = JFactory::getUser();
        return (!$user->get('guest')) ? 'logout' : 'login';
    }

    public static function getProfile(&$params)
    {

        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_users  WHERE userID=".(int)$user->id;
        $db->setQuery($query, 0, 1);
        $profile = $db->loadObject();

        if ($profile)
        {
            if ($profile->image != '')
                $profile->avatar = JURI::root().'media/k2/users/'.$profile->image;

            require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'permissions'.'.php');

            if (JRequest::getCmd('option') != 'com_k2')
                K2HelperPermissions::setPermissions();

            if (K2HelperPermissions::canAddItem())
                $profile->addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');

            return $profile;

        }

    }

    public static function countUserComments($userID)
    {

        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) FROM #__k2_comments WHERE userID=".(int)$userID." AND published=1";
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;

    }

    public static function getMenu($params)
    {
        $items = array();
        $children = array();
        if ($params->get('menu'))
        {
            $menu = JSite::getMenu();
            $items = $menu->getItems('menutype', $params->get('menu'));
        }
        foreach ($items as $item)
        {
            if (K2_JVERSION != '15')
            {
                $item->name = $item->title;
                $item->parent = $item->parent_id;
            }
            $index = $item->parent;
            $list = @$children[$index] ? $children[$index] : array();
            array_push($list, $item);
            $children[$index] = $list;
        }
        if (K2_JVERSION != '15')
        {
            $items = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
        }
        else
        {
            $items = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        }
        $links = array();
        foreach ($items as $item)
        {
            if (K2_JVERSION == '15')
            {
                $item->level = $item->sublevel;
                switch ($item->type)
                {
                    case 'separator' :
                        continue;
                        break;

                    case 'url' :
                        if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
                        {
                            $item->url = $item->link.'&amp;Itemid='.$item->id;
                        }
                        else
                        {
                            $item->url = $item->link;
                        }
                        break;

                    default :
                        $router = JSite::getRouter();
                        $item->url = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid='.$item->id : $item->link.'&Itemid='.$item->id;
                        break;
                }

                $iParams = class_exists('JParameter') ? new JParameter($item->params) : new JRegistry($item->params);
                $iSecure = $iParams->def('secure', 0);
                if ($item->home == 1)
                {
                    $item->url = JURI::base();
                }
                elseif (strcasecmp(substr($item->url, 0, 4), 'http') && (strpos($item->link, 'index.php?') !== false))
                {
                    $item->url = JRoute::_($item->url, true, $iSecure);
                }
                else
                {
                    $item->url = str_replace('&', '&amp;', $item->url);
                }
                $item->route = $item->url;

            }
            else
            {

                $item->flink = $item->link;
                switch ($item->type)
                {
                    case 'separator' :
                        continue;

                    case 'url' :
                        if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
                        {
                            $item->flink = $item->link.'&Itemid='.$item->id;
                        }
                        break;

                    case 'alias' :
                        $item->flink = 'index.php?Itemid='.$item->params->get('aliasoptions');
                        break;

                    default :
                        $router = JSite::getRouter();
                        if ($router->getMode() == JROUTER_MODE_SEF)
                        {
                            $item->flink = 'index.php?Itemid='.$item->id;
                        }
                        else
                        {
                            $item->flink .= '&Itemid='.$item->id;
                        }
                        break;
                }

                if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
                {
                    $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                }
                else
                {
                    $item->flink = JRoute::_($item->flink);
                }

                $item->route = $item->flink;

            }
            $links[] = $item;
        }
        return $links;
    }

}
