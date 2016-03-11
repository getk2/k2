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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelItems extends K2Model
{

	function getData()
	{

		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDBO();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'i.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_trash = $mainframe->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
		$filter_featured = $mainframe->getUserStateFromRequest($option.$view.'filter_featured', 'filter_featured', -1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$tag = $mainframe->getUserStateFromRequest($option.$view.'tag', 'tag', 0, 'int');
		$language = $mainframe->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');

		$query = "SELECT i.*, g.name AS groupname, c.name AS category, v.name AS author, w.name as moderator, u.name AS editor FROM #__k2_items as i";

		$query .= " LEFT JOIN #__k2_categories AS c ON c.id = i.catid"." LEFT JOIN #__groups AS g ON g.id = i.access"." LEFT JOIN #__users AS u ON u.id = i.checked_out"." LEFT JOIN #__users AS v ON v.id = i.created_by"." LEFT JOIN #__users AS w ON w.id = i.modified_by";

		if ($params->get('showTagFilter') && $tag)
		{
			$query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id";
		}

		$query .= " WHERE i.trash={$filter_trash}";

		if ($search)
		{
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$quoted = $db->Quote('%'.$escaped.'%', false);

			if ($params->get('adminSearch') == 'full')
			{
				$query .= " AND ( LOWER(i.title) LIKE ".$quoted." OR LOWER(i.introtext) LIKE ".$quoted." OR LOWER(i.`fulltext`) LIKE ".$quoted." OR LOWER(i.extra_fields_search) LIKE ".$quoted." OR LOWER(i.image_caption) LIKE ".$quoted." OR LOWER(i.image_credits) LIKE ".$quoted." OR LOWER(i.video_caption) LIKE ".$quoted." OR LOWER(i.video_credits) LIKE ".$quoted." OR LOWER(i.metadesc) LIKE ".$quoted." OR LOWER(i.metakey) LIKE ".$quoted.") ";
			}
			else
			{
				$query .= " AND LOWER(i.title) LIKE ".$quoted;
			}
		}

		if ($filter_state > -1)
		{
			$query .= " AND i.published={$filter_state}";
		}

		if ($filter_featured > -1)
		{
			$query .= " AND i.featured={$filter_featured}";
		}

		if ($filter_category > 0)
		{
			if ($params->get('showChildCatItems'))
			{
				K2Model::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models');
				$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
				$categories = $itemListModel->getCategoryTree($filter_category);
				$sql = @implode(',', $categories);
				$query .= " AND i.catid IN ({$sql})";
			}
			else
			{
				$query .= " AND i.catid={$filter_category}";
			}

		}

		if ($filter_author > 0)
		{
			$query .= " AND i.created_by={$filter_author}";
		}

		if ($params->get('showTagFilter') && $tag)
		{
			$query .= " AND tags_xref.tagID = {$tag}";
		}

		if ($language)
		{
			$query .= " AND i.language = ".$db->Quote($language);
		}

		if ($filter_order == 'i.ordering')
		{
			$query .= " ORDER BY i.catid, i.ordering {$filter_order_Dir}";
		}
		else
		{
			$query .= " ORDER BY {$filter_order} {$filter_order_Dir} ";
		}

		if (K2_JVERSION != '15')
		{
			$query = JString::str_ireplace('#__groups', '#__viewlevels', $query);
			$query = JString::str_ireplace('g.name', 'g.title', $query);
		}
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeSetQuery', array(&$query));
		$db->setQuery($query, $limitstart, $limit);
		$rows = $db->loadObjectList();
		return $rows;

	}

	function getTotal()
	{

		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDBO();
		$filter_trash = $mainframe->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
		$filter_featured = $mainframe->getUserStateFromRequest($option.$view.'filter_featured', 'filter_featured', -1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$tag = $mainframe->getUserStateFromRequest($option.$view.'tag', 'tag', 0, 'int');
		$language = $mainframe->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');

		$query = "SELECT COUNT(*) FROM #__k2_items AS i ";

		if ($params->get('showTagFilter') && $tag)
		{
			$query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id";
		}

		$query .= " WHERE trash={$filter_trash} ";

		if ($search)
		{
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$quoted = $db->Quote('%'.$escaped.'%', false);

			if ($params->get('adminSearch') == 'full')
			{
				$query .= " AND ( LOWER(i.title) LIKE ".$quoted." OR LOWER(i.introtext) LIKE ".$quoted." OR LOWER(i.`fulltext`) LIKE ".$quoted." OR LOWER(i.extra_fields_search) LIKE ".$quoted." OR LOWER(i.image_caption) LIKE ".$quoted." OR LOWER(i.image_credits) LIKE ".$quoted." OR LOWER(i.video_caption) LIKE ".$quoted." OR LOWER(i.video_credits) LIKE ".$quoted." OR LOWER(i.metadesc) LIKE ".$quoted." OR LOWER(i.metakey) LIKE ".$quoted.") ";
			}
			else
			{
				$query .= " AND LOWER(i.title) LIKE ".$quoted;
			}
		}

		if ($filter_state > -1)
		{
			$query .= " AND published={$filter_state}";
		}

		if ($filter_featured > -1)
		{
			$query .= " AND featured={$filter_featured}";
		}

		if ($filter_category > 0)
		{
			if ($params->get('showChildCatItems'))
			{
				K2Model::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models');
				$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
				$categories = $itemListModel->getCategoryTree($filter_category);
				$sql = @implode(',', $categories);
				$query .= " AND catid IN ({$sql})";
			}
			else
			{
				$query .= " AND catid={$filter_category}";
			}

		}

		if ($filter_author > 0)
		{
			$query .= " AND created_by={$filter_author}";
		}

		if ($params->get('showTagFilter') && $tag)
		{
			$query .= " AND tags_xref.tagID = {$tag}";
		}

		if ($language)
		{
			$query .= " AND language = ".$db->Quote($language);
		}
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeSetQuery', array(&$query));
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;

	}

	function publish()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->published = 1;
			$row->store();
		}
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFinderChangeState', array(
			'com_k2.item',
			$cid,
			1
		));
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function unpublish()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->published = 0;
			$row->store();
		}
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFinderChangeState', array(
			'com_k2.item',
			$cid,
			0
		));
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function saveorder()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');
		$total = count($cid);
		$order = JRequest::getVar('order', array(0), 'post', 'array');
		JArrayHelper::toInteger($order, array(0));
		$groupings = array();
		for ($i = 0; $i < $total; $i++)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load((int)$cid[$i]);
			$groupings[] = $row->catid;
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store())
				{
					JError::raiseError(500, $db->getErrorMsg());
				}
			}
		}
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
		{
			$groupings = array_unique($groupings);
			foreach ($groupings as $group)
			{
				$row = JTable::getInstance('K2Item', 'Table');
				$row->reorder('catid = '.(int)$group.' AND trash=0');
			}
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		return true;
	}

	function orderup()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$row = JTable::getInstance('K2Item', 'Table');
		$row->load($cid[0]);
		$row->move(-1, 'catid = '.(int)$row->catid.' AND trash=0');
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
			$row->reorder('catid = '.(int)$row->catid.' AND trash=0');
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function orderdown()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$row = JTable::getInstance('K2Item', 'Table');
		$row->load($cid[0]);
		$row->move(1, 'catid = '.(int)$row->catid.' AND trash=0');
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
			$row->reorder('catid = '.(int)$row->catid.' AND trash=0');
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function savefeaturedorder()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');
		$total = count($cid);
		$order = JRequest::getVar('order', array(0), 'post', 'array');
		JArrayHelper::toInteger($order, array(0));
		$groupings = array();
		for ($i = 0; $i < $total; $i++)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load((int)$cid[$i]);
			$groupings[] = $row->catid;
			if ($row->featured_ordering != $order[$i])
			{
				$row->featured_ordering = $order[$i];
				if (!$row->store())
				{
					JError::raiseError(500, $db->getErrorMsg());
				}
			}
		}
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
		{
			$groupings = array_unique($groupings);
			foreach ($groupings as $group)
			{
				$row = JTable::getInstance('K2Item', 'Table');
				$row->reorder('featured = 1 AND trash=0', 'featured_ordering');
			}
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		return true;
	}

	function featuredorderup()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$row = JTable::getInstance('K2Item', 'Table');
		$row->load($cid[0]);
		$row->move(-1, 'featured=1 AND trash=0', 'featured_ordering');
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
			$row->reorder('featured=1 AND trash=0', 'featured_ordering');
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function featuredorderdown()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$row = JTable::getInstance('K2Item', 'Table');
		$row->load($cid[0]);
		$row->move(1, 'featured=1 AND trash=0', 'featured_ordering');
		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('disableCompactOrdering'))
			$row->reorder('featured=1 AND trash=0', 'featured_ordering');
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ORDERING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function accessregistered()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$row = JTable::getInstance('K2Item', 'Table');
		$cid = JRequest::getVar('cid');
		$row->load($cid[0]);
		$row->access = 1;
		if (!$row->check())
		{
			return $row->getError();
		}
		if (!$row->store())
		{
			return $row->getError();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function accessspecial()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$row = JTable::getInstance('K2Item', 'Table');
		$cid = JRequest::getVar('cid');
		$row->load($cid[0]);
		$row->access = 2;
		if (!$row->check())
		{
			return $row->getError();
		}
		if (!$row->store())
		{
			return $row->getError();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function accesspublic()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$row = JTable::getInstance('K2Item', 'Table');
		$cid = JRequest::getVar('cid');
		$row->load($cid[0]);
		$row->access = 0;
		if (!$row->check())
		{
			return $row->getError();
		}
		if (!$row->store())
		{
			return $row->getError();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function copy($batch = false)
	{

		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$params = JComponentHelper::getParams('com_k2');
		$itemModel = K2Model::getInstance('Item', 'K2Model');
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		JArrayHelper::toInteger($cid);
		$row = JTable::getInstance('K2Item', 'Table');
		$copies = array();
		$nullDate = $db->getNullDate();

		foreach ($cid as $id)
		{

			//Load source item
			$item = JTable::getInstance('K2Item', 'Table');
			$item->load($id);
			$item->id = (int)$item->id;

			//Source images
			$sourceImage = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.md5("Image".$item->id).'.jpg';
			$sourceImageXS = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XS.jpg';
			$sourceImageS = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_S.jpg';
			$sourceImageM = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_M.jpg';
			$sourceImageL = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_L.jpg';
			$sourceImageXL = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XL.jpg';
			$sourceImageGeneric = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg';

			//Source gallery
			$sourceGallery = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$item->id;
			$sourceGalleryTag = $item->gallery;

			//Source video
			preg_match_all("#^{(.*?)}(.*?){#", $item->video, $matches, PREG_PATTERN_ORDER);
			$videotype = $matches[1][0];
			$videofile = $matches[2][0];

			if ($videotype == 'flv' || $videotype == 'swf' || $videotype == 'wmv' || $videotype == 'mov' || $videotype == 'mp4' || $videotype == '3gp' || $videotype == 'divx')
			{
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype))
				{
					$sourceVideo = $videofile.'.'.$videotype;
					//$row->video='{'.$videotype.'}'.$row->id.'{/'.$videotype.'}';
				}
			}

			//Source tags
			$query = "SELECT * FROM #__k2_tags_xref WHERE itemID={$item->id}";
			$db->setQuery($query);
			$sourceTags = $db->loadObjectList();

			//Source Attachments
			$sourceAttachments = $itemModel->getAttachments($item->id);

			//Save target item
			$row = JTable::getInstance('K2Item', 'Table');
			$row = $item;
			$row->id = NULL;
			$row->title = JText::_('K2_COPY_OF').' '.$item->title;
			$row->hits = 0;
			$row->published = 0;
			$datenow = JFactory::getDate();
			$row->created = K2_JVERSION == '15' ? $datenow->toMySQL() : $datenow->toSql();
			$row->modified = $nullDate;
			$row->store();
			$copies[] = $row->id;

			//Target images
			if (JFile::exists($sourceImage))
				JFile::copy($sourceImage, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.md5("Image".$row->id).'.jpg');
			if (JFile::exists($sourceImageXS))
				JFile::copy($sourceImageXS, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XS.jpg');
			if (JFile::exists($sourceImageS))
				JFile::copy($sourceImageS, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_S.jpg');
			if (JFile::exists($sourceImageM))
				JFile::copy($sourceImageM, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_M.jpg');
			if (JFile::exists($sourceImageL))
				JFile::copy($sourceImageL, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_L.jpg');
			if (JFile::exists($sourceImageXL))
				JFile::copy($sourceImageXL, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XL.jpg');
			if (JFile::exists($sourceImageGeneric))
				JFile::copy($sourceImageGeneric, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_Generic.jpg');

			//Target gallery
			if ($sourceGalleryTag)
			{
				if (JString::strpos($sourceGalleryTag, 'http://'))
				{
					$row->gallery = $sourceGalleryTag;
				}
				else
				{
					$row->gallery = '{gallery}'.$row->id.'{/gallery}';
					if (JFolder::exists($sourceGallery))
					{
						JFolder::copy($sourceGallery, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$row->id);
					}
				}
			}

			//Target video
			if (isset($sourceVideo) && JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$sourceVideo))
			{
				JFile::copy(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$sourceVideo, JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$row->id.'.'.$videotype);
				$row->video = '{'.$videotype.'}'.$row->id.'{/'.$videotype.'}';
			}

			//Target attachments
			$path = $params->get('attachmentsFolder', NULL);
			if (is_null($path))
				$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
			else
				$savepath = $path;

			foreach ($sourceAttachments as $attachment)
			{
				if (JFile::exists($savepath.DS.$attachment->filename))
				{
					JFile::copy($savepath.DS.$attachment->filename, $savepath.DS.$row->id.'_'.$attachment->filename);
					$attachmentRow = JTable::getInstance('K2Attachment', 'Table');
					$attachmentRow->itemID = $row->id;
					$attachmentRow->title = $attachment->title;
					$attachmentRow->titleAttribute = $attachment->titleAttribute;
					$attachmentRow->filename = $row->id.'_'.$attachment->filename;
					$attachmentRow->hits = 0;
					$attachmentRow->store();
				}
			}

			//Target tags
			foreach ($sourceTags as $tag)
			{
				$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tag->tagID)}, {intval($row->id)})";
				$db->setQuery($query);
				$db->query();
			}

			$row->store();
		}
		if($batch) {
			return $copies;
		} else {
			$mainframe->enqueueMessage(JText::_('K2_COPY_COMPLETED'));
			$mainframe->redirect('index.php?option=com_k2&view=items');
		}

	}

	function featured()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			if ($row->featured == 1)
				$row->featured = 0;
			else
			{
				$row->featured = 1;
				$row->featured_ordering = 1;
			}
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_ITEMS_CHANGED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function trash()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		JArrayHelper::toInteger($cid);
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->trash = 1;
			$row->store();
		}
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFinderChangeState', array(
			'com_k2.item',
			$cid,
			0
		));
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_ITEMS_MOVED_TO_TRASH'));
		$mainframe->redirect('index.php?option=com_k2&view=items');

	}

	function restore()
	{

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$warning = false;
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$query = "SELECT COUNT(*) FROM #__k2_categories WHERE id=".(int)$row->catid." AND trash = 0";
			$db->setQuery($query);
			$result = $db->loadResult();
			if ($result)
			{
				$row->trash = 0;
				$row->store();
			}
			else
			{
				$warning = true;
			}

		}
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFinderChangeState', array(
			'com_k2.item',
			$cid,
			1
		));
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if ($warning)
			$mainframe->enqueueMessage(JText::_('K2_SOME_OF_THE_ITEMS_HAVE_NOT_BEEN_RESTORED_BECAUSE_THEY_BELONG_TO_A_CATEGORY_WHICH_IS_IN_TRASH'), 'notice');
		$mainframe->enqueueMessage(JText::_('K2_ITEMS_RESTORED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');

	}

	function remove()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$itemModel = K2Model::getInstance('Item', 'K2Model');
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->id = (int)$row->id;
			//Delete images
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.md5("Image".$row->id).'.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.md5("Image".$row->id).'.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XS.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XS.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_S.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_S.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_M.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_M.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_L.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_L.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XL.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XL.jpg');
			}
			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_Generic.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_Generic.jpg');
			}

			//Delete gallery
			if (JFolder::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$row->id))
				JFolder::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$row->id);

			//Delete video
			preg_match_all("#^{(.*?)}(.*?){#", $row->video, $matches, PREG_PATTERN_ORDER);
			$videotype = $matches[1][0];
			$videofile = $matches[2][0];

			$videoExtensions = array(
				'flv',
				'mp4',
				'ogv',
				'webm',
				'f4v',
				'm4v',
				'3gp',
				'3g2',
				'mov',
				'mpeg',
				'mpg',
				'avi',
				'wmv',
				'divx',
				'swf'
			);
			$audioExtensions = array(
				'mp3',
				'aac',
				'mp4',
				'ogg',
				'wma'
			);

			if (in_array($videotype, $videoExtensions) || in_array($videotype, $audioExtensions))
			{

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype))
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype);

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype))
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype);
			}

			//Delete attachments
			$path = $params->get('attachmentsFolder', NULL);
			if (is_null($path))
				$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
			else
				$savepath = $path;

			$attachments = $itemModel->getAttachments($row->id);

			foreach ($attachments as $attachment)
			{
				if (JFile::exists($savepath.DS.$attachment->filename))
					JFile::delete($savepath.DS.$attachment->filename);
			}

			$query = "DELETE FROM #__k2_attachments WHERE itemID={$row->id}";
			$db->setQuery($query);
			$db->query();

			//Delete tags
			$query = "DELETE FROM #__k2_tags_xref WHERE itemID={$row->id}";
			$db->setQuery($query);
			$db->query();

			//Delete comments
			$query = "DELETE FROM #__k2_comments WHERE itemID={$row->id}";
			$db->setQuery($query);
			$db->query();

			$row->delete($id);

			$dispatcher->trigger('onFinderAfterDelete', array(
				'com_k2.item',
				$row
			));
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function import()
	{

		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__sections";
		$db->setQuery($query);
		$sections = $db->loadObjectList();

		$query = "SELECT COUNT(*) FROM #__k2_items";
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result)
			$preserveItemIDs = false;
		else
			$preserveItemIDs = true;

		$xml = new JSimpleXML;
		$xml->loadFile(JPATH_COMPONENT.DS.'models'.DS.'category.xml');
		$categoryParams = class_exists('JParameter') ? new JParameter('') : new JRegistry('');

		foreach ($xml->document->params as $paramGroup)
		{
			foreach ($paramGroup->param as $param)
			{
				if ($param->attributes('type') != 'spacer' && $param->attributes('name'))
				{
					$categoryParams->set($param->attributes('name'), $param->attributes('default'));
				}
			}
		}
		$categoryParams = $categoryParams->toString();

		$xml = new JSimpleXML;
		$xml->loadFile(JPATH_COMPONENT.DS.'models'.DS.'item.xml');
		$itemParams = class_exists('JParameter') ? new JParameter('') : new JRegistry('');

		foreach ($xml->document->params as $paramGroup)
		{
			foreach ($paramGroup->param as $param)
			{
				if ($param->attributes('type') != 'spacer' && $param->attributes('name'))
				{
					$itemParams->set($param->attributes('name'), $param->attributes('default'));
				}
			}
		}
		$itemParams = $itemParams->toString();

		$query = "SELECT id, name FROM #__k2_tags";
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		if (is_null($tags))
			$tags = array();

		foreach ($sections as $section)
		{
			$K2Category = JTable::getInstance('K2Category', 'Table');
			$K2Category->name = $section->title;
			$K2Category->alias = $section->title;
			$K2Category->description = $section->description;
			$K2Category->parent = 0;
			$K2Category->published = $section->published;
			$K2Category->access = $section->access;
			$K2Category->ordering = $section->ordering;
			$K2Category->image = $section->image;
			$K2Category->trash = 0;
			$K2Category->params = $categoryParams;
			$K2Category->check();
			$K2Category->store();
			if (JFile::exists(JPATH_SITE.DS.'images'.DS.'stories'.DS.$section->image))
			{
				JFile::copy(JPATH_SITE.DS.'images'.DS.'stories'.DS.$section->image, JPATH_SITE.DS.'media'.DS.'k2'.DS.'categories'.DS.$K2Category->image);
			}
			$query = "SELECT * FROM #__categories WHERE section = ".(int)$section->id;
			$db->setQuery($query);
			$categories = $db->loadObjectList();

			foreach ($categories as $category)
			{
				$K2Subcategory = JTable::getInstance('K2Category', 'Table');
				$K2Subcategory->name = $category->title;
				$K2Subcategory->alias = $category->title;
				$K2Subcategory->description = $category->description;
				$K2Subcategory->parent = $K2Category->id;
				$K2Subcategory->published = $category->published;
				$K2Subcategory->access = $category->access;
				$K2Subcategory->ordering = $category->ordering;
				$K2Subcategory->image = $category->image;
				$K2Subcategory->trash = 0;
				$K2Subcategory->params = $categoryParams;
				$K2Subcategory->check();
				$K2Subcategory->store();
				if (JFile::exists(JPATH_SITE.DS.'images'.DS.'stories'.DS.$category->image))
				{
					JFile::copy(JPATH_SITE.DS.'images'.DS.'stories'.DS.$category->image, JPATH_SITE.DS.'media'.DS.'k2'.DS.'categories'.DS.$K2Subcategory->image);
				}

				$query = "SELECT article.*, xref.content_id
				FROM #__content AS article
				LEFT JOIN #__content_frontpage AS xref ON article.id = xref.content_id
				WHERE catid = ".(int)$category->id;
				$db->setQuery($query);
				$items = $db->loadObjectList();

				foreach ($items as $item)
				{

					$K2Item = JTable::getInstance('K2Item', 'Table');
					$K2Item->title = $item->title;
					$K2Item->alias = $item->title;
					$K2Item->catid = $K2Subcategory->id;
					if ($item->state < 0)
					{
						$K2Item->trash = 1;
					}
					else
					{
						$K2Item->trash = 0;
						$K2Item->published = $item->state;
					}
					$K2Item->featured = ($item->content_id) ? 1 : 0;
					$K2Item->introtext = $item->introtext;
					$K2Item->fulltext = $item->fulltext;
					$K2Item->created = $item->created;
					$K2Item->created_by = $item->created_by;
					$K2Item->created_by_alias = $item->created_by_alias;
					$K2Item->modified = $item->modified;
					$K2Item->modified_by = $item->modified_by;
					$K2Item->publish_up = $item->publish_up;
					$K2Item->publish_down = $item->publish_down;
					$K2Item->access = $item->access;
					$K2Item->ordering = $item->ordering;
					$K2Item->hits = $item->hits;
					$K2Item->metadesc = $item->metadesc;
					$K2Item->metadata = $item->metadata;
					$K2Item->metakey = $item->metakey;
					$K2Item->params = $itemParams;
					$K2Item->check();
					if ($preserveItemIDs)
					{
						$K2Item->id = $item->id;
						$db->insertObject('#__k2_items', $K2Item);
					}
					else
					{
						$K2Item->store();
					}

					if (!empty($item->metakey))
					{
						$itemTags = explode(',', $item->metakey);
						foreach ($itemTags as $itemTag)
						{
							$itemTag = JString::trim($itemTag);
							if (in_array($itemTag, JArrayHelper::getColumn($tags, 'name')))
							{

								$query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($itemTag);
								$db->setQuery($query);
								$id = $db->loadResult();
								$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$id}, {$K2Item->id})";
								$db->setQuery($query);
								$db->query();
							}
							else
							{
								$K2Tag = JTable::getInstance('K2Tag', 'Table');
								$K2Tag->name = $itemTag;
								$K2Tag->published = 1;
								$K2Tag->store();
								$tags[] = $K2Tag;
								$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$K2Tag->id}, {$K2Item->id})";
								$db->setQuery($query);
								$db->query();
							}
						}
					}
				}

			}

		}

		// Handle uncategorized articles
		$query = "SELECT * FROM #__content WHERE sectionid = 0";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if ($items)
		{
			$K2Uncategorised = JTable::getInstance('K2Category', 'Table');
			$K2Uncategorised->name = 'Uncategorized';
			$K2Uncategorised->alias = 'Uncategorized';
			$K2Uncategorised->parent = 0;
			$K2Uncategorised->published = 1;
			$K2Uncategorised->access = 0;
			$K2Uncategorised->ordering = 0;
			$K2Uncategorised->trash = 0;
			$K2Uncategorised->params = $categoryParams;
			$K2Uncategorised->check();
			$K2Uncategorised->store();

			foreach ($items as $item)
			{

				$K2Item = JTable::getInstance('K2Item', 'Table');
				$K2Item->title = $item->title;
				$K2Item->alias = $item->title;
				$K2Item->catid = $K2Uncategorised->id;
				if ($item->state < 0)
				{
					$K2Item->trash = 1;
				}
				else
				{
					$K2Item->trash = 0;
					$K2Item->published = $item->state;
				}
				$K2Item->introtext = $item->introtext;
				$K2Item->fulltext = $item->fulltext;
				$K2Item->created = $item->created;
				$K2Item->created_by = $item->created_by;
				$K2Item->created_by_alias = $item->created_by_alias;
				$K2Item->modified = $item->modified;
				$K2Item->modified_by = $item->modified_by;
				$K2Item->publish_up = $item->publish_up;
				$K2Item->publish_down = $item->publish_down;
				$K2Item->access = $item->access;
				$K2Item->ordering = $item->ordering;
				$K2Item->hits = $item->hits;
				$K2Item->metadesc = $item->metadesc;
				$K2Item->metadata = $item->metadata;
				$K2Item->metakey = $item->metakey;
				$K2Item->params = $itemParams;
				$K2Item->check();
				if ($preserveItemIDs)
				{
					$K2Item->id = $item->id;
					$db->insertObject('#__k2_items', $K2Item);
				}
				else
				{
					$K2Item->store();
				}

				if (!empty($item->metakey))
				{
					$itemTags = explode(',', $item->metakey);
					foreach ($itemTags as $itemTag)
					{
						$itemTag = JString::trim($itemTag);
						if (in_array($itemTag, JArrayHelper::getColumn($tags, 'name')))
						{

							$query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($itemTag);
							$db->setQuery($query);
							$id = $db->loadResult();
							$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$id}, {$K2Item->id})";
							$db->setQuery($query);
							$db->query();
						}
						else
						{
							$K2Tag = JTable::getInstance('K2Tag', 'Table');
							$K2Tag->name = $itemTag;
							$K2Tag->published = 1;
							$K2Tag->store();
							$tags[] = $K2Tag;
							$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$K2Tag->id}, {$K2Item->id})";
							$db->setQuery($query);
							$db->query();
						}
					}
				}
			}
		}
		$mainframe->enqueueMessage(JText::_('K2_IMPORT_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function importJ16()
	{

		jimport('joomla.filesystem.file');
		jimport('joomla.html.parameter');
		jimport('joomla.utilities.xmlelement');
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = "SELECT COUNT(*) FROM #__k2_categories";
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result)
		{
			$preserveCategoryIDs = false;
		}
		else
		{
			$preserveCategoryIDs = true;
		}

		$query = "SELECT COUNT(*) FROM #__k2_items";
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result)
		{
			$preserveItemIDs = false;
		}
		else
		{
			$preserveItemIDs = true;
		}
		$xml = new JXMLElement(JFile::read(JPATH_COMPONENT.DS.'models'.DS.'category.xml'));
		$categoryParams = class_exists('JParameter') ? new JParameter('') : new JRegistry('');
		foreach ($xml->params as $paramGroup)
		{
			foreach ($paramGroup->param as $param)
			{
				if ((string)$param->attributes()->type != 'spacer' && (string)$param->attributes()->name)
				{
					$categoryParams->set((string)$param->attributes()->name, (string)$param->attributes()->default);
				}
			}
		}
		$categoryParams = $categoryParams->toString();

		$xml = new JXMLElement(JFile::read(JPATH_COMPONENT.DS.'models'.DS.'item.xml'));
		$itemParams = class_exists('JParameter') ? new JParameter('') : new JRegistry('');
		foreach ($xml->params as $paramGroup)
		{
			foreach ($paramGroup->param as $param)
			{
				if ((string)$param->attributes()->type != 'spacer' && (string)$param->attributes()->name)
				{
					$itemParams->set((string)$param->attributes()->name, (string)$param->attributes()->default);
				}
			}
		}
		$itemParams = $itemParams->toString();

		$query = "SELECT id, name FROM #__k2_tags";
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		if (is_null($tags))
			$tags = array();

		$query = "SELECT * FROM #__categories WHERE extension = 'com_content'";
		$db->setQuery($query);
		$categories = $db->loadObjectList();
		$mapping = array();
		foreach ($categories as $category)
		{
			$category->params = json_decode($category->params);
			$category->image = $category->params->image;
			$K2Category = JTable::getInstance('K2Category', 'Table');
			$K2Category->name = $category->title;
			$K2Category->alias = $category->title;
			$K2Category->description = $category->description;
			$K2Category->parent = $category->parent_id;
			if ($K2Category->parent == 1)
			{
				$K2Category->parent = 0;
			}
			$K2Category->published = $category->published;
			$K2Category->access = $category->access;
			$K2Category->ordering = $K2Category->getNextOrder('parent='.(int)$category->parent_id);
			$K2Category->image = basename($category->image);
			$K2Category->trash = 0;
			$K2Category->language = $category->language;
			$K2Category->params = $categoryParams;
			$K2Category->check();
			if ($preserveCategoryIDs)
			{
				$K2Category->id = $category->id;
				$db->insertObject('#__k2_categories', $K2Category);
			}
			else
			{
				$K2Category->store();
				$mapping[$category->id] = $K2Category->id;

			}

			if ($K2Category->image && JFile::exists(realpath(JPATH_SITE.DS.$category->image)))
			{
				JFile::copy(realpath(JPATH_SITE.DS.$category->image), JPATH_SITE.DS.'media'.DS.'k2'.DS.'categories'.DS.$K2Category->image);
			}
			$query = "SELECT article.*, xref.content_id
				FROM #__content AS article
				LEFT JOIN #__content_frontpage AS xref ON article.id = xref.content_id
				WHERE catid = ".(int)$category->id;
			$db->setQuery($query);
			$items = $db->loadObjectList();

			foreach ($items as $item)
			{
				$K2Item = JTable::getInstance('K2Item', 'Table');
				$K2Item->title = $item->title;
				$K2Item->alias = $item->title;
				$K2Item->catid = $K2Category->id;
				if ($item->state < 0)
				{
					$K2Item->trash = 1;
				}
				else
				{
					$K2Item->trash = 0;
				}
				$K2Item->published = 1;
				if ($item->state == 0)
				{
					$K2Item->published = 0;
				}
				$K2Item->featured = ($item->content_id) ? 1 : 0;
				$K2Item->introtext = $item->introtext;
				$K2Item->fulltext = $item->fulltext;
				$K2Item->created = $item->created;
				$K2Item->created_by = $item->created_by;
				$K2Item->created_by_alias = $item->created_by_alias;
				$K2Item->modified = $item->modified;
				$K2Item->modified_by = $item->modified_by;
				$K2Item->publish_up = $item->publish_up;
				$K2Item->publish_down = $item->publish_down;
				$K2Item->access = $item->access;
				$K2Item->ordering = $item->ordering;
				$K2Item->hits = $item->hits;
				$K2Item->metadesc = $item->metadesc;
				$K2Item->metadata = $item->metadata;
				$K2Item->metakey = $item->metakey;
				$K2Item->params = $itemParams;
				$K2Item->language = $item->language;
				$K2Item->check();

				if ($preserveItemIDs)
				{
					$K2Item->id = $item->id;
					$db->insertObject('#__k2_items', $K2Item);
				}
				else
				{
					$K2Item->store();
				}

				$item->tags = array();
				if(class_exists('JHelperTags')) {
					$tagsHelper = new JHelperTags;
					$tagsHelper->getItemTags('com_content.article', $item->id);
					$tags = $tagsHelper->itemTags;
					foreach($tags as $tag) {
						$item->tags[] = $tag->title;
					}
				}

				if (!empty($item->metakey) || count($item->tags))
				{
					$itemTags = array_merge(explode(',', $item->metakey), $item->tags);
					$itemTags = array_filter($itemTags);
					$itemTags = array_unique($itemTags);
					foreach ($itemTags as $itemTag)
					{
						$itemTag = JString::trim($itemTag);
						if($itemTag)
						{
							if (in_array($itemTag, JArrayHelper::getColumn($tags, 'name')))
							{

								$query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($itemTag);
								$db->setQuery($query);
								$id = $db->loadResult();
								if($id)
								{
									$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$id}, {$K2Item->id})";
									$db->setQuery($query);
									$db->query();
								}
							}
							else
							{
								$K2Tag = JTable::getInstance('K2Tag', 'Table');
								$K2Tag->name = $itemTag;
								$K2Tag->published = 1;
								$K2Tag->store();
								$tags[] = $K2Tag;
								$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$K2Tag->id}, {$K2Item->id})";
								$db->setQuery($query);
								$db->query();
							}
						}

					}
				}
			}

		}

		foreach ($mapping as $oldID => $newID)
		{
			$query = "UPDATE #__k2_categories SET parent=".$newID." WHERE parent=".$oldID;
			$db->setQuery($query);
			$db->query();
		}
		$mainframe->enqueueMessage(JText::_('K2_IMPORT_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function move()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$catid = JRequest::getInt('moveCategories');
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			$row->catid = $catid;
			$row->ordering = $row->getNextOrder('catid = '.(int)$row->catid.' AND published = 1');
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->enqueueMessage(JText::_('K2_MOVE_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=items');

	}

	function getItemsAuthors()
	{
		$db = $this->getDBO();
		$query = "SELECT id, name, block FROM #__users WHERE id IN(SELECT DISTINCT(created_by) FROM #__k2_items) ORDER BY name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function saveBatch()
	{
		$application = JFactory::getApplication();
		$cid = JRequest::getVar('cid');
		$batchMode = JRequest::getCmd('batchMode');
		$catid = JRequest::getInt('batchCategory');
		$access = JRequest::getCmd('batchAccess');
		$author = JRequest::getInt('batchAuthor');
		$language = JRequest::getCmd('batchLanguage');
		if($batchMode == 'clone'){
			$cid = $this->copy(true);
		}
		foreach ($cid as $id)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($id);
			if($catid)
			{
				$row->catid = $catid;
				$row->ordering = $row->getNextOrder('catid = '.(int)$row->catid.' AND published = 1');
			}
			if($access)
			{
				$row->access = $access;
			}
			if($author)
			{
				$row->created_by = $author;
				$row->created_by_alias = '';
			}
			if($language)
			{
				$row->language = $language;
			}
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$application->enqueueMessage(JText::_('K2_BATCH_COMPLETED'));
		$application->redirect('index.php?option=com_k2&view=items');

	}

}
