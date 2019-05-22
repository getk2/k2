<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewItemlist extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $db = JFactory::getDbo();
        $config = JFactory::getConfig();
        $user = JFactory::getUser();
        $view = JRequest::getCmd('view');
        $task = JRequest::getCmd('task');
        $limitstart = JRequest::getInt('limitstart', 0);
        $limit = JRequest::getInt('limit', 10);
        $moduleID = JRequest::getInt('moduleID');

        $params = K2HelperUtilities::getParams('com_k2');
        $cache = JFactory::getCache('com_k2_extended');
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

        $itemlistModel = $this->getModel('itemlist');
        $itemModel = $this->getModel('item');

        // Menu
        $menu = $app->getMenu();
        $menuDefault = $menu->getDefault();
        $menuActive = $menu->getActive();

        // Dates
        $date = JFactory::getDate();
        $now = (K2_JVERSION == '15') ? $date->toMySQL() : $date->toSql();
        $this->assignRef('now', $now);

        $nullDate = $db->getNullDate();
        $this->assignRef('nullDate', $nullDate);

        // Import plugins
        JPluginHelper::importPlugin('content');
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();

        // --- Feed Output [start] ---
        if ($document->getType() == 'feed') {
            if ($moduleID) {
                $result = $itemlistModel->getModuleItems($moduleID);
                $title = $result->title;
                $items = $result->items;
            }
        }
        // --- Feed Output [finish] ---

        // --- JSON Output [start] ---
        // Set the document type in Joomla 1.5
        if (K2_JVERSION == '15' && JRequest::getCmd('format') == 'json') {
            $document->setMimeEncoding('application/json');
            $document->setType('json');
        }
        if ($document->getType() == 'json') {
            // Prepare JSON output
            $response = new JObject();
            unset($response->_errors);

            $response->site = new stdClass();
            $uri = JURI::getInstance();
            $response->site->url = $uri->toString(array('scheme', 'host', 'port'));
            $response->site->name = (K2_JVERSION == '30') ? $config->get('sitename') : $config->getValue('config.sitename');

            // Handle K2 Content (module)
            if ($moduleID) {
                $result = $itemlistModel->getModuleItems($moduleID);
                $items = $result->items;
                $title = $result->title;
                $prefix = 'cat';
            }
        }
        // --- JSON Output [finish] ---

        // Get data based on task
        if (!$moduleID) {
            switch ($task) {
                case 'category':
                    // Get category
                    $id = JRequest::getInt('id');

                    $category = JTable::getInstance('K2Category', 'Table');
                    $category->load($id);

                    // State check
                    if (!$category->published || $category->trash) {
                        JError::raiseError(404, JText::_('K2_CATEGORY_NOT_FOUND'));
                    }

                    // Access check
                    if (K2_JVERSION != '15') {
                        if (!in_array($category->access, $user->getAuthorisedViewLevels())) {
                            if ($user->guest) {
                                $uri = JFactory::getURI();
                                $url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());
                                $app->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
                                $app->redirect(JRoute::_($url, false));
                            } else {
                                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                                return;
                            }
                        }
                        $languageFilter = $app->getLanguageFilter();
                        $languageTag = JFactory::getLanguage()->getTag();
                        if ($languageFilter && $category->language != $languageTag && $category->language != '*') { // Test logic
                            return;
                        }
                    } else {
                        if ($category->access > $user->get('aid', 0)) {
                            if ($user->guest) {
                                $uri = JFactory::getURI();
                                $url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
                                $app->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
                                $app->redirect(JRoute::_($url, false));
                            } else {
                                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                                return;
                            }
                        }
                    }

                    // Merge params
                    $cparams = (class_exists('JParameter')) ? new JParameter($category->params) : new JRegistry($category->params);

                    // Get the meta information before merging params since we do not want them to be inherited
                    $category->metaDescription = $cparams->get('catMetaDesc');
                    $category->metaKeywords = $cparams->get('catMetaKey');
                    $category->metaRobots = $cparams->get('catMetaRobots');
                    $category->metaAuthor = $cparams->get('catMetaAuthor');

                    if ($cparams->get('inheritFrom')) {
                        $masterCategory = JTable::getInstance('K2Category', 'Table');
                        $masterCategory->load($cparams->get('inheritFrom'));
                        $cparams = (class_exists('JParameter')) ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                    }
                    $params->merge($cparams);

                    // Category link
                    $category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));

                    // Category image
                    $category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

                    // Category plugins
                    $category->text = $category->description;
                    if (K2_JVERSION != '15') {
                        $dispatcher->trigger('onContentPrepare', array(
                            'com_k2.category',
                            &$category,
                            &$params,
                            $limitstart
                        ));
                    } else {
                        $dispatcher->trigger('onPrepareContent', array(
                            &$category,
                            &$params,
                            $limitstart
                        ));
                    }
                    $category->description = $category->text;

                    // Category K2 plugins
                    $category->event = new stdClass;

                    $category->event->K2CategoryDisplay = '';
                    $results = $dispatcher->trigger('onK2CategoryDisplay', array(
                        &$category,
                        &$params,
                        $limitstart
                    ));

                    $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                    $category->text = $category->description;
                    $dispatcher->trigger('onK2PrepareContent', array(
                        &$category,
                        &$params,
                        $limitstart
                    ));
                    $category->description = $category->text;

                    $this->assignRef('category', $category);
                    $this->assignRef('user', $user);

                    // Category children
                    $subCategories = array();
                    $ordering = $params->get('subCatOrdering');
                    $children = $itemlistModel->getCategoryFirstChildren($id, $ordering);
                    if (count($children)) {
                        foreach ($children as $child) {
                            if ($params->get('subCatTitleItemCounter')) {
                                $child->numOfItems = $itemlistModel->countCategoryItems($child->id);
                            }
                            $child->image = K2HelperUtilities::getCategoryImage($child->image, $params);
                            $child->name = htmlspecialchars($child->name, ENT_QUOTES, 'utf-8');
                            $child->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($child->id.':'.urlencode($child->alias))));
                            $subCategories[] = $child;
                        }
                        $this->assignRef('subCategories', $subCategories);
                    }

                    // Set layout
                    $this->setLayout('category');

                    // Set featured flag
                    JRequest::setVar('featured', $params->get('catFeaturedItems'));

                    // Set limit
                    $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

                    // Set ordering
                    if ($params->get('singleCatOrdering')) {
                        $ordering = $params->get('singleCatOrdering');
                    } else {
                        $ordering = $params->get('catOrdering');
                    }

                    // Set title
                    $title = $category->name;
                    $category->name = htmlspecialchars($category->name, ENT_QUOTES, 'utf-8'); // Check this

                    // Set head feed link
                    $addHeadFeedLink = $params->get('catFeedLink');

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'cat';

                        // Prepare the JSON category object
                        $row = new JObject();
                        unset($row->_errors);
                        $row->id = $category->id;
                        $row->alias = $category->alias;
                        $row->link = $category->link;
                        $row->name = $category->name;
                        $row->description = $category->description;
                        $row->image = $category->image;
                        $row->extraFieldsGroup = $category->extraFieldsGroup;
                        $row->ordering = $category->ordering;
                        $row->parent = $category->parent;
                        $row->children = $subCategories;
                        $row->events = $category->event;

                        $response->category = $row;
                    }
                    // --- JSON Output [finish] ---

                    break;

                case 'user':
                    // Get user
                    $id = JRequest::getInt('id');
                    $userObject = JFactory::getUser($id);

                    // Check user status
                    if ($userObject->block) {
                        JError::raiseError(404, JText::_('K2_USER_NOT_FOUND'));
                    }

                    // Get K2 user profile
                    $userObject->profile = $itemlistModel->getUserProfile();

                    // User image
                    $userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

                    // User K2 plugins
                    $userObject->event = new stdClass;

                    $userObject->event->K2UserDisplay = '';
                    if (is_object($userObject->profile) && $userObject->profile->id > 0) {
                        $results = $dispatcher->trigger('onK2UserDisplay', array(
                            &$userObject->profile,
                            &$params,
                            $limitstart
                        ));
                        $userObject->event->K2UserDisplay = trim(implode("\n", $results));
                        $userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'utf-8');
                    }
                    $this->assignRef('user', $userObject);

                    // Set layout
                    $this->setLayout('user');

                    // Set limit
                    $limit = $params->get('userItemCount');

                    // Set ordering
                    $ordering = $params->get('userOrdering');

                    // Set title
                    $title = $userObject->name;

                    // Set head feed link
                    $addHeadFeedLink = $params->get('userFeedLink', 1);

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'user';

                        // Prepare the JSON user object
                        $row = new JObject();
                        unset($row->_errors);
                        $row->name = $userObject->name;
                        $row->avatar = $userObject->avatar;
                        $row->profile = $userObject->profile;
                        if (isset($userObject->profile->plugins)) {
                            unset($userObject->profile->plugins);
                        }
                        $row->events = $userObject->event;

                        $response->user = $row;
                    }
                    // --- JSON Output [finish] ---

                    break;

                case 'tag':
                    // Prevent spammers from using the tag view
                    $tag = JRequest::getString('tag');
                    $db->setQuery('SELECT id, name FROM #__k2_tags WHERE name = '.$db->quote($tag));
                    $tag = $db->loadObject();
                    if (!$tag || !$tag->id) {
                        JError::raiseError(404, JText::_('K2_NOT_FOUND'));
                        return false;
                    }

                    // Set layout
                    $this->setLayout('tag');

                    // Set limit
                    $limit = $params->get('tagItemCount');

                    // Set ordering
                    $ordering = $params->get('tagOrdering');

                   // Set title
                    $title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.$tag->name;

                    // Set head feed link
                    $addHeadFeedLink = $params->get('tagFeedLink', 1);

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'tag';

                        $response->tag = JRequest::getVar('tag');
                    }
                    // --- JSON Output [finish] ---

                    break;

                case 'search':
                    // Set layout
                    $this->setLayout('generic');

                    // Set limit
                    $limit = $params->get('genericItemCount');

                    // Set title
                    $title = JText::_('K2_SEARCH_RESULTS_FOR').' '.JRequest::getVar('searchword');

                    // Set head feed link
                    $addHeadFeedLink = $params->get('genericFeedLink', 1);

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'generic';

                        $response->search = JRequest::getVar('searchword');
                    }
                    // --- JSON Output [finish] ---

                    break;

                case 'date':
                    // Set layout
                    $this->setLayout('generic');

                    // Set limit
                    $limit = $params->get('genericItemCount');

                    // Set ordering
                    $ordering = 'rdate';

                    // Fix wrong timezone
                    if (function_exists('date_default_timezone_get')) {
                        $originalTimezone = date_default_timezone_get();
                    }
                    if (function_exists('date_default_timezone_set')) {
                        date_default_timezone_set('UTC');
                    }

                    // Set title
                    if (JRequest::getInt('day')) {
                        $dateFromRequest = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month').'-'.JRequest::getInt('day'));
                        $dateFormat = (K2_JVERSION == '15') ? '%A, %d %B %Y' : 'l, d F Y';
                    } else {
                        $dateFromRequest = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month'));
                        $dateFormat = (K2_JVERSION == '15') ? '%B %Y' : 'F Y';
                    }
                    $title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $dateFromRequest, $dateFormat);

                    // Restore the original timezone
                    if (function_exists('date_default_timezone_set') && isset($originalTimezone)) {
                        date_default_timezone_set($originalTimezone);
                    }

                    // Set head feed link
                    $addHeadFeedLink = $params->get('genericFeedLink', 1);

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'generic';

                        $response->date = JHTML::_('date', $dateFromRequest, $dateFormat);
                    }
                    // --- JSON Output [finish] ---

                    break;

                default:
                    $this->assignRef('user', $user);

                    // Set layout
                    $this->setLayout('category');

                    // Set featured flag
                    JRequest::setVar('featured', $params->get('catFeaturedItems'));

                    // Set limit
                    $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

                    // Set ordering
                    $ordering = $params->get('catOrdering');

                    // Set title
                    $title = $params->get('page_title');

                    // Set head feed link
                    $addHeadFeedLink = $params->get('catFeedLink', 1);

                    // --- JSON Output [start] ---
                    if ($document->getType() == 'json') {
                        // Set parameters prefix
                        $prefix = 'cat';
                    }
                    // --- JSON Output [finish] ---

                    break;
            }

            // --- Feed Output [start] ---
            if ($document->getType() == 'feed') {
                $title = JFilterOutput::ampReplace($title);
                $limit = $params->get('feedLimit');
            }
            // --- Feed Output [finish] ---

            // Set a default limit (for the model) if none is found
            if (!$limit) {
                $limit = 10;
            }
            // Allow Feed & JSON outputs to request more items that the preset limit
            if (in_array($document->getType(), ['feed', 'json']) && JRequest::getInt('limit')) {
                $limit = JRequest::getInt('limit');
            }
            // Protect from large limit requests
            if ($limit > 100) {
                $limit = 100;
            }
            JRequest::setVar('limit', $limit);

            // Allow for simplified paginated results using "page"
            $page = JRequest::getInt('page');
            if ($page) {
                $limitstart = $page * $limit;
                JRequest::setVar('limitstart', $limitstart);
            }

            // Get items
            if (!isset($ordering)) {
                $items = $itemlistModel->getData();
            } else {
                $items = $itemlistModel->getData($ordering);
            }

            // If a user has no published items, do not display their K2 user page (in the frontend) and redirect to the homepage of the site.
            $userPageDisplay = 0;
            switch ($params->get('profilePageDisplay', 0)) {
                case 1:
                    $userPageDisplay = 1;
                    break;
                case 2:
                    if ($user->id > 0) {
                        $userPageDisplay = 1;
                    }
                    break;
            }
            if ((count($items) == 0 && $task == 'user') && $userPageDisplay == 0) {
                $app->redirect(JUri::root());
            }
        }

        if ($document->getType() != 'json') {
            // Pagination
            jimport('joomla.html.pagination');
            $total = (count($items)) ? $itemlistModel->getTotal() : 0;
            $pagination = new JPagination($total, $limitstart, $limit);
        }

        $rowsForJSON = array();

        for ($i = 0; $i < count($items); $i++) {
            // Ensure that all items have a group. Group-less items get assigned to the leading group
            $items[$i]->itemGroup = 'leading';

            // Item group
            if ($task == "category" || $task == "") {
                if ($i < ($params->get('num_links') + $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items'))) {
                    $items[$i]->itemGroup = 'links';
                }
                if ($i < ($params->get('num_secondary_items') + $params->get('num_leading_items') + $params->get('num_primary_items'))) {
                    $items[$i]->itemGroup = 'secondary';
                }
                if ($i < ($params->get('num_primary_items') + $params->get('num_leading_items'))) {
                    $items[$i]->itemGroup = 'primary';
                }
                if ($i < $params->get('num_leading_items')) {
                    $items[$i]->itemGroup = 'leading';
                }
            }

            // --- Feed Output [start] ---
            if ($document->getType() == 'feed') {
                $item = $itemModel->prepareFeedItem($items[$i]);
                $item->title = html_entity_decode($this->escape($item->title));

                $feedItem = new JFeedItem();
                $feedItem->link = $item->link;
                $feedItem->title = $item->title;
                $feedItem->description = $item->description;
                $feedItem->date = $item->created;
                $feedItem->category = $item->category->name;
                $feedItem->author = $item->author->name;
                if ($params->get('feedBogusEmail')) {
                    $feedItem->authorEmail = $params->get('feedBogusEmail');
                } else {
                    if ($app->getCfg('feed_email') == 'author') {
                        $feedItem->authorEmail = $item->author->email;
                    } else {
                        $feedItem->authorEmail = $app->getCfg('mailfrom');
                    }
                }
                if ($params->get('feedItemImage') && JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_'.$params->get('feedImgSize').'.jpg')) {
                    $feedItem->setEnclosure($item->enclosure);
                }

                // Add feed item
                $document->addItem($feedItem);
            }
            // --- Feed Output [finish] ---

            // --- JSON Output [start] ---
            if ($document->getType() == 'json') {
                // Override some display parameters to show a minimum of content elements
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
            }
            // --- JSON Output [finish] ---

            // Check if the model should use the cache for preparing the item even if the user is logged in
            if ($user->guest || $task == 'tag' || $task == 'search' || $task == 'date') {
                $cacheFlag = true;
            } else {
                $cacheFlag = true;
                if (K2HelperPermissions::canEditItem($items[$i]->created_by, $items[$i]->catid)) {
                    $cacheFlag = false;
                }
            }

            // Prepare item
            if ($cacheFlag) {
                $hits = $items[$i]->hits;
                $items[$i]->hits = 0;
                JTable::getInstance('K2Category', 'Table');
                $items[$i] = $cache->call(array(
                    $itemModel,
                    'prepareItem'
                ), $items[$i], $view, $task);
                $items[$i]->hits = $hits;
            } else {
                $items[$i] = $itemModel->prepareItem($items[$i], $view, $task);
            }

            // Plugins
            $items[$i] = $itemModel->execPlugins($items[$i], $view, $task);

            // Trigger comments counter event if needed
            if (
                $params->get('catItemK2Plugins') &&
                ($params->get('catItemCommentsAnchor') || $params->get('itemCommentsAnchor') || $params->get('itemComments'))
            ) {
                $results = $dispatcher->trigger('onK2CommentsCounter', array(
                    &$items[$i],
                    &$params,
                    $limitstart
                ));
                $items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));
            }

            // --- JSON Output [start] ---
            if ($document->getType() == 'json') {
                // Set default image
                if ($task == 'date' || $task == 'search' || $task == 'tag' || $task == 'user') {
                    $items[$i]->image = (isset($items[$i]->imageGeneric)) ? $items[$i]->imageGeneric : '';
                } else {
                    if (!$moduleID) {
                        K2HelperUtilities::setDefaultImage($items[$i], $view, $params);
                    }
                }

                $rowsForJSON[] = $itemModel->prepareJSONItem($items[$i]);
            }
            // --- JSON Output [finish] ---
        }

        // --- JSON Output [start] ---
        if ($document->getType() == 'json') {
            $response->items = $rowsForJSON;

            // Output
            $json = json_encode($response);
            $callback = JRequest::getCmd('callback');
            if ($callback) {
                $document->setMimeEncoding('application/javascript');
                echo $callback.'('.$json.')';
            } else {
                echo $json;
            }
        }
        // --- JSON Output [finish] ---

        // Add item link
        if (K2HelperPermissions::canAddItem()) {
            $addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
            $this->assignRef('addLink', $addLink);
        }

        // Feed link
        if ($task == 'tag') {
            $link = K2HelperRoute::getTagRoute(JRequest::getVar('tag'));
        } else {
            $link = '';
        }
        $sef = (K2_JVERSION == '30') ? $config->get('sef') : $config->getValue('config.sef');
        if (!is_null($menuActive) && $menuActive->id == $menuDefault->id && $sef) {
            $link .= '&Itemid='.$menuActive->id.'&format=feed&limitstart=';
        } else {
            $link .= '&format=feed&limitstart=';
        }
        $feed = JRoute::_($link);
        $this->assignRef('feed', $feed);

        // Pathway
        $pathway = $app->getPathWay();
        if (!isset($menuActive->query['task'])) {
            $menuActive->query['task'] = '';
        }
        if ($menuActive) {
            switch ($task) {
                case 'category':
                    if ($menuActive->query['task'] != 'category' || $menuActive->query['id'] != JRequest::getInt('id')) {
                        $pathway->addItem($title, '');
                    }
                    break;
                case 'user':
                    if ($menuActive->query['task'] != 'user' || $menuActive->query['id'] != JRequest::getInt('id')) {
                        $pathway->addItem($title, '');
                    }
                    break;

                case 'tag':
                    if ($menuActive->query['task'] != 'tag' || $menuActive->query['tag'] != JRequest::getVar('tag')) {
                        $pathway->addItem($title, '');
                    }
                    break;

                case 'search':
                case 'date':
                    $pathway->addItem($title, '');
                    break;
            }
        }

        // --- B/C stuff [start] ---
        // Update the Google Search results container
        if ($task == 'search') {
            $params->set('googleSearch', 0);
            $googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
            if ($googleSearchContainerID == 'k2Container') {
                $googleSearchContainerID = 'k2GoogleSearchContainer';
            }
            $params->set('googleSearchContainer', $googleSearchContainerID);
        }
        // --- B/C stuff [finish] ---

        // Head Stuff
        if (!in_array($document->getType(), ['feed', 'json', 'raw'])) {
            // Set canonical link
            if ($task == 'category') {
                $canonicalURL = $params->get('canonicalURL', 'relative');
                if ($canonicalURL == 'absolute') {
                    $document->addHeadLink(substr(str_replace(JUri::root(true), '', JUri::root(false)), 0, -1).$category->link, 'canonical', 'rel');
                }
                if ($canonicalURL == 'relative') {
                    $document->addHeadLink($category->link, 'canonical', 'rel');
                }
            }

            // Set page title
            if (is_object($menuActive) && isset($menuActive->params)) {
                if (is_string($menuActive->params)) {
                    $menu_params = (K2_JVERSION == '15') ? new JParameter($menuActive->params) : new JRegistry($menuActive->params);
                } else {
                    $menu_params = $menuActive->params;
                }
                if (!$menu_params->get('page_title')) {
                    $params->set('page_title', $title);
                }
            } else {
                $params->set('page_title', $title);
            }

            // We're adding a new variable here which won't get the appended/prepended site title,
            // when enabled via Joomla's SEO/SEF settings
            $params->set('page_title_clean', $title);

            if (K2_JVERSION != '15') {
                if ($app->getCfg('sitename_pagetitles', 0) == 1) {
                    $tmpTitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $params->get('page_title'));
                    $params->set('page_title', $tmpTitle);
                } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
                    $tmpTitle = JText::sprintf('JPAGETITLE', $params->get('page_title'), $app->getCfg('sitename'));
                    $params->set('page_title', $tmpTitle);
                }
            }
            $document->setTitle($params->get('page_title'));

            // Set metadata for category
            if ($task == 'category') {
                if ($category->metaDescription) {
                    $document->setDescription($category->metaDescription);
                } else {
                    $metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $this->category->description);
                    $metaDescItem = strip_tags($metaDescItem);
                    $metaDescItem = K2HelperUtilities::characterLimit($metaDescItem, $params->get('metaDescLimit', 150));
                    if (K2_JVERSION != '15') {
                        $metaDescItem = html_entity_decode($metaDescItem);
                    }
                    $document->setDescription($metaDescItem);
                }
                if ($category->metaKeywords) {
                    $document->setMetadata('keywords', $category->metaKeywords);
                }
                if ($category->metaRobots) {
                    $document->setMetadata('robots', $category->metaRobots);
                }
                if ($category->metaAuthor) {
                    $document->setMetadata('author', $category->metaAuthor);
                }
            }

            if (K2_JVERSION != '15') {
                // Menu metadata options
                if ($params->get('menu-meta_description')) {
                    $document->setDescription($params->get('menu-meta_description'));
                }

                if ($params->get('menu-meta_keywords')) {
                    $document->setMetadata('keywords', $params->get('menu-meta_keywords'));
                }

                if ($params->get('robots')) {
                    $document->setMetadata('robots', $params->get('robots'));
                }

                // Menu page display options
                if ($params->get('page_heading')) {
                    $params->set('page_title', $params->get('page_heading'));
                }
                $params->set('show_page_title', $params->get('show_page_heading'));
            }

            // Add head feed link
            if ($addHeadFeedLink) {
                $document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', array(
                    'type' => 'application/rss+xml',
                    'title' => 'RSS 2.0'
                ));
                $document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', array(
                    'type' => 'application/atom+xml',
                    'title' => 'Atom 1.0'
                ));
            }

            // Common for social meta tags
            $uri = JURI::getInstance();
            $metaUrl = $uri->toString();
            $metaTitle = ($title) ? $title : $document->getTitle();
            $metaDesc = strip_tags($document->getDescription());
            $metaImage = '';
            if ($task == 'category' && $this->category->image && strpos($this->category->image, 'placeholder/category.png') === false) {
                $metaImage = substr(JURI::root(), 0, -1).str_replace(JURI::root(true), '', $this->category->image);
            }

            // Set Facebook meta tags
            if ($params->get('facebookMetatags', 1)) {
                $document->setMetaData('og:url', $metaUrl);
                $document->setMetaData('og:type', 'website');
                $document->setMetaData('og:title', filter_var($metaTitle, FILTER_SANITIZE_STRING));
                $document->setMetaData('og:description', $metaDesc);
                if ($metaImage) {
                    $document->setMetaData('og:image', $metaImage);
                    $document->setMetaData('image', $metaImage);
                }
            }

            // Set Twitter meta tags
            if ($params->get('twitterMetatags', 1)) {
                $document->setMetaData('twitter:card', 'summary');
                if ($params->get('twitterUsername')) {
                    $document->setMetaData('twitter:site', '@'.$params->get('twitterUsername'));
                }
                $document->setMetaData('twitter:title', filter_var($metaTitle, FILTER_SANITIZE_STRING));
                $document->setMetaData('twitter:description', $metaDesc);
                if ($metaImage) {
                    $document->setMetaData('twitter:image', $metaImage);
                    if (!$params->get('facebookMetatags')) {
                        $document->setMetaData('image', $metaImage); // Generic meta
                    }
                    $document->setMetaData('twitter:image:alt', filter_var($metaTitle, FILTER_SANITIZE_STRING));
                }
            }
        }

        if (!in_array($document->getType(), ['feed', 'json'])) {
            // Lookup template folders
            $this->_addPath('template', JPATH_COMPONENT.'/templates');
            $this->_addPath('template', JPATH_COMPONENT.'/templates/default');

            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates');
            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates/default');

            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2');
            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/default');

            $theme = $params->get('theme');
            if ($theme) {
                $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$theme);
                $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates/'.$theme);
                $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/'.$theme);
            }

            // Allow temporary template loading with ?template=
            $template = JRequest::getCmd('template');
            if (isset($template)) {
                // Look for overrides in template folder (new K2 template structure)
                $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2');
                $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2/default');
                if ($theme) {
                    $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2/'.$theme);
                }
            }

            // Assign data
            if ($task == "category" || $task == "") {
                // Leading items
                $offset = 0;
                $length = $params->get('num_leading_items');
                $leading = array_slice($items, $offset, $length);

                // Primary
                $offset = (int)$params->get('num_leading_items');
                $length = (int)$params->get('num_primary_items');
                $primary = array_slice($items, $offset, $length);

                // Secondary
                $offset = (int)($params->get('num_leading_items') + $params->get('num_primary_items'));
                $length = (int)$params->get('num_secondary_items');
                $secondary = array_slice($items, $offset, $length);

                // Links
                $offset = (int)($params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items'));
                $length = (int)$params->get('num_links');
                $links = array_slice($items, $offset, $length);

                $this->assignRef('leading', $leading);
                $this->assignRef('primary', $primary);
                $this->assignRef('secondary', $secondary);
                $this->assignRef('links', $links);
            } else {
                $this->assignRef('items', $items);
            }

            // Set default values to avoid division by zero
            if ($params->get('num_leading_columns') == 0) {
                $params->set('num_leading_columns', 1);
            }
            if ($params->get('num_primary_columns') == 0) {
                $params->set('num_primary_columns', 1);
            }
            if ($params->get('num_secondary_columns') == 0) {
                $params->set('num_secondary_columns', 1);
            }
            if ($params->get('num_links_columns') == 0) {
                $params->set('num_links_columns', 1);
            }

            $this->assignRef('params', $params);
            $this->assignRef('pagination', $pagination);

            // K2 Plugins
            $dispatcher->trigger('onK2BeforeViewDisplay');

            // Display
            parent::display($tpl);
        }
    }

    public function module()
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        if ($document->getType() == 'raw') {
            $componentParams = JComponentHelper::getParams('com_k2');

            $itemlistModel = K2Model::getInstance('Itemlist', 'K2Model');

            jimport('joomla.application.module.helper');
            $moduleID = JRequest::getInt('moduleID');
            if ($moduleID) {
                $result = $itemlistModel->getModuleItems($moduleID);
                $items = $result->items;

                if (is_string($result->params)) {
                    $params = class_exists('JParameter') ? new JParameter($result->params) : new JRegistry($result->params);
                } else {
                    $params = $result->params;
                }

                if ($params->get('getTemplate')) {
                    require(JModuleHelper::getLayoutPath('mod_k2_content', $params->get('getTemplate').'/default'));
                } else {
                    require(JModuleHelper::getLayoutPath($result->module, 'default'));
                }
            }
            $app->close();
        }
    }
}
