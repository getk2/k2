<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewLatest extends K2View
{
    public function display($tpl = null)
    {
        $application = JFactory::getApplication();
        $params = K2HelperUtilities::getParams('com_k2');
        $document = JFactory::getDocument();
        $user = JFactory::getUser();
        $cache = JFactory::getCache('com_k2_extended');
        $limit = $params->get('latestItemsLimit');
        $limitstart = JRequest::getInt('limitstart');
        $model = $this->getModel('itemlist');
        $itemModel = $this->getModel('item');

        if ($params->get('source')) {
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
                        if ($application->getLanguageFilter()) {
                            $languageTag = JFactory::getLanguage()->getTag();
                            $languageCheck = in_array($category->language, array($languageTag, '*'));
                        }
                    } else {
                        $accessCheck = $category->access <= $user->get('aid', 0);
                    }

                    if ($category->published && $accessCheck && $languageCheck) {

                        //Merge params
                        $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
                        if ($cparams->get('inheritFrom')) {
                            $masterCategory = JTable::getInstance('K2Category', 'Table');
                            $masterCategory->load($cparams->get('inheritFrom'));
                            $cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                        }
                        $params->merge($cparams);

                        //Category image
                        $category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

                        //Category plugins
                        $dispatcher = JDispatcher::getInstance();
                        JPluginHelper::importPlugin('content');
                        $category->text = $category->description;

                        if (K2_JVERSION != '15') {
                            $dispatcher->trigger('onContentPrepare', array('com_k2.category', &$category, &$params, $limitstart));
                        } else {
                            $dispatcher->trigger('onPrepareContent', array(&$category, &$params, $limitstart));
                        }
                        $category->description = $category->text;

                        //Category K2 plugins
                        $category->event->K2CategoryDisplay = '';
                        JPluginHelper::importPlugin('k2');
                        $results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
                        $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                        $category->text = $category->description;
                        $dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
                        $category->description = $category->text;

                        //Category link
                        $link = urldecode(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias)));
                        $category->link = JRoute::_($link);
                        $category->feed = JRoute::_($link.'&format=feed');

                        JRequest::setVar('view', 'itemlist');
                        JRequest::setVar('task', 'category');
                        JRequest::setVar('id', $category->id);
                        JRequest::setVar('featured', 1);
                        JRequest::setVar('limit', $limit);
                        JRequest::setVar('clearFlag', true);

                        $category->name = htmlspecialchars($category->name, ENT_QUOTES);
                        if ($limit) {
                            $category->items = $model->getData('rdate');

                            JRequest::setVar('view', 'latest');
                            JRequest::setVar('task', '');

                            for ($i = 0; $i < sizeof($category->items); $i++) {
                                $hits = $category->items[$i]->hits;
                                $category->items[$i]->hits = 0;
                                $category->items[$i] = $cache->call(array($itemModel, 'prepareItem'), $category->items[$i], 'latest', '');
                                $category->items[$i]->hits = $hits;
                                $category->items[$i] = $itemModel->execPlugins($category->items[$i], 'latest', '');

                                //Trigger comments counter event
                                $dispatcher = JDispatcher::getInstance();
                                JPluginHelper::importPlugin('k2');
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

                        //User profile
                        $userObject->profile = $model->getUserProfile($userID);

                        //User image
                        $userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

                        //User K2 plugins
                        $userObject->event->K2UserDisplay = '';
                        if (is_object($userObject->profile) && $userObject->profile->id > 0) {
                            $dispatcher = JDispatcher::getInstance();
                            JPluginHelper::importPlugin('k2');
                            $results = $dispatcher->trigger('onK2UserDisplay', array(&$userObject->profile, &$params, $limitstart));
                            $userObject->event->K2UserDisplay = trim(implode("\n", $results));
                            $userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'UTF-8');
                        }

                        $link = K2HelperRoute::getUserRoute($userObject->id);
                        $userObject->link = JRoute::_($link);
                        $userObject->feed = JRoute::_($link.'&format=feed');
                        $userObject->name = htmlspecialchars($userObject->name, ENT_QUOTES);
                        if ($limit) {
                            $userObject->items = $model->getAuthorLatest(0, $limit, $userID);

                            for ($i = 0; $i < sizeof($userObject->items); $i++) {
                                $hits = $userObject->items[$i]->hits;
                                $userObject->items[$i]->hits = 0;
                                $userObject->items[$i] = $cache->call(array($itemModel, 'prepareItem'), $userObject->items[$i], 'latest', '');
                                $userObject->items[$i]->hits = $hits;

                                //Plugins
                                $userObject->items[$i] = $itemModel->execPlugins($userObject->items[$i], 'latest', '');

                                //Trigger comments counter event
                                $dispatcher = JDispatcher::getInstance();
                                JPluginHelper::importPlugin('k2');
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

        // Browser title
        $browserTitle = $params->get('page_title');
        if (K2_JVERSION != '15') {
            if ($application->getCfg('sitename_pagetitles', 0) == 1) {
                $browserTitle = JText::sprintf('JPAGETITLE', $application->getCfg('sitename'), $params->get('page_title'));
            } elseif ($application->getCfg('sitename_pagetitles', 0) == 2) {
                $browserTitle = JText::sprintf('JPAGETITLE', $params->get('page_title'), $application->getCfg('sitename'));
            }
        }
        $document->setTitle($browserTitle);


        // Set menu metadata for Joomla 2.5+
        if (K2_JVERSION != '15') {
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

        // Set Facebook meta data
        if ($params->get('facebookMetatags', '1')) {
            $document = JFactory::getDocument();
            $uri = JURI::getInstance();
            $document->setMetaData('og:url', $uri->toString());
            $document->setMetaData('og:title', (K2_JVERSION == '15') ? htmlspecialchars($document->getTitle(), ENT_QUOTES, 'UTF-8') : $document->getTitle());
            $document->setMetaData('og:type', 'website');
            $document->setMetaData('og:description', strip_tags($document->getDescription()));
        }

        // Lookup template folders
        $this->_addPath('template', JPATH_COMPONENT.'/templates');
        $this->_addPath('template', JPATH_COMPONENT.'/templates/default');

        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/default');

        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/default');

        $theme = $params->get('theme');
        if ($theme) {
            $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$theme);
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/'.$theme);
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/'.$theme);
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

        //Assign params
        $this->assignRef('params', $params);
        $this->assignRef('source', $source);

        //Set layout
        $this->setLayout('latest');

        //Display
        parent::display($tpl);
    }
}
