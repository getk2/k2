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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

class K2ModelItem extends K2Model
{
	function getData()
	{
		$application = JFactory::getApplication();
		$id = JRequest::getInt('id');
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_items WHERE id={$id}";
		if (K2_JVERSION != '15')
		{
			$languageFilter = $application->getLanguageFilter();
			if ($languageFilter)
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).",".$db->Quote('*').")";
			}
		}
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();
		return $row;
	}

	function prepareItem($item, $view, $task)
	{
		jimport('joomla.filesystem.file');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
		$limitstart = JRequest::getInt('limitstart');
		$application = JFactory::getApplication();
		//Initialize params
		if ($view != 'item')
		{
			if (K2_JVERSION == '30')
			{
				$params = $application->getParams('com_k2');
			}
			else
			{
				$component = JComponentHelper::getComponent('com_k2');
				$params = class_exists('JParameter') ? new JParameter($component->params) : new JRegistry($component->params);
				$itemid = JRequest::getInt('Itemid');
				if ($itemid)
				{
					$menu = $application->getMenu();
					$menuparams = $menu->getParams($itemid);
					$params->merge($menuparams);
				}
			}
		}
		else
		{
			$params = K2HelperUtilities::getParams('com_k2');
		}

		//Category
		$db = JFactory::getDbo();
		$category = JTable::getInstance('K2Category', 'Table');
		$category->load($item->catid);

		$item->category = $category;
		$item->category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));

		//Read more link
		$link = K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->category->alias));
		$item->link = urldecode(JRoute::_($link));

		//Print link
		$item->printLink = urldecode(JRoute::_($link.'&tmpl=component&print=1'));

		//Params
		$cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
		$iparams = class_exists('JParameter') ? new JParameter($item->params) : new JRegistry($item->params);
		$item->params = version_compare(PHP_VERSION, '5.0.0', '>=') ? clone $params : $params;

		if ($cparams->get('inheritFrom'))
		{
			$masterCategoryID = $cparams->get('inheritFrom');
			$masterCategory = JTable::getInstance('K2Category', 'Table');
			$masterCategory->load((int)$masterCategoryID);
			$cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
		}
		$item->params->merge($cparams);
		$item->params->merge($iparams);

		// Edit link
		if (K2HelperPermissions::canEditItem($item->created_by, $item->catid))
			$item->editLink = JRoute::_('index.php?option=com_k2&view=item&task=edit&cid='.$item->id.'&tmpl=component');

		// Tags
		if (($view == 'item' && ($item->params->get('itemTags') || $item->params->get('itemRelated'))) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemTags')) || ($view == 'itemlist' && $task == 'user' && $item->params->get('userItemTags')) || ($view == 'latest' && $params->get('latestItemTags')))
		{
			$tags = $this->getItemTags($item->id);
			for ($i = 0; $i < sizeof($tags); $i++)
			{
				$tags[$i]->link = JRoute::_(K2HelperRoute::getTagRoute($tags[$i]->name));
			}
			$item->tags = $tags;
		}

		// Image
		$item->imageXSmall = '';
		$item->imageSmall = '';
		$item->imageMedium = '';
		$item->imageLarge = '';
		$item->imageXLarge = '';

		$date = JFactory::getDate($item->modified);
		$timestamp = '?t='.$date->toUnix();

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg'))
		{
			$item->imageXSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageXSmall .= $timestamp;
			}
		}

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg'))
		{
			$item->imageSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageSmall .= $timestamp;
			}
		}

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg'))
		{
			$item->imageMedium = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageMedium .= $timestamp;
			}
		}

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg'))
		{
			$item->imageLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageLarge .= $timestamp;
			}
		}

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg'))
		{
			$item->imageXLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageXLarge .= $timestamp;
			}
		}

		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg'))
		{
			$item->imageGeneric = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
			if ($params->get('imageTimestamp'))
			{
				$item->imageGeneric .= $timestamp;
			}
		}

		// Extra fields
		if ((($view == 'item' || $view == 'relatedByTag') && $item->params->get('itemExtraFields')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemExtraFields')) || ($view == 'itemlist' && $task == 'tag' && $item->params->get('tagItemExtraFields')) || ($view == 'itemlist' && ($task == 'search' || $task == 'date') && $item->params->get('genericItemExtraFields')))
		{
			$item->extra_fields = $this->getItemExtraFields($item->extra_fields, $item);
		}

		// Attachments
		if (($view == 'item' && $item->params->get('itemAttachments')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemAttachments')))
		{
			$item->attachments = $this->getItemAttachments($item->id);
		}

		// Rating
		if (($view == 'item' && $item->params->get('itemRating')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemRating')))
		{
			$item->votingPercentage = $this->getVotesPercentage($item->id);
			$item->numOfvotes = $this->getVotesNum($item->id);

		}

		// Filtering
		if ($params->get('introTextCleanup'))
		{
			$filterTags = preg_split('#[,\s]+#', trim($params->get('introTextCleanupExcludeTags')));
			$filterAttrs = preg_split('#[,\s]+#', trim($params->get('introTextCleanupTagAttr')));
			$filterAttrs = array_filter($filterAttrs);
			$item->introtext = K2HelperUtilities::cleanTags($item->introtext, $filterTags);
			if (count($filterAttrs))
			{
				$item->introtext = K2HelperUtilities::cleanAttributes($item->introtext, $filterTags, $filterAttrs);
			}
		}

		if ($params->get('fullTextCleanup'))
		{
			$filterTags = preg_split('#[,\s]+#', trim($params->get('fullTextCleanupExcludeTags')));
			$filterAttrs = preg_split('#[,\s]+#', trim($params->get('fullTextCleanupTagAttr')));
			$filterAttrs = array_filter($filterAttrs);
			$item->fulltext = K2HelperUtilities::cleanTags($item->fulltext, $filterTags);
			if (count($filterAttrs))
			{
				$item->fulltext = K2HelperUtilities::cleanAttributes($item->fulltext, $filterTags, $filterAttrs);
			}
		}

		if ($item->params->get('catItemIntroTextWordLimit') && $task == 'category')
		{
			$item->introtext = K2HelperUtilities::wordLimit($item->introtext, $item->params->get('catItemIntroTextWordLimit'));
		}

		$item->cleanTitle = $item->title;
		$item->title = htmlspecialchars($item->title, ENT_QUOTES);
		$item->image_caption = htmlspecialchars($item->image_caption, ENT_QUOTES);

		// Author
		$metaAuthor = K2_JVERSION != '15' && $application->getCfg('MetaAuthor');
		if ($metaAuthor || ($view == 'item' && ($item->params->get('itemAuthorBlock') || $item->params->get('itemAuthor'))) ||  ($view == 'itemlist' && ($task == '' || $task == 'category') && ($item->params->get('catItemAuthorBlock') || $item->params->get('catItemAuthor'))) || ($view == 'itemlist' && $task == 'user') || ($view == 'relatedByTag'))
		{
			if (!empty($item->created_by_alias))
			{
				$item->author = new stdClass;
				$item->author->name = $item->created_by_alias;
				$item->author->avatar = K2HelperUtilities::getAvatar('alias');
				$item->author->link = JURI::root();
			}
			else
			{
				$author = JFactory::getUser($item->created_by);
				$item->author = $author;
				$item->author->link = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
				$item->author->profile = $this->getUserProfile($item->created_by);
				$item->author->avatar = K2HelperUtilities::getAvatar($author->id, $author->email, $params->get('userImageWidth'));
			}

			if (!isset($item->author->profile) || is_null($item->author->profile))
			{

				$item->author->profile = new JObject;
				$item->author->profile->gender = NULL;

			}

		}

		// Num of comments
		if ($params->get('comments', 0) > 0)
		{
			$user = JFactory::getUser();
			if (!$user->guest && $user->id == $item->created_by && $params->get('inlineCommentsModeration'))
			{
				$item->numOfComments = $this->countItemComments($item->id, false);
			}
			else
			{
				$item->numOfComments = $this->countItemComments($item->id);
			}
		}

		return $item;
	}

	function prepareFeedItem(&$item)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
		$params = K2HelperUtilities::getParams('com_k2');
		$limitstart = 0;
		$view = JRequest::getCmd('view');
		// Category
		$category = JTable::getInstance('K2Category', 'Table');
		$category->load($item->catid);
		$item->category = $category;

		// Read more link
		$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.$item->alias, $item->catid.':'.urlencode($item->category->alias))));

		// Filtering
		if ($params->get('introTextCleanup'))
		{
			$filterTags = preg_split('#[,\s]+#', trim($params->get('introTextCleanupExcludeTags')));
			$filterAttrs = preg_split('#[,\s]+#', trim($params->get('introTextCleanupTagAttr')));
			$filter = new JFilterInput($filterTags, $filterAttrs, 0, 1);
			$item->introtext = $filter->clean($item->introtext);
		}

		if ($params->get('fullTextCleanup'))
		{
			$filterTags = preg_split('#[,\s]+#', trim($params->get('fullTextCleanupExcludeTags')));
			$filterAttrs = preg_split('#[,\s]+#', trim($params->get('fullTextCleanupTagAttr')));
			$filter = new JFilterInput($filterTags, $filterAttrs, 0, 1);
			$item->fulltext = $filter->clean($item->fulltext);
		}

		// Description
		$item->description = '';

		// Item image
		if ($params->get('feedItemImage') && JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_'.$params->get('feedImgSize').'.jpg'))
		{
			$altText = $item->image_caption ? $item->image_caption : $item->title;
			$item->description .= '<div class="K2FeedImage"><img src="'.JURI::root().'media/k2/items/cache/'.md5('Image'.$item->id).'_'.$params->get('feedImgSize').'.jpg" alt="'.$altText.'" /></div>';
		}

		// Item Introtext
		if ($params->get('feedItemIntroText'))
		{
			// Introtext word limit
			if ($params->get('feedTextWordLimit') && $item->introtext)
			{
				$item->introtext = K2HelperUtilities::wordLimit($item->introtext, $params->get('feedTextWordLimit'));
			}
			$item->description .= '<div class="K2FeedIntroText">'.$item->introtext.'</div>';
		}

		// Item Fulltext
		if ($params->get('feedItemFullText') && $item->fulltext)
		{
			$item->description .= '<div class="K2FeedFullText">'.$item->fulltext.'</div>';
		}

		// Item Tags
		if ($params->get('feedItemTags'))
		{
			$tags = $this->getItemTags($item->id);
			if (count($tags))
			{
				$item->description .= '<div class="K2FeedTags"><ul>';
				foreach ($tags as $tag)
				{
					$item->description .= '<li>'.$tag->name.'</li>';
				}
				$item->description .= '<ul></div>';
			}
		}

		// Item Video
		if ($params->get('feedItemVideo') && $item->video)
		{
			if (!empty($item->video) && JString::substr($item->video, 0, 1) !== '{')
			{
				$item->description .= '<div class="K2FeedVideo">'.$item->video.'</div>';
			}
			else
			{
				$params->set('vfolder', 'media/k2/videos');
				$params->set('afolder', 'media/k2/audio');
				if (JString::strpos($item->video, 'remote}'))
				{
					preg_match("#}(.*?){/#s", $item->video, $matches);
					if (!JString::strpos($matches[1], 'http://}'))
						$item->video = str_replace($matches[1], JURI::root().$matches[1], $item->video);
				}
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$item->text = $item->video;
				if (K2_JVERSION == '15')
				{
					$dispatcher->trigger('onPrepareContent', array(
						&$item,
						&$params,
						$limitstart
					));
				}
				else
				{
					$dispatcher->trigger('onContentPrepare', array(
						'com_k2.'.$view,
						&$item,
						&$params,
						$limitstart
					));
				}                $item->description .= '<div class="K2FeedVideo">'.$item->text.'</div>';
			}
		}

		// Item gallery
		if ($params->get('feedItemGallery') && $item->gallery)
		{
			$params->set('galleries_rootfolder', 'media/k2/galleries');
			$params->set('enabledownload', '0');
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$item->text = $item->gallery;
			if (K2_JVERSION == '15')
			{
				$dispatcher->trigger('onPrepareContent', array(
					&$item,
					&$params,
					$limitstart
				));
			}
			else
			{
				$dispatcher->trigger('onContentPrepare', array(
					'com_k2.'.$view,
					&$item,
					&$params,
					$limitstart
				));
			}
			$item->description .= '<div class="K2FeedGallery">'.$item->text.'</div>';
		}

		// Item attachments
		if ($params->get('feedItemAttachments'))
		{
			$attachments = $this->getItemAttachments($item->id);
			if (count($attachments))
			{
				$item->description .= '<div class="K2FeedAttachments"><ul>';
				foreach ($attachments as $attachment)
				{
					$item->description .= '<li><a title="'.htmlentities($attachment->titleAttribute, ENT_QUOTES, 'UTF-8').'" href="'.$attachment->link.'">'.$attachment->title.'</a></li>';
				}
				$item->description .= '<ul></div>';
			}
		}

		// Author
		if (!empty($item->created_by_alias))
		{
			if(!isset($item->author))
			{
				$item->author = new stdClass;
			}
			$item->author->name = $item->created_by_alias;
			$item->author->email = '';
		}
		else
		{
			$author = JFactory::getUser($item->created_by);
			$item->author = $author;
			$item->author->link = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
			$item->author->profile = $this->getUserProfile($item->created_by);
		}

		return $item;
	}

	function prepareJSONItem($item)
	{
		$row = new JObject();
		unset($row->_errors);
		$row->id = $item->id;
		$row->title = $item->title;
		$row->alias = $item->alias;
		$row->link = $item->link;
		$row->catid = $item->catid;
		$row->introtext = $item->introtext;
		$row->fulltext = $item->fulltext;
		$row->extra_fields = $item->extra_fields;
		$row->created = $item->created;
		//$row->created_by = $item->created_by;
		$row->created_by_alias = $item->created_by_alias;
		$row->modified = $item->modified;
		//$row->modified_by = $item->modified_by;
		$row->featured = $item->featured;
		//$row->ordering = $item->ordering;
		//$row->featured_ordering = $item->featured_ordering;
		$row->image = isset($item->image) ? $item->image : '';
		$row->imageWidth = isset($item->imageWidth) ? $item->imageWidth : '';
		$row->image_caption = $item->image_caption;
		$row->image_credits = $item->image_credits;
		$row->imageXSmall = $item->imageXSmall;
		$row->imageSmall = $item->imageSmall;
		$row->imageMedium = $item->imageMedium;
		$row->imageLarge = $item->imageLarge;
		$row->imageXLarge = $item->imageXLarge;
		$row->video = $item->video;
		$row->video_caption = $item->video_caption;
		$row->video_credits = $item->video_credits;
		$row->gallery = $item->gallery;
		$row->hits = $item->hits;
		//$row->plugins = $item->plugins;
		$row->category = new stdClass;
		$row->category->id = $item->category->id;
		$row->category->name = $item->category->name;
		$row->category->alias = $item->category->alias;
		$row->category->link = $item->category->link;
		$row->category->description = $item->category->description;
		$row->category->image = $item->category->image;
		$row->category->ordering = $item->category->ordering;
		//$row->category->plugins = $item->category->plugins;
		$row->tags = isset($item->tags) ? $item->tags : array();
		$row->attachments = isset($item->attachments) ? $item->attachments : array();
		$row->votingPercentage = isset($item->votingPercentage) ? $item->votingPercentage : '';
		$row->numOfvotes = isset($item->numOfvotes) ? $item->numOfvotes : '';
		if (isset($item->author))
		{
			$row->author = new stdClass;
			//$row->author->id = $item->author->id;
			$row->author->name = $item->author->name;
			//$row->author->username = $item->author->username;
			$row->author->link = $item->author->link;
			$row->author->avatar = $item->author->avatar;
			if (isset($item->author->profile))
			{
				unset($item->author->profile->plugins);
			}
			$row->author->profile = $item->author->profile;
			if (isset($row->author->profile->url))
			{
				$row->author->profile->url = htmlspecialchars($row->author->profile->url, ENT_QUOTES, 'UTF-8');
			}
		}
		$row->numOfComments = $item->numOfComments;
		$row->events = $item->event;
		$row->language = $item->language;
		return $row;
	}

	function execPlugins($item, $view, $task)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$params = K2HelperUtilities::getParams('com_k2');
		$limitstart = JRequest::getInt('limitstart');
		// Import plugins
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

		if (!isset($this->isSigInstalled))
		{
			$this->isSigInstalled = (
			JFile::exists(JPATH_SITE.'/plugins/content/jw_sigpro.php') ||
			JFile::exists(JPATH_SITE.'/plugins/content/jw_sigpro/jw_sigpro.php') ||
			JFile::exists(JPATH_SITE.'/plugins/content/jw_sigpro/jw_sigpro/jw_sigpro.php')
			);
		}

		if (!$this->isSigInstalled)
		{
			$item->gallery = null;
		}

		// Gallery
		if (($view == 'item' && $item->params->get('itemImageGallery')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemImageGallery')) || ($view == 'relatedByTag'))
		{
			if ($item->gallery)
			{
				if (JString::strpos($item->gallery, 'flickr.com') === false)
				{
					$item->gallery = "{gallery}{$item->id}{/gallery}";
					if (!JFolder::exists(JPATH_SITE.'/media/k2/galleries/'.$item->id))
					{
						$item->gallery = null;
					}
				}
				$params->set('galleries_rootfolder', 'media/k2/galleries');

				if ($view == 'item')
				{
					$width = (int)$item->params->get('itemImageGalleryWidth');
					$height = (int)$item->params->get('itemImageGalleryHeight');
				}
				else
				{
					$width = (int)$item->params->get('catItemImageGalleryWidth');
					$height = (int)$item->params->get('catItemImageGalleryHeight');
				}

				if($width && $height) {
					if (JString::strpos($item->gallery, 'flickr.com') !== false)
					{
						$sigParams = JComponentHelper::getParams('com_sigpro');
						$item->gallery = str_replace('{/gallery}', ':'.$sigParams->get('flickrImageCount', 20).':'.$width.':'.$height.'{/gallery}', $item->gallery);
					}
					else
					{
						$item->gallery = str_replace('{/gallery}', ':'.$width.':'.$height.'{/gallery}', $item->gallery);
					}
				}

				$item->text = $item->gallery;
				if (K2_JVERSION == '15')
				{
					$dispatcher->trigger('onPrepareContent', array(
						&$item,
						&$params,
						$limitstart
					));
				}
				else
				{
					$dispatcher->trigger('onContentPrepare', array(
						'com_k2.'.$view.'-gallery',
						&$item,
						&$params,
						$limitstart
					));
				}
				$item->gallery = $item->text;
			}
		}

		// Video
		if (($view == 'item' && $item->params->get('itemVideo')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemVideo')) || ($view == 'latest' && $item->params->get('latestItemVideo')) || ($view == 'relatedByTag'))
		{
			if (!empty($item->video) && JString::substr($item->video, 0, 1) !== '{')
			{
				$item->video = $item->video;
				$item->videoType = 'embedded';
			}
			else
			{
				$item->videoType = 'allvideos';
				$params->set('afolder', 'media/k2/audio');
				$params->set('vfolder', 'media/k2/videos');

				if (JString::strpos($item->video, 'remote}'))
				{
					preg_match("#}(.*?){/#s", $item->video, $matches);
					if (JString::substr($matches[1], 0, 7) != 'http://')
						$item->video = str_replace($matches[1], JURI::root().$matches[1], $item->video);
				}

				if ($view == 'item')
				{
					$params->set('vwidth', $item->params->get('itemVideoWidth'));
					$params->set('vheight', $item->params->get('itemVideoHeight'));
					$params->set('autoplay', $item->params->get('itemVideoAutoPlay'));
				}
				else if ($view == 'latest')
				{
					$params->set('vwidth', $item->params->get('latestItemVideoWidth'));
					$params->set('vheight', $item->params->get('latestItemVideoHeight'));
					$params->set('autoplay', $item->params->get('latestItemVideoAutoPlay'));
				}
				else
				{
					$params->set('vwidth', $item->params->get('catItemVideoWidth'));
					$params->set('vheight', $item->params->get('catItemVideoHeight'));
					$params->set('autoplay', $item->params->get('catItemVideoAutoPlay'));
				}

				$item->text = $item->video;
				if (K2_JVERSION == '15')
				{
					$dispatcher->trigger('onPrepareContent', array(
						&$item,
						&$params,
						$limitstart
					));
				}
				else
				{
					$dispatcher->trigger('onContentPrepare', array(
						'com_k2.'.$view.'-media',
						&$item,
						&$params,
						$limitstart
					));
				}
				$item->video = $item->text;
			}

		}

		// Plugins
		$item->text = '';
		$params->set('vfolder', NULL);
		$params->set('afolder', NULL);
		$params->set('vwidth', NULL);
		$params->set('vheight', NULL);
		$params->set('autoplay', NULL);
		$params->set('galleries_rootfolder', NULL);
		$params->set('enabledownload', NULL);

		if ($view == 'item')
		{

			if ($item->params->get('itemIntroText'))
				$item->text .= $item->introtext;
			if ($item->params->get('itemFullText'))
				$item->text .= '{K2Splitter}'.$item->fulltext;
		}
		else if($view == 'latest') {
			if ($item->params->get('latestItemIntroText'))
				$item->text .= $item->introtext;
		}
		else
		{

			switch($task)
			{
				case '' :
				case 'category' :
					if ($item->params->get('catItemIntroText'))
						$item->text .= $item->introtext;
					break;

				case 'user' :
					if ($item->params->get('userItemIntroText'))
						$item->text .= $item->introtext;
					break;

				case 'tag' :
					if ($item->params->get('tagItemIntroText'))
						$item->text .= $item->introtext;
					break;

				default :
					if ($item->params->get('genericItemIntroText'))
						$item->text .= $item->introtext;
					break;
			}

		}
		$item->event = new stdClass;
		if (K2_JVERSION != '15')
		{

			$item->event->BeforeDisplay = '';
			$item->event->AfterDisplay = '';

			$dispatcher->trigger('onContentPrepare', array(
				'com_k2.'.$view,
				&$item,
				&$params,
				$limitstart
			));

			$results = $dispatcher->trigger('onContentAfterTitle', array(
				'com_k2.'.$view,
				&$item,
				&$params,
				$limitstart
			));
			$item->event->AfterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array(
				'com_k2.'.$view,
				&$item,
				&$params,
				$limitstart
			));
			$item->event->BeforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array(
				'com_k2.'.$view,
				&$item,
				&$params,
				$limitstart
			));
			$item->event->AfterDisplayContent = trim(implode("\n", $results));

		}
		else
		{
			$results = $dispatcher->trigger('onBeforeDisplay', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->BeforeDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onAfterDisplay', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->AfterDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onAfterDisplayTitle', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->AfterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onBeforeDisplayContent', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->BeforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onAfterDisplayContent', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->AfterDisplayContent = trim(implode("\n", $results));

			$dispatcher->trigger('onPrepareContent', array(
				&$item,
				&$params,
				$limitstart
			));

		}

		// K2 plugins
		$item->event->K2BeforeDisplay = '';
		$item->event->K2AfterDisplay = '';
		$item->event->K2AfterDisplayTitle = '';
		$item->event->K2BeforeDisplayContent = '';
		$item->event->K2AfterDisplayContent = '';

		if (($view == 'item' && $item->params->get('itemK2Plugins')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemK2Plugins')) || ($view == 'itemlist' && $task == 'user' && $item->params->get('userItemK2Plugins')) || ($view == 'itemlist' && ($task == 'search' || $task == 'tag' || $task == 'date')))
		{

			JPluginHelper::importPlugin('k2');

			$results = $dispatcher->trigger('onK2BeforeDisplay', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2BeforeDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplay', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2AfterDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplayTitle', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2AfterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2BeforeDisplayContent', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2BeforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplayContent', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2AfterDisplayContent = trim(implode("\n", $results));

			$dispatcher->trigger('onK2PrepareContent', array(
				&$item,
				&$params,
				$limitstart
			));

		}

		if ($view == 'item')
		{
			@list($item->introtext, $item->fulltext) = explode('{K2Splitter}', $item->text);
		}
		else
		{
			$item->introtext = $item->text;
		}

		// Extra fields plugins
		if (($view == 'item' && $item->params->get('itemExtraFields')) || ($view == 'itemlist' && ($task == '' || $task == 'category') && $item->params->get('catItemExtraFields')) || ($view == 'itemlist' && $task == 'tag' && $item->params->get('tagItemExtraFields')) || ($view == 'itemlist' && ($task == 'search' || $task == 'date') && $item->params->get('genericItemExtraFields')))
		{
			if (count($item->extra_fields) && is_array($item->extra_fields))
			{
				foreach ($item->extra_fields as $key => $extraField)
				{
					if ($extraField->type == 'textarea' || $extraField->type == 'textfield')
					{
						$tmp = new JObject();
						$tmp->text = $extraField->value;
						if (K2_JVERSION != '15')
						{
							$dispatcher->trigger('onContentPrepare', array(
								'com_k2.'.$view,
								&$tmp,
								&$params,
								$limitstart
							));
						}
						else
						{
							$dispatcher->trigger('onPrepareContent', array(
								&$tmp,
								&$params,
								$limitstart
							));
						}
						$dispatcher->trigger('onK2PrepareContent', array(
							&$tmp,
							&$params,
							$limitstart
						));
						$extraField->value = $tmp->text;
					}
				}
			}
		}
		return $item;
	}

	function hit($id)
	{
		$row = JTable::getInstance('K2Item', 'Table');
		$row->hit($id);
	}

	function vote()
	{
		$application = JFactory::getApplication();
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

		// Get item
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load(JRequest::getInt('itemID'));

		// Get category
		$category = JTable::getInstance('K2Category', 'Table');
		$category->load($item->catid);

		// Access check
		$user = JFactory::getUser();
		if (K2_JVERSION != '15')
		{
			if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($category->access, $user->getAuthorisedViewLevels()))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}
		}
		else
		{
			if ($item->access > $user->get('aid', 0) || $category->access > $user->get('aid', 0))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}
		}

		// Published check
		if (!$item->published || $item->trash)
		{
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}
		if (!$category->published || $category->trash)
		{
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		$rate = JRequest::getVar('user_rating', 0, '', 'int');

		if ($rate >= 1 && $rate <= 5)
		{
			$db = JFactory::getDbo();
			$userIP = $_SERVER['REMOTE_ADDR'];
			$query = "SELECT * FROM #__k2_rating WHERE itemID =".(int)$item->id;
			$db->setQuery($query);
			$rating = $db->loadObject();

			if (!$rating)
			{
				$query = "INSERT INTO #__k2_rating ( itemID, lastip, rating_sum, rating_count ) VALUES ( ".(int)$item->id.", ".$db->Quote($userIP).", {$rate}, 1 )";
				$db->setQuery($query);
				$db->query();
				echo JText::_('K2_THANKS_FOR_RATING');

			}

			else
			{
				if ($userIP != ($rating->lastip))
				{
					$query = "UPDATE #__k2_rating"." SET rating_count = rating_count + 1, rating_sum = rating_sum + {$rate}, lastip = ".$db->Quote($userIP)." WHERE itemID = {$item->id}";
					$db->setQuery($query);
					$db->query();
					echo JText::_('K2_THANKS_FOR_RATING');

				}
				else
				{
					echo JText::_('K2_YOU_HAVE_ALREADY_RATED_THIS_ITEM');
				}
			}

		}
		$application->close();
	}

	function getRating($id)
	{
		$id = (int)$id;
		static $K2RatingsInstances = array();
		if (array_key_exists($id, $K2RatingsInstances))
		{
			return $K2RatingsInstances[$id];
		}
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_rating WHERE itemID = ".$id;
		$db->setQuery($query);
		$vote = $db->loadObject();
		$K2RatingsInstances[$id] = $vote;
		return $K2RatingsInstances[$id];
	}

	function getVotesNum($itemID = NULL)
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$xhr = false;
		if (is_null($itemID))
		{
			$itemID = JRequest::getInt('itemID');
			$xhr = true;
		}
		$vote = $this->getRating($itemID);
		if (!is_null($vote))
		{
			$rating_count = intval($vote->rating_count);
		}
		else
		{
			$rating_count = 0;
		}
		if ($rating_count != 1)
		{
			$result = "(".$rating_count." ".JText::_('K2_VOTES').")";
		}
		else
		{
			$result = "(".$rating_count." ".JText::_('K2_VOTE').")";
		}
		if ($xhr)
		{
			echo $result;
			$application->close();
		}
		else
		{
			return $result;
		}
	}

	function getVotesPercentage($itemID = NULL)
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$xhr = false;
		$result = 0;
		if (is_null($itemID))
		{
			$itemID = JRequest::getInt('itemID');
			$xhr = true;
		}
		$vote = $this->getRating($itemID);
		if (!is_null($vote) && $vote->rating_count != 0)
		{
			$result = number_format(intval($vote->rating_sum) / intval($vote->rating_count), 2) * 20;
		}
		if ($xhr)
		{
			echo $result;
			$application->close();
		}
		else
		{
			return $result;
		}
	}

	function comment()
	{
		$application = JFactory::getApplication();
		jimport('joomla.mail.helper');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
		$params = K2HelperUtilities::getParams('com_k2');
		$user = JFactory::getUser();
		$config = JFactory::getConfig();
		$response = new JObject();

		// Get item
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load(JRequest::getInt('itemID'));

		// Get category
		$category = JTable::getInstance('K2Category', 'Table');
		$category->load($item->catid);

		// Access check
		if (K2_JVERSION != '15')
		{
			if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($category->access, $user->getAuthorisedViewLevels()))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}
		}
		else
		{
			if ($item->access > $user->get('aid', 0) || $category->access > $user->get('aid', 0))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}
		}

		// Published check
		if (!$item->published || $item->trash)
		{
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}
		if (!$category->published || $category->trash)
		{
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		// Check permissions
		if ((($params->get('comments') == '2') && ($user->id > 0) && K2HelperPermissions::canAddComment($item->catid)) || ($params->get('comments') == '1'))
		{

			// If new antispam settings are not saved, show a message to the comments form and stop the comment submission
			$antispamProtection = $params->get('antispam', null);
			if(
				$antispamProtection === null ||
				(($antispamProtection == 'recaptcha' || $antispamProtection == 'both') && !$params->get('recaptcha_private_key')) ||
				(($antispamProtection == 'akismet' || $antispamProtection == 'both') && !$params->get('akismetApiKey'))
			)
			{
				$response->message = JText::_('K2_ANTISPAM_SETTINGS_ERROR');
				$response->cssClass = 'k2FormLogError';
				echo json_encode($response);
				$application->close();
			}

			$row = JTable::getInstance('K2Comment', 'Table');

			if (!$row->bind(JRequest::get('post')))
			{
				$response->message = $row->getError();
				$response->cssClass = 'k2FormLogError';
				echo json_encode($response);
				$application->close();
			}

			$row->commentText = JRequest::getString('commentText', '', 'default');
			$row->commentText = strip_tags($row->commentText);

			// Clean vars
			$filter = JFilterInput::getInstance();
			$row->userName = $filter->clean($row->userName, 'username');
			if ($row->commentURL && preg_match('/^((http|https|ftp):\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i', $row->commentURL))
			{
				$url = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $row->commentURL);
				$url = str_replace(';//', '://', $url);
				if ($url != '')
				{
					$url = (!strstr($url, '://')) ? 'http://'.$url : $url;
					$url = preg_replace('/&([^#])(?![a-z]{2,8};)/', '&#038;$1', $url);
					$row->commentURL = $url;
				}
			}
			else
			{
				$row->commentURL = '';
			}

			$datenow = JFactory::getDate();
			$row->commentDate = K2_JVERSION == '15' ? $datenow->toMySQL() : $datenow->toSql();

			if (!$user->guest)
			{
				$row->userID = $user->id;
				$row->commentEmail = $user->email;
				$row->userName = $user->name;
			}

			$userName = trim($row->userName);
			$commentEmail = trim($row->commentEmail);
			$commentText = trim($row->commentText);
			$commentURL = trim($row->commentURL);

			if (empty($userName) || $userName == JText::_('K2_ENTER_YOUR_NAME') || empty($commentText) || $commentText == JText::_('K2_ENTER_YOUR_MESSAGE_HERE') || empty($commentEmail) || $commentEmail == JText::_('K2_ENTER_YOUR_EMAIL_ADDRESS'))
			{
				$response->message = JText::_('K2_YOU_NEED_TO_FILL_IN_ALL_REQUIRED_FIELDS');
				$response->cssClass = 'k2FormLogError';
				echo json_encode($response);
				$application->close();
			}

			if (!JMailHelper::isEmailAddress($commentEmail))
			{
				$response->message = JText::_('K2_INVALID_EMAIL_ADDRESS');
				$response->cssClass = 'k2FormLogError';
				echo json_encode($response);
				$application->close();
			}

			if ($user->guest)
			{
				$db = JFactory::getDbo();
				$query = "SELECT COUNT(*) FROM #__users WHERE name=".$db->Quote($userName)." OR email=".$db->Quote($commentEmail);
				$db->setQuery($query);
				$result = $db->loadresult();
				if ($result > 0)
				{
					$response->message = JText::_('K2_THE_NAME_OR_EMAIL_ADDRESS_YOU_TYPED_IS_ALREADY_IN_USE');
					$response->cssClass = 'k2FormLogError';
					echo json_encode($response);
					$application->close();
				}

			}

			// Google reCAPTCHA
			if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both')
			{
				if ($user->guest || $params->get('recaptchaForRegistered', 1))
				{
					if($params->get('recaptchaV2'))
					{
						require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
						if (!K2HelperUtilities::verifyRecaptcha())
						{
							$response->message = JText::_('K2_COULD_NOT_VERIFY_THAT_YOU_ARE_NOT_A_ROBOT');
							$response->cssClass = 'k2FormLogError';
							echo json_encode($response);
							$application->close();
						}
					}
					else
					{
						if (!function_exists('_recaptcha_qsencode'))
						{
							require_once(JPATH_SITE.'/media/k2/assets/vendors/google/recaptcha_legacy/recaptcha.php');
						}
						$privatekey = trim($params->get('recaptcha_private_key'));
						$recaptcha_challenge_field = isset($_POST["recaptcha_challenge_field"]) ? $_POST["recaptcha_challenge_field"] : '';
						$recaptcha_response_field = isset($_POST["recaptcha_response_field"]) ? $_POST["recaptcha_response_field"] : '';
						$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge_field, $recaptcha_response_field);
						if (!$resp->is_valid)
						{
							$response->message = JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN');
							$response->cssClass = 'k2FormLogError';
							echo json_encode($response);
							$application->close();
						}
					}

				}
			}

			// Akismet
			if ($params->get('antispam') == 'akismet' || $params->get('antispam') == 'both')
			{
				if ($user->guest || $params->get('akismetForRegistered', 1))
				{
					if ($params->get('akismetApiKey'))
					{
						require_once(JPATH_SITE.'/media/k2/assets/vendors/achingbrain/php5-akismet/akismet.class.php');
						$akismetApiKey = trim($params->get('akismetApiKey'));
						$akismet = new Akismet(JURI::root(false), $akismetApiKey);
						$akismet->setCommentAuthor($userName);
						$akismet->setCommentAuthorEmail($commentEmail);
						$akismet->setCommentAuthorURL($commentURL);
						$akismet->setCommentContent($commentText);
						$akismet->setPermalink(JURI::root(false).'index.php?option=com_k2&view=item&id='.JRequest::getInt('itemID'));
						try
						{
							if ($akismet->isCommentSpam())
							{
								$response->message = JText::_('K2_SPAM_ATTEMPT_HAS_BEEN_DETECTED_THE_COMMENT_HAS_BEEN_REJECTED');
								$response->cssClass = 'k2FormLogError';
								echo json_encode($response);
								$application->close();
							}
						}
						catch(Exception $e)
						{
							$response->message = $e->getMessage();
							$response->cssClass = 'k2FormLogSuccess';
							echo json_encode($response);
							$application->close();
						}

					}
				}
			}

			if ($commentURL == JText::_('K2_ENTER_YOUR_SITE_URL') || $commentURL == "")
			{
				$row->commentURL = NULL;
			}
			else
			{
				if (substr($commentURL, 0, 7) != 'http://')
				{
					$row->commentURL = 'http://'.$commentURL;
				}
			}

			if ($params->get('commentsPublishing', false))
			{
				$row->published = 1;
			}
			else
			{
				$row->published = 0;
				// Auto publish comments for users with administrative permissions
				if (K2_JVERSION != '15')
				{
					if ($user->authorise('core.admin'))
					{
						$row->published = 1;
					}
				}
				else
				{
					if ($user->gid > 23)
					{
						$row->published = 1;
					}
				}
			}

			if (!$row->store())
			{
				$response->message = $row->getError();
				$response->cssClass = 'k2FormLogError';
				echo json_encode($response);
				$application->close();
			}

			if ($row->published)
			{
				$caching = K2_JVERSION == '30' ? $config->get('caching') : $config->getValue('config.caching');
				if ($caching && $user->guest)
				{
					$response->message = JText::_('K2_THANK_YOU_YOUR_COMMENT_WILL_BE_PUBLISHED_SHORTLY');
					$response->cssClass = 'k2FormLogSuccess';
					echo json_encode($response);
				}
				else
				{
					$response->message = JText::_('K2_COMMENT_ADDED_REFRESHING_PAGE');
					$response->cssClass = 'k2FormLogSuccess';
					$response->refresh = 1;
					echo json_encode($response);
				}

			}
			else
			{
				$response->message = JText::_('K2_COMMENT_ADDED_AND_WAITING_FOR_APPROVAL');
				$response->cssClass = 'k2FormLogSuccess';
				echo json_encode($response);
			}

		}
		$application->close();
	}

	function getItemTags($itemID)
	{
		$itemID = (int)$itemID;
		static $K2ItemTagsInstances = array();
		if (isset($K2ItemTagsInstances[$itemID]))
		{
			return $K2ItemTagsInstances[$itemID];
		}
		$db = JFactory::getDbo();
		$query = "SELECT tag.*
			FROM #__k2_tags AS tag
			JOIN #__k2_tags_xref AS xref ON tag.id = xref.tagID
			WHERE tag.published=1
			AND xref.itemID = ".(int)$itemID." ORDER BY xref.id ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$K2ItemTagsInstances[$itemID] = $rows;
		return $K2ItemTagsInstances[$itemID];
	}

	function getItemExtraFields($itemExtraFields, &$item = null)
	{
		static $K2ItemExtraFieldsInstances = array();
		if ($item && isset($K2ItemExtraFieldsInstances[$item->id]))
		{
			$this->buildAliasBasedExtraFields($K2ItemExtraFieldsInstances[$item->id], $item);
			return $K2ItemExtraFieldsInstances[$item->id];
		}

		jimport('joomla.filesystem.file');
		$db = JFactory::getDbo();
		$jsonObjects = json_decode($itemExtraFields);
		$imgExtensions = array(
			'jpg',
			'jpeg',
			'gif',
			'png'
		);
		$params = K2HelperUtilities::getParams('com_k2');

		if (count($jsonObjects) < 1)
		{
			return NULL;
		}

		foreach ($jsonObjects as $object)
		{
			$extraFieldsIDs[] = $object->id;
		}
		JArrayHelper::toInteger($extraFieldsIDs);
		$condition = @implode(',', $extraFieldsIDs);

		$query = "SELECT extraFieldsGroup FROM #__k2_categories WHERE id=".(int)$item->catid;
		$db->setQuery($query);
		$group = $db->loadResult();

		$query = "SELECT * FROM #__k2_extra_fields WHERE `group` = ".(int)$group." AND published=1 AND (id IN ({$condition}) OR `type` = 'header') ORDER BY ordering ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$size = count($rows);

		for ($i = 0; $i < $size; $i++)
		{
			$value = '';
			$rawValue = '';
			$values = array();
			foreach ($jsonObjects as $object)
			{
				if ($rows[$i]->id == $object->id)
				{
					if ($rows[$i]->type == 'textfield' || $rows[$i]->type == 'textarea' || $rows[$i]->type == 'date')
					{
						$value = $object->value;
						if ($rows[$i]->type == 'date' && $value)
						{
							$rawValue = $value;
							$offset = (K2_JVERSION != '15') ? null : 0;
							$value = JHTML::_('date', $value, JText::_('K2_DATE_FORMAT_LC'), $offset);
						}

					}
					else if ($rows[$i]->type == 'image')
					{
						if ($object->value)
						{
							$src = '';
							if (JString::strpos('http://', $object->value) === false)
							{
								$src .= JURI::root(true);
							}
							$src .= $object->value;
							$value = '<img src="'.$src.'" alt="'.$rows[$i]->name.'" />';
						}
						else
						{
							$value = false;
						}

					}
					else if ($rows[$i]->type == 'labels')
					{
						$labels = explode(',', $object->value);
						if (!is_array($labels))
						{
							$labels = (array)$labels;
						}
						$value = '';
						foreach ($labels as $label)
						{
							$label = JString::trim($label);
							if($label != '')
							{
								$label = str_replace('-', ' ', $label);
								$value .= '<a href="'.JRoute::_('index.php?option=com_k2&view=itemlist&task=search&searchword=' . urlencode($label)) . '">'.$label.'</a>';
							}
						}

					}
					else if ($rows[$i]->type == 'select' || $rows[$i]->type == 'radio')
					{
						foreach (json_decode($rows[$i]->value) as $option)
						{
							if ($option->value == $object->value)
							{
								$value .= $option->name;
							}

						}
					}
					else if ($rows[$i]->type == 'multipleSelect')
					{
						foreach (json_decode($rows[$i]->value) as $option)
						{
							if (@in_array($option->value, $object->value))
							{
								$values[] = $option->name;
							}

						}
						$value = @implode(', ', $values);
					}
					else if ($rows[$i]->type == 'csv')
					{
						$array = $object->value;
						if (count($array))
						{
							$value .= '<table cellspacing="0" cellpadding="0" class="csvTable">';
							foreach ($array as $key => $row)
							{
								$value .= '<tr>';
								foreach ($row as $cell)
								{
									$value .= ($key > 0) ? '<td>'.$cell.'</td>' : '<th>'.$cell.'</th>';
								}
								$value .= '</tr>';
							}
							$value .= '</table>';
						}

					}
					else
					{

						switch ($object->value[2])
						{
							case 'same' :
							default :
								$attributes = '';
								break;

							case 'new' :
								$attributes = 'target="_blank"';
								break;

							case 'popup' :
								$attributes = 'class="classicPopup" rel="{\'x\':'.$params->get('linkPopupWidth').',\'y\':'.$params->get('linkPopupHeight').'}"';
								break;

							case 'lightbox' :

								// Joomla modal required
								if (!defined('K2_JOOMLA_MODAL_REQUIRED'))
									define('K2_JOOMLA_MODAL_REQUIRED', true);

								$filename = @basename($object->value[1]);
								$extension = JFile::getExt($filename);
								if (!empty($extension) && in_array($extension, $imgExtensions))
								{
									$attributes = 'data-k2-modal="image"';
								}
								else
								{
									$attributes = 'data-k2-modal="iframe"';
								}
								break;
						}
						$object->value[0] = JString::trim($object->value[0]);
						$object->value[1] = JString::trim($object->value[1]);

						if ($object->value[1] && $object->value[1] != 'http://' && $object->value[1] != 'https://')
						{
							if ($object->value[0] == '')
							{
								$object->value[0] = $object->value[1];
							}
							$rows[$i]->url = $object->value[1];
							$rows[$i]->text = $object->value[0];
							$rows[$i]->attributes = $attributes;
							$value = '<a href="'.$object->value[1].'" '.$attributes.'>'.$object->value[0].'</a>';
						}
						else
						{
							$value = false;
						}
					}

				}

			}

			if ($rows[$i]->type == 'header')
			{
				$tmp = json_decode($rows[$i]->value);
				if (!$tmp[0]->displayInFrontEnd)
				{
					$value = null;
				}
				else
				{
					$value = $tmp[0]->value;
				}
			}

			// Detect alias
			$tmpValues = json_decode($rows[$i]->value);
			if (isset($tmpValues[0]) && isset($tmpValues[0]->alias) && !empty($tmpValues[0]->alias))
			{
				$rows[$i]->alias = $tmpValues[0]->alias;
			}
			else
			{
				$filter = JFilterInput::getInstance();
				$rows[$i]->alias = $filter->clean($rows[$i]->name, 'WORD');
				if (!$rows[$i]->alias)
				{
					$rows[$i]->alias = 'extraField'.$rows[$i]->id;
				}
			}

			if (JString::trim($value) != '')
			{
				if (JString::trim($rawValue) != '')
				{
					$rows[$i]->rawValue = $rawValue;
				}
				$rows[$i]->value = $value;
				if (!is_null($item))
				{
					if (!isset($item->extraFields))
					{
						$item->extraFields = new stdClass;
					}
					$tmpAlias = $rows[$i]->alias;
					$item->extraFields->$tmpAlias = $rows[$i];
				}
			}
			else
			{
				unset($rows[$i]);
			}
		}

		if ($item)
		{
			$K2ItemExtraFieldsInstances[$item->id] = $rows;
		}
		$this->buildAliasBasedExtraFields($K2ItemExtraFieldsInstances[$item->id], $item);
		return $K2ItemExtraFieldsInstances[$item->id];
	}

	function buildAliasBasedExtraFields($extraFields, &$item)
	{
		if (is_null($item))
		{
			return false;
		}
		if (!isset($item->extraFields))
		{
			$item->extraFields = new stdClass;
		}
		foreach ($extraFields as $extraField)
		{
			$tmpAlias = $extraField->alias;
			$item->extraFields->$tmpAlias = $extraField;
		}
	}

	function getItemAttachments($itemID)
	{
		$itemID = (int)$itemID;
		static $K2ItemAttachmentsInstances = array();
		if (isset($K2ItemAttachmentsInstances[$itemID]))
		{
			return $K2ItemAttachmentsInstances[$itemID];
		}
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_attachments WHERE itemID=".$itemID;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($row->id) : JUtility::getHash($row->id);
			$row->link = JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$row->id.'_'.$hash);
		}
		$K2ItemAttachmentsInstances[$itemID] = $rows;
		return $K2ItemAttachmentsInstances[$itemID];
	}

	function getItemComments($itemID, $limitstart, $limit, $published = true)
	{
		$params = K2HelperUtilities::getParams('com_k2');
		$order = $params->get('commentsOrdering', 'DESC');
		$ordering = ($order == 'DESC') ? 'DESC' : 'ASC';
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_comments WHERE itemID=".(int)$itemID;
		if ($published)
		{
			$query .= " AND published=1 ";
		}
		$query .= " ORDER BY commentDate {$ordering}";
		$db->setQuery($query, $limitstart, $limit);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function countItemComments($itemID, $published = true)
	{
		$itemID = (int)$itemID;
		$index = $itemID.'_'.(int)$published;
		static $K2ItemCommentsCountInstances = array();
		if (isset($K2ItemCommentsCountInstances[$index]))
		{
			return $K2ItemCommentsCountInstances[$index];
		}
		$db = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM #__k2_comments WHERE itemID=".$itemID;
		if ($published)
		{
			$query .= " AND published=1 ";
		}
		$db->setQuery($query);
		$result = $db->loadResult();
		$K2ItemCommentsCountInstances[$index] = $result;
		return $K2ItemCommentsCountInstances[$index];
	}

	function checkin()
	{
	    $application = JFactory::getApplication();
		$id = JRequest::getInt('cid');
	    if($id)
	    {
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->checkin();
	    }
	    else
	    {
			// Clean up SIGPro
			$sigProFolder = JRequest::getCmd('sigProFolder');
			if($sigProFolder && !is_numeric($sigProFolder) && JFolder::exists(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder))
			{
				JFolder::delete(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder);
			}
	    }
		$application->close();
	}

	function getPreviousItem($id, $catid, $ordering)
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = (int)$id;
		$catid = (int)$catid;
		$ordering = (int)$ordering;
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		if (K2_JVERSION != '15')
		{
			$accessCondition = ' AND access IN('.implode(',', $user->getAuthorisedViewLevels()).')';
		}
		else
		{
			$accessCondition = ' AND access <= '.$user->aid; ;
		}

		$languageCondition = '';
		if (K2_JVERSION != '15')
		{
			if ($application->getLanguageFilter())
			{
				$languageCondition = "AND language IN (".$db->quote(JFactory::getLanguage()->getTag()).",".$db->quote('*').")";
			}
		}

		if ($ordering == "0")
		{
			$query = "SELECT * FROM #__k2_items WHERE id < {$id} AND catid={$catid} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) {$accessCondition} AND trash=0 {$languageCondition} ORDER BY ordering DESC";
		}
		else
		{
			$query = "SELECT * FROM #__k2_items WHERE id != {$id} AND catid={$catid} AND ordering < {$ordering} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) {$accessCondition} AND trash=0 {$languageCondition} ORDER BY ordering DESC";
		}

		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();
		return $row;
	}

	function getNextItem($id, $catid, $ordering)
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = (int)$id;
		$catid = (int)$catid;
		$ordering = (int)$ordering;
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		if (K2_JVERSION != '15')
		{
			$accessCondition = ' AND access IN('.implode(',', $user->getAuthorisedViewLevels()).')';
		}
		else
		{
			$accessCondition = ' AND access <= '.$user->aid; ;
		}

		$languageCondition = '';
		if (K2_JVERSION != '15')
		{
			if ($application->getLanguageFilter())
			{
				$languageCondition = "AND language IN (".$db->quote(JFactory::getLanguage()->getTag()).",".$db->quote('*').")";
			}
		}

		if ($ordering == "0")
		{
			$query = "SELECT * FROM #__k2_items WHERE id > {$id} AND catid={$catid} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) {$accessCondition} AND trash=0 {$languageCondition} ORDER BY ordering ASC";
		}
		else
		{
			$query = "SELECT * FROM #__k2_items WHERE id != {$id} AND catid={$catid} AND ordering > {$ordering} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) {$accessCondition} AND trash=0 {$languageCondition} ORDER BY ordering ASC";
		}
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();
		return $row;
	}

	function getUserProfile($id = NULL)
	{
		$db = JFactory::getDbo();
		if (is_null($id))
			$id = JRequest::getInt('id');

		static $K2UsersInstances = array();
		if (isset($K2UsersInstances[$id]))
		{
			return $K2UsersInstances[$id];
		}

		$query = "SELECT id, gender, description, image, url, `group`, plugins FROM #__k2_users WHERE userID={$id}";
		$db->setQuery($query);
		$row = $db->loadObject();
		$K2UsersInstances[$id] = $row;
		return $row;
	}
}
