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

class K2ViewItem extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $document = JFactory::getDocument();
        $params = K2HelperUtilities::getParams('com_k2');
        $limitstart = JRequest::getInt('limitstart', 0);
        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');

        $db = JFactory::getDbo();
        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $this->setLayout('item');

        // Add link
        if (K2HelperPermissions::canAddItem()) {
            $addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
        }
        $this->assignRef('addLink', $addLink);

        // Get item model
        $model = $this->getModel();
        $item = $model->getData();

        // Check if item exists
        if (!is_object($item) || !$item->id) {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        // --- JSON Output [start] ---
        // Set the document type in Joomla 1.5
        if (K2_JVERSION == '15' && JRequest::getCmd('format') == 'json') {
            $document->setMimeEncoding('application/json');
            $document->setType('json');
        }
        if ($document->getType() == 'json') {
            // Override some display parameters to show a minimum of content elements
            $itemParams = class_exists('JParameter') ? new JParameter($item->params) : new JRegistry($item->params);
            $itemParams->set('itemIntroText', true);
            $itemParams->set('itemFullText', true);
            $itemParams->set('itemTags', true);
            $itemParams->set('itemExtraFields', true);
            $itemParams->set('itemAttachments', true);
            $itemParams->set('itemRating', true);
            $itemParams->set('itemAuthor', true);
            $itemParams->set('itemImageGallery', true);
            $itemParams->set('itemVideo', true);
            $item->params = $itemParams->toString();
        }
        // --- JSON Output [finish] ---

        // Prepare item
        $item = $model->prepareItem($item, $view, $task);
        $itemTextBeforePlugins = $item->introtext.' '.$item->fulltext;

        // Plugins
        $item = $model->execPlugins($item, $view, $task);

        // User K2 plugins
        $item->event->K2UserDisplay = '';
        if (isset($item->author) && is_object($item->author->profile) && isset($item->author->profile->id)) {
            JPluginHelper::importPlugin('k2');
            $dispatcher = JDispatcher::getInstance();
            $results = $dispatcher->trigger('onK2UserDisplay', array(
                &$item->author->profile,
                &$params,
                $limitstart
            ));
            $item->event->K2UserDisplay = trim(implode("\n", $results));
            $item->author->profile->url = htmlspecialchars($item->author->profile->url, ENT_QUOTES, 'UTF-8');
        }

        // Access check
        if ($this->getLayout() == 'form') {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        if (K2_JVERSION != '15') {
            if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($item->category->access, $user->getAuthorisedViewLevels())) {
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
        } else {
            if ($item->access > $user->get('aid', 0) || $item->category->access > $user->get('aid', 0)) {
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

        // Published check
        if (!$item->published || $item->trash) {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_up != $nullDate && $item->publish_up > $now) {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_down != $nullDate && $item->publish_down < $now) {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if (!$item->category->published || $item->category->trash) {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        // Increase hits counter
        $model->hit($item->id);

        // Set default image
        K2HelperUtilities::setDefaultImage($item, $view);

        // B/C code for reCaptcha
        if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') {
            $params->set('recaptcha', true);
            $item->params->set('recaptcha', true);
        } else {
            $params->set('recaptcha', false);
            $item->params->set('recaptcha', false);
        }

        // Comments
        if ($document->getType() != 'json') {
            $item->event->K2CommentsCounter = '';
            $item->event->K2CommentsBlock = '';
            if ($item->params->get('itemComments')) {
                // Trigger comments events
                JPluginHelper::importPlugin('k2');
                $dispatcher = JDispatcher::getInstance();
                $results = $dispatcher->trigger('onK2CommentsCounter', array(
                &$item,
                &$params,
                $limitstart
            ));
                $item->event->K2CommentsCounter = trim(implode("\n", $results));
                $results = $dispatcher->trigger('onK2CommentsBlock', array(
                &$item,
                &$params,
                $limitstart
            ));
                $item->event->K2CommentsBlock = trim(implode("\n", $results));

                // Load K2 native comments system only if there are no plugins overriding it
                if (empty($item->event->K2CommentsCounter) && empty($item->event->K2CommentsBlock)) {

                    // Load reCaptcha
                    if (!JRequest::getInt('print') && ($item->params->get('comments') == '1' || ($item->params->get('comments') == '2' && K2HelperPermissions::canAddComment($item->catid)))) {
                        if ($params->get('recaptcha') && ($user->guest || $params->get('recaptchaForRegistered', 1))) {
                            if ($params->get('recaptchaV2')) {
                                $document->addScript('https://www.google.com/recaptcha/api.js?onload=onK2RecaptchaLoaded&render=explicit');
                                $document->addScriptDeclaration('
                                    /* K2: reCaptcha v2 */
                                    function onK2RecaptchaLoaded(){
                                        grecaptcha.render("recaptcha", {
                                            "sitekey": "'.$item->params->get('recaptcha_public_key').'"
                                        });
                                    }
                                ');
                                $this->recaptchaClass = 'k2-recaptcha-v2';
                            } else {
                                $document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
                                $document->addScriptDeclaration('
                                    /* K2: reCaptcha v1 */
                                    function showRecaptcha(){
                                        Recaptcha.create("'.$item->params->get('recaptcha_public_key').'", "recaptcha", {
                                            theme: "'.$item->params->get('recaptcha_theme', 'clean').'"
                                        });
                                    }
                                    $K2(window).load(function() {
                                        showRecaptcha();
                                    });
                                ');
                                $this->recaptchaClass = 'k2-recaptcha-v1';
                            }
                        }
                    }

                    // Check for inline comment moderation
                    if (!$user->guest && $user->id == $item->created_by && $params->get('inlineCommentsModeration')) {
                        $inlineCommentsModeration = true;
                        $commentsPublished = false;
                    } else {
                        $inlineCommentsModeration = false;
                        $commentsPublished = true;
                    }
                    $this->assignRef('inlineCommentsModeration', $inlineCommentsModeration);

                    // Flag spammer link
                    $reportSpammerFlag = false;
                    if (K2_JVERSION != '15') {
                        if ($user->authorise('core.admin', 'com_k2')) {
                            $reportSpammerFlag = true;
                            $document->addScriptDeclaration('var K2Language = ["'.JText::_('K2_REPORT_USER_WARNING', true).'"];');
                        }
                    } else {
                        if ($user->gid > 24) {
                            $reportSpammerFlag = true;
                        }
                    }

                    $limit = $params->get('commentsLimit');
                    $comments = $model->getItemComments($item->id, $limitstart, $limit, $commentsPublished);

                    for ($i = 0; $i < count($comments); $i++) {
                        $comments[$i]->commentText = nl2br($comments[$i]->commentText);

                        // Convert URLs to links properly
                        $comments[$i]->commentText = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2", $comments[$i]->commentText);
                        $comments[$i]->commentText = preg_replace("/([\w]+:\/\/[\w\-?&;#~=\.\/\@]+[\w\/])/i", "<a target=\"_blank\" rel=\"nofollow\" href=\"$1\">$1</A>", $comments[$i]->commentText);
                        $comments[$i]->commentText = preg_replace("/([\w\-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i", "<a href=\"mailto:$1\">$1</A>", $comments[$i]->commentText);

                        $comments[$i]->userImage = K2HelperUtilities::getAvatar($comments[$i]->userID, $comments[$i]->commentEmail, $params->get('commenterImgWidth'));
                        if ($comments[$i]->userID > 0) {
                            $comments[$i]->userLink = K2HelperRoute::getUserRoute($comments[$i]->userID);
                        } else {
                            $comments[$i]->userLink = $comments[$i]->commentURL;
                        }
                        if ($reportSpammerFlag && $comments[$i]->userID > 0) {
                            $comments[$i]->reportUserLink = JRoute::_('index.php?option=com_k2&view=comments&task=reportSpammer&id='.$comments[$i]->userID.'&format=raw');
                        } else {
                            $comments[$i]->reportUserLink = false;
                        }
                    }

                    $item->comments = $comments;

                    if (!isset($item->numOfComments)) {
                        $item->numOfComments = 0;
                    }

                    jimport('joomla.html.pagination');
                    $total = $item->numOfComments;
                    $pagination = new JPagination($total, $limitstart, $limit);
                }
            }
        }

        // Author's latest items
        if ($item->params->get('itemAuthorLatest') && $item->created_by_alias == '') {
            $model = $this->getModel('itemlist');
            $authorLatestItems = $model->getAuthorLatest($item->id, $item->params->get('itemAuthorLatestLimit'), $item->created_by);
            if (count($authorLatestItems)) {
                for ($i = 0; $i < count($authorLatestItems); $i++) {
                    $authorLatestItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($authorLatestItems[$i]->id.':'.urlencode($authorLatestItems[$i]->alias), $authorLatestItems[$i]->catid.':'.urlencode($authorLatestItems[$i]->categoryalias))));
                }
                $this->assignRef('authorLatestItems', $authorLatestItems);
            }
        }

        // Related items
        if ($item->params->get('itemRelated') && isset($item->tags) && count($item->tags)) {
            $model = $this->getModel('itemlist');
            $relatedItems = $model->getRelatedItems($item->id, $item->tags, $item->params);
            if (count($relatedItems)) {
                for ($i = 0; $i < count($relatedItems); $i++) {
                    $relatedItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($relatedItems[$i]->id.':'.urlencode($relatedItems[$i]->alias), $relatedItems[$i]->catid.':'.urlencode($relatedItems[$i]->categoryalias))));
                }
                $this->assignRef('relatedItems', $relatedItems);
            }
        }

        // Navigation (previous and next item)
        if ($item->params->get('itemNavigation')) {
            $model = $this->getModel('item');

            $nextItem = $model->getNextItem($item->id, $item->catid, $item->ordering);
            if (!is_null($nextItem)) {
                $item->nextLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($nextItem->id.':'.urlencode($nextItem->alias), $nextItem->catid.':'.urlencode($item->category->alias))));
                $item->nextTitle = $nextItem->title;

                $date = JFactory::getDate($item->modified);
                $timestamp = '?t='.$date->toUnix();

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_XS.jpg')) {
                    $item->nextImageXSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_XS.jpg'.$timestamp;
                }
                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_S.jpg')) {
                    $item->nextImageSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_S.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_M.jpg')) {
                    $item->nextImageMedium = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_M.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_L.jpg')) {
                    $item->nextImageLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_L.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_XL.jpg')) {
                    $item->nextImageXLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_XL.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_Generic.jpg')) {
                    $item->nextImageGeneric = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$nextItem->id).'_Generic.jpg'.$timestamp;
                }
            }

            $previousItem = $model->getPreviousItem($item->id, $item->catid, $item->ordering);
            if (!is_null($previousItem)) {
                $item->previousLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($previousItem->id.':'.urlencode($previousItem->alias), $previousItem->catid.':'.urlencode($item->category->alias))));
                $item->previousTitle = $previousItem->title;

                $date = JFactory::getDate($item->modified);
                $timestamp = '?t='.$date->toUnix();

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_XS.jpg')) {
                    $item->previousImageXSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_XS.jpg'.$timestamp;
                }
                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_S.jpg')) {
                    $item->previousImageSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_S.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_M.jpg')) {
                    $item->previousImageMedium = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_M.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_L.jpg')) {
                    $item->previousImageLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_L.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_XL.jpg')) {
                    $item->previousImageXLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_XL.jpg'.$timestamp;
                }

                if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_Generic.jpg')) {
                    $item->previousImageGeneric = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$previousItem->id).'_Generic.jpg'.$timestamp;
                }
            }
        }

        // Absolute URL
        $uri = JURI::getInstance();
        $item->absoluteURL = $uri->toString();

        // Get the frontend's language for use in social media buttons - use explicit variable references for future update flexibility
        $getSiteLanguage = JFactory::getLanguage();
        $languageTag = $getSiteLanguage->getTag();
        $item->langTagForFB = str_replace('-', '_', $languageTag);
        $item->langTagForTW = strtolower($languageTag);
        $item->langTagForLI = str_replace('-', '_', $languageTag);

        // Set the link for sharing
        $item->sharinglink = $item->absoluteURL;

        // --- B/C stuff [start] ---
        // Social Share URL
        $item->socialLink = urlencode($item->absoluteURL);

        // Twitter link (legacy code)
        if ($params->get('twitterUsername')) {
            $item->twitterURL = 'https://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL).'&amp;via='.$params->get('twitterUsername');
        } else {
            $item->twitterURL = 'https://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL);
        }

        // Deprecate Google+ sharing
        $params->set('itemGooglePlusOneButton', 0);
        $item->params->set('itemGooglePlusOneButton', 0);
        $item->langTagForGP = '';
        // --- B/C stuff [end] ---

        // Email link
        if (K2_JVERSION != '15') {
            require_once(JPATH_SITE.'/components/com_mailto/helpers/mailto.php');
            $template = $app->getTemplate();
            $item->emailLink = JRoute::_('index.php?option=com_mailto&tmpl=component&template='.$template.'&link='.MailToHelper::addLink($item->absoluteURL));
        } else {
            require_once(JPATH_SITE.'/components/com_mailto/helpers/mailto.php');
            $item->emailLink = JRoute::_('index.php?option=com_mailto&tmpl=component&link='.MailToHelper::addLink($item->absoluteURL));
        }

        // Get current menu item
        $menus = $app->getMenu();
        $menu = $menus->getActive();

        // Check if the current menu item matches the displayed K2 item
        $menuItemMatchesK2Item = false;
        if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'item' && isset($menu->query['id']) && $menu->query['id'] == $item->id) {
            $menuItemMatchesK2Item = true;
        }

        // Set pathway
        $pathway = $app->getPathWay();
        if ($menu) {
            if (isset($menu->query['view']) && ($menu->query['view'] != 'item' || $menu->query['id'] != $item->id)) {
                if (!isset($menu->query['task']) || $menu->query['task'] != 'category' || $menu->query['id'] != $item->catid) {
                    $pathway->addItem($item->category->name, $item->category->link);
                }
                $pathway->addItem($item->cleanTitle, '');
            }
        }

        // --- JSON Output [start] ---
        if ($document->getType() == 'json') {
            // Build the output object
            $row = $model->prepareJSONItem($item);

            // Output
            $response = new stdClass();

            // Site
            $response->site = new stdClass();
            $uri = JURI::getInstance();
            $response->site->url = $uri->toString(array('scheme', 'host', 'port'));

            $config = JFactory::getConfig();
            $response->site->name = K2_JVERSION == '30' ? $config->get('sitename') : $config->getValue('config.sitename');

            $response->item = $row;

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

        // Head Stuff
        if (!in_array($document->getType(), ['raw', 'json'])) {
            // Set canonical link
            $canonicalURL = $params->get('canonicalURL', 'relative');
            if ($canonicalURL == 'absolute') {
                $document->addHeadLink(substr(str_replace(JUri::root(true), '', JUri::root(false)), 0, -1).$item->link, 'canonical', 'rel');
            }
            if ($canonicalURL == 'relative') {
                $document->addHeadLink($item->link, 'canonical', 'rel');
            }

            // Set page title
            if ($menuItemMatchesK2Item) {
                if (is_string($menu->params)) {
                    $menu_params = K2_JVERSION == '15' ? new JParameter($menu->params) : new JRegistry($menu->params);
                } else {
                    $menu_params = $menu->params;
                }
                if (!$menu_params->get('page_title')) {
                    $params->set('page_title', $item->cleanTitle);
                }
            } else {
                $params->set('page_title', $item->cleanTitle);
            }
            if (K2_JVERSION != '15') {
                if ($app->getCfg('sitename_pagetitles', 0) == 1) {
                    $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $params->get('page_title'));
                    $params->set('page_title', $title);
                } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
                    $title = JText::sprintf('JPAGETITLE', $params->get('page_title'), $app->getCfg('sitename'));
                    $params->set('page_title', $title);
                }
            }
            $document->setTitle($params->get('page_title'));

            // Set metadata
            $metaDesc = '';

            // Get metadata from the menu item (for Joomla 2.5+)
            if ($menuItemMatchesK2Item) {
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
            }

            // --- Override metadata with data from the item ---
            // Meta: Description
            if ($item->metadesc) {
                $metaDesc = filter_var($item->metadesc, FILTER_SANITIZE_STRING);
            } else {
                $metaDesc = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $itemTextBeforePlugins);
                $metaDesc = filter_var($metaDesc, FILTER_SANITIZE_STRING);
                $metaDesc = K2HelperUtilities::characterLimit($metaDesc, $params->get('metaDescLimit', 150));
            }
            if ($metaDesc) {
                $document->setDescription($metaDesc);
            }

            // Meta: Keywords
            if ($item->metakey) {
                $document->setMetadata('keywords', $item->metakey);
            } else {
                if (isset($item->tags) && count($item->tags)) {
                    $tmp = array();
                    foreach ($item->tags as $tag) {
                        $tmp[] = $tag->name;
                    }
                    $document->setMetadata('keywords', implode(',', $tmp));
                }
            }

            // Meta: Robots & author
            if ($app->getCfg('MetaAuthor') == '1' && isset($item->author->name)) {
                $document->setMetadata('author', $item->author->name);
            }

            $itemMetaData = class_exists('JParameter') ? new JParameter($item->metadata) : new JRegistry($item->metadata);
            $itemMetaData = $itemMetaData->toArray();
            foreach ($itemMetaData as $k => $v) {
                if (($k == 'robots' || $k == 'author') && $v) {
                    $document->setMetadata($k, $v);
                }
            }

            // Common for social meta tags
            if ($item->metadesc) {
                $socialMetaDesc = $item->metadesc;
            } else {
                $socialMetaDesc = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $itemTextBeforePlugins);
                $socialMetaDesc = filter_var($socialMetaDesc, FILTER_SANITIZE_STRING);
            }

            // Set Facebook meta tags
            if ($params->get('facebookMetatags', 1)) {
                $document->setMetaData('og:url', $item->absoluteURL);
                $document->setMetaData('og:type', 'article');
                $document->setMetaData('og:title', filter_var($item->title, FILTER_SANITIZE_STRING));
                $document->setMetaData('og:description', K2HelperUtilities::characterLimit($socialMetaDesc, 300)); // 300 chars limit for Facebook post sharing
                $facebookImage = 'image'.$params->get('facebookImage', 'Medium');
                if ($item->$facebookImage) {
                    $basename = basename($item->$facebookImage);
                    if (strpos($basename, '?t=')!==false) {
                        $tmpBasename = explode('?t=', $basename);
                        $basenameWithNoTimestamp = $tmpBasename[0];
                    } else {
                        $basenameWithNoTimestamp = $basename;
                    }
                    if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$basenameWithNoTimestamp)) {
                        $image = JURI::root().'media/k2/items/cache/'.$basename;
                        $document->setMetaData('og:image', $image);
                        $document->setMetaData('image', $image); // Generic meta
                    }
                }
            }

            // Set Twitter meta tags
            if ($params->get('twitterMetatags', 1)) {
                $document->setMetaData('twitter:card', 'summary');
                if ($params->get('twitterUsername')) {
                    $document->setMetaData('twitter:site', '@'.$params->get('twitterUsername'));
                }
                $document->setMetaData('twitter:title', filter_var($item->title, FILTER_SANITIZE_STRING));
                $document->setMetaData('twitter:description', K2HelperUtilities::characterLimit($socialMetaDesc, 200)); // 200 chars limit for Twitter post sharing
                $twitterImage = 'image'.$params->get('twitterImage', 'Medium');
                if ($item->$twitterImage) {
                    $basename = basename($item->$twitterImage);
                    if (strpos($basename, '?t=')!==false) {
                        $tmpBasename = explode('?t=', $basename);
                        $basenameWithNoTimestamp = $tmpBasename[0];
                    } else {
                        $basenameWithNoTimestamp = $basename;
                    }
                    if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$basenameWithNoTimestamp)) {
                        $image = JURI::root().'media/k2/items/cache/'.$basename;
                        $document->setMetaData('twitter:image', $image);
                        if (!$params->get('facebookMetatags')) {
                            $document->setMetaData('image', $image); // Generic meta
                        }
                        $document->setMetaData('twitter:image:alt', (!empty($item->image_caption)) ? filter_var($item->image_caption, FILTER_SANITIZE_STRING) : filter_var($item->title, FILTER_SANITIZE_STRING));
                    }
                }
            }
        }

        if ($document->getType() != 'json') {
            // Lookup template folders
            $this->_addPath('template', JPATH_COMPONENT.'/templates');
            $this->_addPath('template', JPATH_COMPONENT.'/templates/default');

            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates');
            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates/default');

            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2');
            $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/default');

            if ($item->params->get('theme')) {
                $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$item->params->get('theme'));
                $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/templates/'.$item->params->get('theme'));
                $this->_addPath('template', JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_k2/'.$item->params->get('theme'));
            }

            // Allow temporary template loading with ?template=
            $template = JRequest::getCmd('template');
            if (isset($template)) {
                // Look for overrides in template folder (new K2 template structure)
                $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2');
                $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2/default');
                if ($item->params->get('theme')) {
                    $this->_addPath('template', JPATH_SITE.'/templates/'.$template.'/html/com_k2/'.$item->params->get('theme'));
                }
            }

            // Assign data
            $this->assignRef('item', $item);
            $this->assignRef('user', $user);
            $this->assignRef('params', $item->params);
            $this->assignRef('pagination', $pagination);

            // Display
            parent::display($tpl);
        }
    }
}
