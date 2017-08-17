<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewItemlist extends K2View
{

    function display($tpl = null)
    {

        $application = JFactory::getApplication();
        $params = K2HelperUtilities::getParams('com_k2');
        $model = $this->getModel('itemlist');
        $limitstart = JRequest::getInt('limitstart');
        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');

        //Add link
        if (K2HelperPermissions::canAddItem())
            $addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
        $this->assignRef('addLink', $addLink);

        //Get data depending on task
        switch ($task)
        {

            case 'category' :
                //Get category
                $id = JRequest::getInt('id');
                JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
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
                        if ($user->guest)
                        {
                            $uri = JFactory::getURI();
                            $url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
							$application->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
                            $application->redirect(JRoute::_($url, false));
                        }
                        else
                        {
                            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                            return;
                        }
                    }
                    $languageFilter = $application->getLanguageFilter();
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
                        if ($user->guest)
                        {
                            $uri = JFactory::getURI();
                            $url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
							$application->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
                            $application->redirect(JRoute::_($url, false));
                        }
                        else
                        {
                            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                            return;
                        }
                    }
                }

                // Hide the add new item link if user cannot post in the specific category
                if (!K2HelperPermissions::canAddItem($id))
                {
                    unset($this->addLink);
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
                $category->event->K2CategoryDisplay = '';
                JPluginHelper::importPlugin('k2');
                $results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
                $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                $category->text = $category->description;
                $dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
                $category->description = $category->text;

                $this->assignRef('category', $category);
                $this->assignRef('user', $user);

                //Category children
                $ordering = $params->get('subCatOrdering');
                $children = $model->getCategoryFirstChildren($id, $ordering);
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
                        $subCategories[] = $child;
                    }
                    $this->assignRef('subCategories', $subCategories);
                }

                //Set limit
                $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

                //Set featured flag
                JRequest::setVar('featured', $params->get('catFeaturedItems'));

                //Set layout
                $this->setLayout('category');

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

                $this->assignRef('user', $userObject);

                //Set layout
                $this->setLayout('user');

                //Set limit
                $limit = $params->get('userItemCount');

                //Set title
                $title = $userObject->name;

                // Set ordering
                $ordering = $params->get('userOrdering');

                break;

            case 'tag' :
                //Set layout
                $this->setLayout('tag');

                //Set limit
                $limit = $params->get('tagItemCount');

                // Prevent spammers from using the tag view
                $tag = JRequest::getString('tag');
                $db = JFactory::getDbo();
                $db->setQuery('SELECT id, name FROM #__k2_tags WHERE name = '.$db->quote($tag));
                $tag = $db->loadObject();
                if (!$tag->id)
                {
                  JError::raiseError(404, JText::_('K2_NOT_FOUND'));
                  return false;
                }

                //set title
                $title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.$tag->name;

                // Set ordering
                $ordering = $params->get('tagOrdering');
                break;

            case 'search' :
                //Set layout
                $this->setLayout('generic');
                $tpl = JRequest::getCmd('tpl', null);

                //Set limit
                $limit = $params->get('genericItemCount');

                //Set title
                $title = JText::_('K2_SEARCH_RESULTS_FOR').' '.JRequest::getVar('searchword');
                break;

            case 'date' :
                //Set layout
                $this->setLayout('generic');

                //Set limit
                $limit = $params->get('genericItemCount');

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
                break;

            default :
                //Set layout
                $this->setLayout('category');
                $user = JFactory::getUser();
                $this->assignRef('user', $user);

                //Set limit
                $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');
                //Set featured flag
                JRequest::setVar('featured', $params->get('catFeaturedItems'));

                //Set title
                $title = $params->get('page_title');

                // Set ordering
                $ordering = $params->get('catOrdering');

                break;
        }

        //Set limit for model
        JRequest::setVar('limit', $limit);

        if (!isset($ordering))
        {
            $items = $model->getData();
        }
        else
        {
            $items = $model->getData($ordering);
        }

        //Pagination
        jimport('joomla.html.pagination');
        $total = count($items) ? $model->getTotal() : 0;
        $pagination = new JPagination($total, $limitstart, $limit);

        //Prepare items
        $user = JFactory::getUser();
        $cache = JFactory::getCache('com_k2_extended');
        $model = $this->getModel('item');
        for ($i = 0; $i < sizeof($items); $i++)
        {

            //Item group
            if ($task == "category" || $task == "")
            {
                if ($i < ($params->get('num_links') + $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items')))
                    $items[$i]->itemGroup = 'links';
                if ($i < ($params->get('num_secondary_items') + $params->get('num_leading_items') + $params->get('num_primary_items')))
                    $items[$i]->itemGroup = 'secondary';
                if ($i < ($params->get('num_primary_items') + $params->get('num_leading_items')))
                    $items[$i]->itemGroup = 'primary';
                if ($i < $params->get('num_leading_items'))
                    $items[$i]->itemGroup = 'leading';
            }

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

        }

        //Pathway
        $pathway = $application->getPathWay();
        $pathway->addItem($title);

        //Feed link
        $config = JFactory::getConfig();
        $menu = $application->getMenu();
        $default = $menu->getDefault();
        $active = $menu->getActive();
        if ($task == 'tag')
        {
            $link = K2HelperRoute::getTagRoute(JRequest::getVar('tag'));
        }
        else
        {
            $link = '';
        }
        $sef = K2_JVERSION == '30' ? $config->get('sef') : $config->getValue('config.sef');
        if (!is_null($active) && $active->id == $default->id && $sef)
        {
            $link .= '&Itemid='.$active->id.'&format=feed&limitstart=';
        }
        else
        {
            $link .= '&format=feed&limitstart=';
        }

        $feed = JRoute::_($link);
        $this->assignRef('feed', $feed);

        //Assign data
        if ($task == "category" || $task == "")
        {
            $leading = @array_slice($items, 0, $params->get('num_leading_items'));
            $primary = @array_slice($items, $params->get('num_leading_items'), $params->get('num_primary_items'));
            $secondary = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items'), $params->get('num_secondary_items'));
            $links = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items'), $params->get('num_links'));
            $this->assignRef('leading', $leading);
            $this->assignRef('primary', $primary);
            $this->assignRef('secondary', $secondary);
            $this->assignRef('links', $links);
        }
        else
        {
            $this->assignRef('items', $items);
        }

        //Set default values to avoid division by zero
        if ($params->get('num_leading_columns') == 0)
            $params->set('num_leading_columns', 1);
        if ($params->get('num_primary_columns') == 0)
            $params->set('num_primary_columns', 1);
        if ($params->get('num_secondary_columns') == 0)
            $params->set('num_secondary_columns', 1);
        if ($params->get('num_links_columns') == 0)
            $params->set('num_links_columns', 1);

        $this->assignRef('params', $params);
        $this->assignRef('pagination', $pagination);

        //Look for template files in component folders
        $this->_addPath('template', JPATH_COMPONENT.'/templates');
        $this->_addPath('template', JPATH_COMPONENT.'/templates/default');

        //Look for overrides in template folder (K2 template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/default');

        //Look for overrides in template folder (Joomla template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/default');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');

        //Look for specific K2 theme files
        if ($params->get('theme'))
        {
            $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/'.$params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/'.$params->get('theme'));
        }

        $db = JFactory::getDbo();
        $nullDate = $db->getNullDate();
        $this->assignRef('nullDate', $nullDate);

        parent::display($tpl);
    }

    function module()
    {
        jimport('joomla.application.module.helper');
        $application = JFactory::getApplication();
        $moduleID = JRequest::getInt('moduleID');
        $model = K2Model::getInstance('Itemlist', 'K2Model');
        if ($moduleID)
        {
            $result = $model->getModuleItems($moduleID);
            $items = $result->items;
            $componentParams = JComponentHelper::getParams('com_k2');
            if (is_string($result->params))
            {
                $params = class_exists('JParameter') ? new JParameter($result->params) : new JRegistry($result->params);
            }
            else
            {
                $params = $result->params;
            }

            if ($params->get('getTemplate'))
                require(JModuleHelper::getLayoutPath('mod_k2_content', $params->get('getTemplate').'/default'));
            else
                require(JModuleHelper::getLayoutPath($result->module, 'default'));
        }
        $application->close();
    }

}
