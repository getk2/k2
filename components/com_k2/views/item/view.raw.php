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

class K2ViewItem extends K2View
{

    function display($tpl = null)
    {
        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        $document = JFactory::getDocument();
        $params = K2HelperUtilities::getParams('com_k2');
        $limitstart = JRequest::getInt('limitstart', 0);
        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');

        $db = JFactory::getDbo();
        $jnow = JFactory::getDate();
        $now =  K2_JVERSION == '15'?$jnow->toMySQL():$jnow->toSql();
        $nullDate = $db->getNullDate();

        $this->setLayout('item');

        // Add link
        if (K2HelperPermissions::canAddItem())
            $addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
        $this->assignRef('addLink', $addLink);

        // Get item
        $model = $this->getModel();
        $item = $model->getData();
        $item->event = new stdClass;

        // Does the item exists?
        if (!is_object($item) || !$item->id)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        // Prepare item
        $item = $model->prepareItem($item, $view, $task);

        // Plugins
        $item = $model->execPlugins($item, $view, $task);
        
        // User K2 plugins
        $item->event->K2UserDisplay = '';
        if (isset($item->author) && is_object($item->author->profile) && isset($item->author->profile->id))
        {
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('k2');
            $results = $dispatcher->trigger('onK2UserDisplay', array(&$item->author->profile, &$params, $limitstart));
            $item->event->K2UserDisplay = trim(implode("\n", $results));
            $item->author->profile->url = htmlspecialchars($item->author->profile->url, ENT_QUOTES, 'UTF-8');
        }

        // Access check
        if ($this->getLayout() == 'form')
        {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        if (K2_JVERSION != '15')
        {
            if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($item->category->access, $user->getAuthorisedViewLevels()))
            {
                if ($user->guest)
                {
                    $uri = JFactory::getURI();
                    $url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());
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
        else
        {
            if ($item->access > $user->get('aid', 0) || $item->category->access > $user->get('aid', 0))
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

        // Published check
        if (!$item->published || $item->trash)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_up != $nullDate && $item->publish_up > $now)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_down != $nullDate && $item->publish_down < $now)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if (!$item->category->published || $item->category->trash)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        // Increase hits counter
        $model->hit($item->id);

        // Set default image
        K2HelperUtilities::setDefaultImage($item, $view);
        
        // Comments
        $item->event->K2CommentsCounter = '';
        $item->event->K2CommentsBlock = '';
        if ($item->params->get('itemComments'))
        {

            // Trigger comments events
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('k2');
            $results = $dispatcher->trigger('onK2CommentsCounter', array(&$item, &$params, $limitstart));
            $item->event->K2CommentsCounter = trim(implode("\n", $results));
            $results = $dispatcher->trigger('onK2CommentsBlock', array(&$item, &$params, $limitstart));
            $item->event->K2CommentsBlock = trim(implode("\n", $results));

            // Load K2 native comments system only if there are no plugins overriding it
            if (empty($item->event->K2CommentsCounter) && empty($item->event->K2CommentsBlock))
            {

                $limit = $params->get('commentsLimit');
                $comments = $model->getItemComments($item->id, $limitstart, $limit);
                $pattern = "@\b(https?://)?(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@";

                for ($i = 0; $i < sizeof($comments); $i++)
                {

                    $comments[$i]->commentText = nl2br($comments[$i]->commentText);
                    $comments[$i]->commentText = preg_replace($pattern, '<a target="_blank" rel="nofollow" href="\0">\0</a>', $comments[$i]->commentText);
                    $comments[$i]->userImage = K2HelperUtilities::getAvatar($comments[$i]->userID, $comments[$i]->commentEmail, $params->get('commenterImgWidth'));
                    if ($comments[$i]->userID > 0)
                        $comments[$i]->userLink = K2HelperRoute::getUserRoute($comments[$i]->userID);
                    else
                        $comments[$i]->userLink = $comments[$i]->commentURL;
                }

                $item->comments = $comments;

                jimport('joomla.html.pagination');
                $total = $item->numOfComments;
                $pagination = new JPagination($total, $limitstart, $limit);
            }

        }

        // Author's latest items
        if ($item->params->get('itemAuthorLatest') && $item->created_by_alias == '')
        {
            $model = $this->getModel('itemlist');
            $authorLatestItems = $model->getAuthorLatest($item->id, $item->params->get('itemAuthorLatestLimit'), $item->created_by);
            if (count($authorLatestItems))
            {
                for ($i = 0; $i < sizeof($authorLatestItems); $i++)
                {
                    $authorLatestItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($authorLatestItems[$i]->id.':'.urlencode($authorLatestItems[$i]->alias), $authorLatestItems[$i]->catid.':'.urlencode($authorLatestItems[$i]->categoryalias))));
                }
                $this->assignRef('authorLatestItems', $authorLatestItems);
            }
        }

        // Related items
        if ($item->params->get('itemRelated') && isset($item->tags) && count($item->tags))
        {
            $model = $this->getModel('itemlist');
            $relatedItems = $model->getRelatedItems($item->id, $item->tags, $item->params);
            if (count($relatedItems))
            {
                for ($i = 0; $i < sizeof($relatedItems); $i++)
                {
                    $relatedItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($relatedItems[$i]->id.':'.urlencode($relatedItems[$i]->alias), $relatedItems[$i]->catid.':'.urlencode($relatedItems[$i]->categoryalias))));
                }
                $this->assignRef('relatedItems', $relatedItems);
            }

        }

        // Navigation (previous and next item)
        if ($item->params->get('itemNavigation'))
        {
            $model = $this->getModel('item');

            $nextItem = $model->getNextItem($item->id, $item->catid, $item->ordering);
            if (!is_null($nextItem))
            {
                $item->nextLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($nextItem->id.':'.urlencode($nextItem->alias), $nextItem->catid.':'.urlencode($item->category->alias))));
                $item->nextTitle = $nextItem->title;
            }

            $previousItem = $model->getPreviousItem($item->id, $item->catid, $item->ordering);
            if (!is_null($previousItem))
            {
                $item->previousLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($previousItem->id.':'.urlencode($previousItem->alias), $previousItem->catid.':'.urlencode($item->category->alias))));
                $item->previousTitle = $previousItem->title;
            }

        }

        // Absolute URL
        $uri = JURI::getInstance();
        $item->absoluteURL = $uri->toString();

        // Email link
        if (K2_JVERSION != '15')
        {
            require_once(JPATH_SITE.'/components/com_mailto/helpers/mailto.php');
            $template = $application->getTemplate();
            $item->emailLink = JRoute::_('index.php?option=com_mailto&tmpl=component&template='.$template.'&link='.MailToHelper::addLink($item->absoluteURL));
        }
        else
        {
            require_once(JPATH_SITE.'/components/com_mailto/helpers/mailto.php');
            $item->emailLink = JRoute::_('index.php?option=com_mailto&tmpl=component&link='.MailToHelper::addLink($item->absoluteURL));
        }

        // Twitter link (legacy code)
        if ($params->get('twitterUsername'))
        {
            $item->twitterURL = 'http://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL).'&amp;via='.$params->get('twitterUsername');
        }
        else
        {
            $item->twitterURL = 'http://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL);
        }

        // Social link
        $item->socialLink = urlencode($item->absoluteURL);

        // Look for template files in component folders
        $this->_addPath('template', JPATH_COMPONENT.'/templates');
        $this->_addPath('template', JPATH_COMPONENT.'/templates/default');

        // Look for overrides in template folder (K2 template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/default');

        // Look for overrides in template folder (Joomla template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/default');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');

        // Look for specific K2 theme files
        if ($item->params->get('theme'))
        {
            $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$item->params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates/'.$item->params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/'.$item->params->get('theme'));
        }

        // Assign data
        $this->assignRef('item', $item);
        $this->assignRef('user', $user);
        $this->assignRef('params', $item->params);
        $this->assignRef('pagination', $pagination);

        parent::display($tpl);
    }

}
