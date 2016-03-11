<?php
/**
 * @version     2.7.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.view');

class K2ViewItemlist extends K2View
{

    function display($tpl = null)
    {

        $mainframe = JFactory::getApplication();
        $params = K2HelperUtilities::getParams('com_k2');
        $document = JFactory::getDocument();
        if (K2_JVERSION == '15')
        {
            $document->setMimeEncoding('application/json');
            $document->setType('json');
        }
        $model = $this->getModel('itemlist');

        //Set limit for model
        $limit = JRequest::getInt('limit');
        if ($limit > 100 || $limit == 0)
        {
            $limit = 100;
            JRequest::setVar('limit', $limit);
        }
        $page = JRequest::getInt('page');
        if ($page <= 0)
        {
            $limitstart = 0;
        }
        else
        {
            $page--;
            $limitstart = $page * $limit;
        }
        JRequest::setVar('limitstart', $limitstart);

        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');

        $response = new JObject();
        unset($response->_errors);

        // Site
        $response->site = new stdClass();
        $uri = JURI::getInstance();
        $response->site->url = $uri->toString(array('scheme', 'host', 'port'));
        $config = JFactory::getConfig();
        $response->site->name = K2_JVERSION == '30' ? $config->get('sitename') : $config->getValue('config.sitename');

        $moduleID = JRequest::getInt('moduleID');
        if ($moduleID)
        {

            $result = $model->getModuleItems($moduleID);
            $items = $result->items;
            $title = $result->title;
            $prefix = 'cat';

        }
        else
        {

            //Get data depending on task
            switch ($task)
            {

                case 'category' :
                    //Get category
                    $id = JRequest::getInt('id');
                    JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
                    $category = JTable::getInstance('K2Category', 'Table');
                    $category->load($id);

                    // State Check
                    if (!$category->published || $category->trash)
                    {
                        JError::raiseError(404, JText::_('K2_CATEGORY_NOT_FOUND'));
                    }

                    //Access check
                    $user = JFactory::getUser();
                    if (K2_JVERSION != '15')
                    {
                        if (!in_array($category->access, $user->getAuthorisedViewLevels()))
                        {
                            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                        }
                        $languageFilter = $mainframe->getLanguageFilter();
                        $languageTag = JFactory::getLanguage()->getTag();
                        if ($languageFilter && $category->language != $languageTag && $category->language != '*')
                        {
                            return;
                        }
                    }
                    else
                    {
                        if ($category->access > $user->get('aid', 0))
                        {
                            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                        }
                    }

                    //Merge params
                    $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
                    if ($cparams->get('inheritFrom'))
                    {
                        $masterCategory = JTable::getInstance('K2Category', 'Table');
                        $masterCategory->load($cparams->get('inheritFrom'));
                        $cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                    }
                    $params->merge($cparams);

                    //Category link
                    $category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));

                    //Category image
                    $category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

                    //Category plugins
                    $dispatcher = JDispatcher::getInstance();
                    JPluginHelper::importPlugin('content');
                    $category->text = $category->description;

                    if (K2_JVERSION != '15')
                    {
                        $dispatcher->trigger('onContentPrepare', array('com_k2.category', &$category, &$params, $limitstart));
                    }
                    else
                    {
                        $dispatcher->trigger('onPrepareContent', array(&$category, &$params, $limitstart));
                    }

                    $category->description = $category->text;

                    //Category K2 plugins
                    $category->event = new stdClass;
                    $category->event->K2CategoryDisplay = '';
                    JPluginHelper::importPlugin('k2');
                    $results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
                    $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                    $category->text = $category->description;
                    $dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
                    $category->description = $category->text;

                    //Category children
                    $ordering = $params->get('subCatOrdering');
                    $children = $model->getCategoryFirstChildren($id, $ordering);
                    $subCategories = array();
                    if (count($children))
                    {
                        foreach ($children as $child)
                        {
                            if ($params->get('subCatTitleItemCounter'))
                            {
                                $child->numOfItems = $model->countCategoryItems($child->id);
                            }
                            $child->image = K2HelperUtilities::getCategoryImage($child->image, $params);
                            $child->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($child->id.':'.urlencode($child->alias))));
                            unset($child->params);
                            unset($child->access);
                            unset($child->published);
                            unset($child->trash);
                            unset($child->language);
                            $subCategories[] = $child;
                        }
                    }

                    //Set featured flag
                    JRequest::setVar('featured', $params->get('catFeaturedItems'));

                    //Set title
                    $title = $category->name;

                    // Set ordering
                    if ($params->get('singleCatOrdering'))
                    {
                        $ordering = $params->get('singleCatOrdering');
                    }
                    else
                    {
                        $ordering = $params->get('catOrdering');
                    }

                    // Set parameters prefix
                    $prefix = 'cat';
                    // Prepare the JSON category object;
                    $row = new JObject();
                    unset($row->_errors);
                    $row->id = $category->id;
                    $row->name = $category->name;
                    $row->alias = $category->alias;
                    $row->link = $category->link;
                    $row->parent = $category->parent;
                    $row->extraFieldsGroup = $category->extraFieldsGroup;
                    $row->image = $category->image;
                    $row->ordering = $category->ordering;
                    //$row->plugins = $category->plugins;
                    $row->events = $category->event;
                    $row->chidlren = $subCategories;
                    $response->category = $row;
                    break;

                case 'user' :
                    //Get user
                    $id = JRequest::getInt('id');
                    $userObject = JFactory::getUser($id);

                    //Check user status
                    if ($userObject->block)
                    {
                        JError::raiseError(404, JText::_('K2_USER_NOT_FOUND'));
                    }

                    //Get K2 user profile
                    $userObject->profile = $model->getUserProfile();

                    //User image
                    $userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

                    //User K2 plugins
                    $userObject->event->K2UserDisplay = '';
                    if (is_object($userObject->profile) && $userObject->profile->id > 0)
                    {
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('k2');
                        $results = $dispatcher->trigger('onK2UserDisplay', array(&$userObject->profile, &$params, $limitstart));
                        $userObject->event->K2UserDisplay = trim(implode("\n", $results));
                        $userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'UTF-8');

                    }

                    //Set title
                    $title = $userObject->name;

                    // Set ordering
                    $ordering = $params->get('userOrdering');

                    // Set parameters prefix
                    $prefix = 'user';
                    // Prepare the JSON user object;
                    $row = new JObject();
                    unset($row->_errors);
                    //$row->id = $userObject->id;
                    $row->name = $userObject->name;
                    //$row->username = $userObject->username;
                    if (isset($userObject->profile->plugins))
                    {
                        unset($userObject->profile->plugins);
                    }
                    $row->profile = $userObject->profile;
                    $row->avatar = $userObject->avatar;
                    $row->events = $userObject->event;
                    $response->user = $row;
                    break;

                case 'tag' :
                    //Set limit
                    $limit = $params->get('tagItemCount');

                    //set title
                    $title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.JRequest::getVar('tag');

                    // Set ordering
                    $ordering = $params->get('tagOrdering');

                    // Set parameters prefix
                    $prefix = 'tag';
                    $response->tag = JRequest::getVar('tag');
                    break;

                case 'search' :

                    //Set title
                    $title = JText::_('K2_SEARCH_RESULTS_FOR').' '.JRequest::getVar('searchword');

                    // Set parameters prefix
                    $prefix = 'generic';
                    $response->search = JRequest::getVar('searchword');
                    break;

                case 'date' :

                    // Set title
                    if (JRequest::getInt('day'))
                    {
                        $date = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month').'-'.JRequest::getInt('day'));
                        $dateFormat = (K2_JVERSION == '15') ? '%A, %d %B %Y' : 'l, d F Y';
                        $title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
                    }
                    else
                    {
                        $date = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month'));
                        $dateFormat = (K2_JVERSION == '15') ? '%B %Y' : 'F Y';
                        $title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
                    }
                    // Set ordering
                    $ordering = 'rdate';

                    // Set parameters prefix
                    $prefix = 'generic';
                    $response->date = JHTML::_('date', $date, $dateFormat);
                    break;

                default :
                    $user = JFactory::getUser();

                    //Set limit
                    $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');
                    //Set featured flag
                    JRequest::setVar('featured', $params->get('catFeaturedItems'));

                    //Set title
                    $title = $params->get('page_title');

                    // Set ordering
                    $ordering = $params->get('catOrdering');

                    // Set parameters prefix
                    $prefix = 'cat';

                    break;
            }

            if (!isset($ordering))
            {
                $items = $model->getData();
            }
            else
            {
                $items = $model->getData($ordering);
            }

        }

        //Prepare items
        $user = JFactory::getUser();
        $cache = JFactory::getCache('com_k2_extended');
        $model = $this->getModel('item');
        $rows = array();
        for ($i = 0; $i < sizeof($items); $i++)
        {

            //Item group
            if ($task == "category" || $task == "")
            {
                $items[$i]->itemGroup = 'links';

                if ($i < ($params->get('num_links') + $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items')))
                    $items[$i]->itemGroup = 'links';
                if ($i < ($params->get('num_secondary_items') + $params->get('num_leading_items') + $params->get('num_primary_items')))
                    $items[$i]->itemGroup = 'secondary';
                if ($i < ($params->get('num_primary_items') + $params->get('num_leading_items')))
                    $items[$i]->itemGroup = 'primary';
                if ($i < $params->get('num_leading_items'))
                    $items[$i]->itemGroup = 'leading';
            }
            else
            {
                $items[$i]->itemGroup = '';
            }

            $itemParams = class_exists('JParameter') ? new JParameter($items[$i]->params) : new JRegistry($items[$i]->params);
            $itemParams->set($prefix.'ItemIntroText', true);
            $itemParams->set($prefix.'ItemFullText', true);
            $itemParams->set($prefix.'ItemTags', true);
            $itemParams->set($prefix.'ItemExtraFields', true);
            $itemParams->set($prefix.'ItemAttachments', true);
            $itemParams->set($prefix.'ItemRating', true);
            $itemParams->set($prefix.'ItemAuthor', true);
            $itemParams->set($prefix.'ItemImageGallery', true);
            $itemParams->set($prefix.'ItemVideo', true);
            $itemParams->set($prefix.'ItemImage', true);
            $items[$i]->params = $itemParams->toString();

            //Check if model should use cache for preparing item even if user is logged in
            if ($user->guest || $task == 'tag' || $task == 'search' || $task == 'date')
            {
                $cacheFlag = true;
            }
            else
            {
                $cacheFlag = true;
                if (K2HelperPermissions::canEditItem($items[$i]->created_by, $items[$i]->catid))
                {
                    $cacheFlag = false;
                }
            }

            //Prepare item
            if ($cacheFlag)
            {
                $hits = $items[$i]->hits;
                $items[$i]->hits = 0;
                JTable::getInstance('K2Category', 'Table');
                $items[$i] = $cache->call(array($model, 'prepareItem'), $items[$i], $view, $task);
                $items[$i]->hits = $hits;
            }
            else
            {
                $items[$i] = $model->prepareItem($items[$i], $view, $task);
            }

            //Plugins
            $items[$i] = $model->execPlugins($items[$i], $view, $task);

            //Trigger comments counter event
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('k2');
            $results = $dispatcher->trigger('onK2CommentsCounter', array(&$items[$i], &$params, $limitstart));
            $items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));

            // Set default image
            if ($task == 'user' || $task == 'tag' || $task == 'search' || $task == 'date')
            {
                $items[$i]->image = (isset($items[$i]->imageGeneric)) ? $items[$i]->imageGeneric : '';
            }
            else
            {
                if (!$moduleID)
                {
                    K2HelperUtilities::setDefaultImage($items[$i], $view, $params);

                }
            }

            $rows[] = $model->prepareJSONItem($items[$i]);

        }

        $response->items = $rows;
        
        // Prevent spammers from using the tag view
        if ($task == 'tag' && !count($response->items))
        {
            $tag = JRequest::getString('tag');
            $db = JFactory::getDBO();
            $db->setQuery('SELECT id FROM #__k2_tags WHERE name = '.$db->quote($tag));
            $tagID = $db->loadResult();
            if (!$tagID)
            {
                JError::raiseError(404, JText::_('K2_NOT_FOUND'));
                return false;
            }
        }

        // Output
        $json = json_encode($response);
        $callback = JRequest::getCmd('callback');
        if ($callback)
        {
            $document->setMimeEncoding('application/javascript');
            echo $callback.'('.$json.')';
        }
        else
        {
            echo $json;
        }

    }

}
