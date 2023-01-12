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

jimport('joomla.application.component.view');

class K2ViewLatest extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();

        $params = K2HelperUtilities::getParams('com_k2');

        $cache = JFactory::getCache('com_k2_extended');

        $limit = $params->get('latestItemsLimit');
        $limitstart = JRequest::getInt('limitstart');

        $model = $this->getModel('itemlist');
        $itemModel = $this->getModel('item');

        // Menu
        $menu = $app->getMenu();
        $menuDefault = $menu->getDefault();
        $menuActive = $menu->getActive();

        // Important URLs
        $currentAbsoluteUrl = JUri::getInstance()->toString();
        $currentRelativeUrl = JUri::root(true).str_replace(substr(JUri::root(), 0, -1), '', $currentAbsoluteUrl);

        // Set layout
        $this->setLayout('latest');

        // Import plugins
        JPluginHelper::importPlugin('content');
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();

        if ($params->get('source')) {
            // Categories
            $categoryIDs = $params->get('categoryIDs');
            if (is_string($categoryIDs) && !empty($categoryIDs)) {
                $categoryIDs = array();
                $categoryIDs[] = $params->get('categoryIDs');
            }
            $categories = array();
            JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
            if (is_array($categoryIDs)) {
                foreach ($categoryIDs as $categoryID) {
                    $category = JTable::getInstance('K2Category', 'Table');
                    $category->load($categoryID);
                    $category->event = new stdClass;
                    $languageCheck = true;
                    if (K2_JVERSION != '15') {
                        $accessCheck = in_array($category->access, $user->getAuthorisedViewLevels());
                        if ($app->getLanguageFilter()) {
                            $languageTag = JFactory::getLanguage()->getTag();
                            $languageCheck = in_array($category->language, array($languageTag, '*'));
                        }
                    } else {
                        $accessCheck = $category->access <= $user->get('aid', 0);
                    }

                    if ($category->published && $accessCheck && $languageCheck) {
                        // Merge params
                        $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
                        if ($cparams->get('inheritFrom')) {
                            $masterCategory = JTable::getInstance('K2Category', 'Table');
                            $masterCategory->load($cparams->get('inheritFrom'));
                            $cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                        }
                        $params->merge($cparams);

                        // Category image
                        $category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

                        // Category plugins
                        $category->text = $category->description;

                        if (K2_JVERSION != '15') {
                            $dispatcher->trigger('onContentPrepare', array('com_k2.category', &$category, &$params, $limitstart));
                        } else {
                            $dispatcher->trigger('onPrepareContent', array(&$category, &$params, $limitstart));
                        }
                        $category->description = $category->text;

                        // Category K2 plugins
                        $category->event->K2CategoryDisplay = '';
                        $results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
                        $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                        $category->text = $category->description;
                        $dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
                        $category->description = $category->text;

                        // Category link
                        $link = urldecode(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias)));
                        $category->link = JRoute::_($link);
                        $category->feed = JRoute::_($link.'&format=feed');

                        JRequest::setVar('view', 'itemlist');
                        JRequest::setVar('task', 'category');
                        JRequest::setVar('id', $category->id);
                        JRequest::setVar('featured', 1);
                        JRequest::setVar('limit', $limit);
                        JRequest::setVar('clearFlag', true);

                        $category->name = htmlspecialchars($category->name, ENT_QUOTES, 'utf-8');
                        if ($limit) {
                            $category->items = $model->getData('rdate');

                            JRequest::setVar('view', 'latest');
                            JRequest::setVar('task', '');

                            for ($i = 0; $i < count($category->items); $i++) {
                                $hits = $category->items[$i]->hits;
                                $category->items[$i]->hits = 0;
                                $category->items[$i] = $cache->call(array($itemModel, 'prepareItem'), $category->items[$i], 'latest', '');
                                $category->items[$i]->hits = $hits;
                                $category->items[$i] = $itemModel->execPlugins($category->items[$i], 'latest', '');

                                // Trigger comments counter event
                                $results = $dispatcher->trigger('onK2CommentsCounter', array(&$category->items[$i], &$params, $limitstart));
                                $category->items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));
                            }
                        } else {
                            $category->items = array();
                        }
                        $categories[] = $category;
                    }
                }
            }
            $source = 'categories';
            $this->assignRef('blocks', $categories);
        } else {
            // Users
            $usersIDs = $params->get('userIDs');
            if (is_string($usersIDs) && !empty($usersIDs)) {
                $usersIDs = array();
                $usersIDs[] = $params->get('userIDs');
            }

            $users = array();
            if (is_array($usersIDs)) {
                foreach ($usersIDs as $userID) {
                    $userObject = JFactory::getUser($userID);
                    if (!$userObject->block) {
                        $userObject->event = new stdClass;

                        // User profile
                        $userObject->profile = $model->getUserProfile($userID);

                        // User image
                        $userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

                        // User K2 plugins
                        $userObject->event->K2UserDisplay = '';
                        if (is_object($userObject->profile) && $userObject->profile->id > 0) {
                            $results = $dispatcher->trigger('onK2UserDisplay', array(&$userObject->profile, &$params, $limitstart));
                            $userObject->event->K2UserDisplay = trim(implode("\n", $results));
                            $userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'utf-8');
                        }

                        $link = K2HelperRoute::getUserRoute($userObject->id);
                        $userObject->link = JRoute::_($link);
                        $userObject->feed = JRoute::_($link.'&format=feed');
                        $userObject->name = htmlspecialchars($userObject->name, ENT_QUOTES, 'utf-8');
                        if ($limit) {
                            $userObject->items = $model->getAuthorLatest(0, $limit, $userID);

                            for ($i = 0; $i < count($userObject->items); $i++) {
                                $hits = $userObject->items[$i]->hits;
                                $userObject->items[$i]->hits = 0;
                                $userObject->items[$i] = $cache->call(array($itemModel, 'prepareItem'), $userObject->items[$i], 'latest', '');
                                $userObject->items[$i]->hits = $hits;

                                // Plugins
                                $userObject->items[$i] = $itemModel->execPlugins($userObject->items[$i], 'latest', '');

                                // Trigger comments counter event
                                $results = $dispatcher->trigger('onK2CommentsCounter', array(&$userObject->items[$i], &$params, $limitstart));
                                $userObject->items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));
                            }
                        } else {
                            $userObject->items = array();
                        }
                        $users[] = $userObject;
                    }
                }
            }
            $source = 'users';
            $this->assignRef('blocks', $users);
        }

        // Head Stuff
        if (!in_array($document->getType(), ['raw', 'json'])) {
            // Set canonical link
            $this->setCanonicalUrl($currentAbsoluteUrl);

            // Set <title>
            if (K2_JVERSION != '15') {
                // Prepend/append site name
                if ($app->getCfg('sitename_pagetitles', 0) == 1) {
                    $params->set('page_title', JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $params->get('page_title')));
                } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
                    $params->set('page_title', JText::sprintf('JPAGETITLE', $params->get('page_title'), $app->getCfg('sitename')));
                }

                // B/C assignment so Joomla 2.5+ uses the 'show_page_title' parameter as Joomla 1.5 does
                $params->set('show_page_title', $params->get('show_page_heading'));
            }

            $metaTitle = trim($params->get('page_title'));
            $document->setTitle($metaTitle);

            // Set meta description
            $metaDesc = $document->getMetadata('description');

            if (K2_JVERSION != '15') {
                if ($params->get('menu-meta_description')) {
                    $metaDesc = $params->get('menu-meta_description');
                }
            }

            $metaDesc = trim($metaDesc);
            $document->setDescription(K2HelperUtilities::characterLimit($metaDesc, $params->get('metaDescLimit', 150)));

            // Set meta keywords
            $metaKeywords = $document->getMetadata('keywords');

            if (K2_JVERSION != '15') {
                if ($params->get('menu-meta_keywords')) {
                    $metaKeywords = $params->get('menu-meta_keywords');
                }
            }

            $metaKeywords = trim($metaKeywords);
            $document->setMetadata('keywords', $metaKeywords);

            // Set meta robots
            $metaRobots = (K2_JVERSION != '15') ? $document->getMetadata('robots') : '';

            if (K2_JVERSION != '15') {
                if ($params->get('robots')) {
                    $metaRobots = $params->get('robots');
                }
            }

            $document->setMetadata('robots', $metaRobots);

            // Set Facebook meta tags
            if ($params->get('facebookMetatags', 1)) {
                $document->setMetaData('og:url', $currentAbsoluteUrl);
                $document->setMetaData('og:type', 'website');
                $document->setMetaData('og:title', filter_var($metaTitle, FILTER_SANITIZE_STRING));
                $document->setMetaData('og:description', K2HelperUtilities::characterLimit($metaDesc, 300)); // 300 chars limit for Facebook post sharing
            }

            // Set Twitter meta tags
            if ($params->get('twitterMetatags', 1)) {
                $document->setMetaData('twitter:card', 'summary');
                if ($params->get('twitterUsername')) {
                    $document->setMetaData('twitter:site', '@'.$params->get('twitterUsername'));
                }
                $document->setMetaData('twitter:title', filter_var($metaTitle, FILTER_SANITIZE_STRING));
                $document->setMetaData('twitter:description', K2HelperUtilities::characterLimit($metaDesc, 200)); // 200 chars limit for Twitter post sharing
            }
        }

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
        $this->assignRef('params', $params);
        $this->assignRef('source', $source);
        $this->assignRef('user', $user);

        // Display
        parent::display($tpl);
    }

    private function setCanonicalUrl($url)
    {
        $document = JFactory::getDocument();
        $params = K2HelperUtilities::getParams('com_k2');
        $canonicalURL = $params->get('canonicalURL', 'relative');
        if ($canonicalURL == 'absolute') {
            $url = substr(str_replace(JUri::root(true), '', JUri::root(false)), 0, -1).$url;
        }
        $document->addHeadLink($url, 'canonical', 'rel');
    }
}
