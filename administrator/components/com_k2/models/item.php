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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelItem extends K2Model
{

	function getData()
	{

		$cid = JRequest::getVar('cid');
		$row = JTable::getInstance('K2Item', 'Table');
		$row->load($cid);
		return $row;
	}

	function save($front = false)
	{

		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'class.upload.php');
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$row = JTable::getInstance('K2Item', 'Table');
		$params = JComponentHelper::getParams('com_k2');
		$nullDate = $db->getNullDate();

		if (!$row->bind(JRequest::get('post')))
		{
			$mainframe->enqueueMessage($row->getError(), 'error');
			$mainframe->redirect('index.php?option=com_k2&view=items');
		}

		if ($front && $row->id == NULL)
		{
			JLoader::register('K2HelperPermissions', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'permissions.php');
			if (!K2HelperPermissions::canAddItem($row->catid))
			{
				$mainframe->enqueueMessage(JText::_('K2_YOU_ARE_NOT_ALLOWED_TO_POST_TO_THIS_CATEGORY_SAVE_FAILED'), 'error');
				$mainframe->redirect('index.php?option=com_k2&view=item&task=add&tmpl=component');
			}
		}

		$isNew = ($row->id) ? false : true;

		// If we are in front-end and the item is not new we need to get it's current published state.
		if (!$isNew && $front)
		{
			$id = JRequest::getInt('id');
			$currentRow = JTable::getInstance('K2Item', 'Table');
			$currentRow->load($id);
			$isAlreadyPublished = $currentRow->published;
			$currentFeaturedState = $currentRow->featured;
		}

		if ($params->get('mergeEditors'))
		{
			$text = JRequest::getVar('text', '', 'post', 'string', 2);
			if ($params->get('xssFiltering'))
			{
				$filter = new JFilterInput( array(), array(), 1, 1, 0);
				$text = $filter->clean($text);
			}
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $text);
			if ($tagPos == 0)
			{
				$row->introtext = $text;
				$row->fulltext = '';
			}
			else
				list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		}
		else
		{
			$row->introtext = JRequest::getVar('introtext', '', 'post', 'string', 2);
			$row->fulltext = JRequest::getVar('fulltext', '', 'post', 'string', 2);
			if ($params->get('xssFiltering'))
			{
				$filter = new JFilterInput( array(), array(), 1, 1, 0);
				$row->introtext = $filter->clean($row->introtext);
				$row->fulltext = $filter->clean($row->fulltext);
			}
		}

		if ($row->id)
		{
			$datenow = JFactory::getDate();
			$row->modified = K2_JVERSION == '15' ? $datenow->toMySQL() : $datenow->toSql();
			$row->modified_by = $user->get('id');
		}
		else
		{
			$row->ordering = $row->getNextOrder("catid = ".(int)$row->catid." AND trash = 0");
			if ($row->featured)
				$row->featured_ordering = $row->getNextOrder("featured = 1 AND trash = 0", 'featured_ordering');
		}

		$row->created_by = $row->created_by ? $row->created_by : $user->get('id');

		if ($front)
		{
			$K2Permissions = K2Permissions::getInstance();
			if (!$K2Permissions->permissions->get('editAll'))
			{
				$row->created_by = $user->get('id');
			}
		}

		if ($row->created && strlen(trim($row->created)) <= 10)
		{
			$row->created .= ' 00:00:00';
		}

		$config = JFactory::getConfig();
		$tzoffset = K2_JVERSION == '30' ? $config->get('offset') : $config->getValue('config.offset');
		$date = JFactory::getDate($row->created, $tzoffset);
		$row->created = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();

		if (strlen(trim($row->publish_up)) <= 10)
		{
			$row->publish_up .= ' 00:00:00';
		}

		$date = JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();

		if (trim($row->publish_down) == JText::_('K2_NEVER') || trim($row->publish_down) == '')
		{
			$row->publish_down = $nullDate;
		}
		else
		{
			if (strlen(trim($row->publish_down)) <= 10)
			{
				$row->publish_down .= ' 00:00:00';
			}
			$date = JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();
		}

		$metadata = JRequest::getVar('meta', null, 'post', 'array');
		if (is_array($metadata))
		{
			$txt = array();
			foreach ($metadata as $k => $v)
			{
				if ($k == 'description')
				{
					$row->metadesc = $v;
				}
				elseif ($k == 'keywords')
				{
					$row->metakey = $v;
				}
				else
				{
					$txt[] = "$k=$v";
				}
			}
			$row->metadata = implode("\n", $txt);
		}

		if (!$row->check())
		{
			$mainframe->enqueueMessage($row->getError(), 'error');
			$mainframe->redirect('index.php?option=com_k2&view=item&cid='.$row->id);
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$result = $dispatcher->trigger('onBeforeK2Save', array(
			&$row,
			$isNew
		));
		if (in_array(false, $result, true))
		{
			JError::raiseError(500, $row->getError());
			return false;
		}

		//Trigger the finder before save event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');
		$results = $dispatcher->trigger('onFinderBeforeSave', array(
			'com_k2.item',
			$row,
			$isNew
		));

		// Try to save the video if there is no need to wait for item ID
		if (!JRequest::getBool('del_video'))
		{
			if (!isset($files['video']))
			{

				if (JRequest::getVar('remoteVideo'))
				{
					$fileurl = JRequest::getVar('remoteVideo');
					$filetype = JFile::getExt($fileurl);
					$row->video = '{'.$filetype.'remote}'.$fileurl.'{/'.$filetype.'remote}';
				}

				if (JRequest::getVar('videoID'))
				{
					$provider = JRequest::getWord('videoProvider');
					$videoID = JRequest::getVar('videoID');
					$row->video = '{'.$provider.'}'.$videoID.'{/'.$provider.'}';
				}

				if (JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW))
				{
					$row->video = JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW);
				}
			}
		}

		// JoomFish! Front-end editing compatibility
		if ($mainframe->isSite() && JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php'))
		{
			if (version_compare(phpversion(), '5.0') < 0)
			{
				$tmpRow = $row;
			}
			else
			{
				$tmpRow = clone($row);
			}
		}

		if (!$row->store())
		{
			$mainframe->enqueueMessage($row->getError(), 'error');
			$mainframe->redirect('index.php?option=com_k2&view=items');
		}

		// JoomFish! Front-end editing compatibility
		if ($mainframe->isSite() && JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php'))
		{
			$itemID = $row->id;
			$row = $tmpRow;
			$row->id = $itemID;
		}

		if (!$params->get('disableCompactOrdering'))
		{
			$row->reorder("catid = ".(int)$row->catid." AND trash = 0");
		}
		if ($row->featured && !$params->get('disableCompactOrdering'))
		{
			$row->reorder("featured = 1 AND trash = 0", 'featured_ordering');
		}
		$files = JRequest::get('files');

		//Image
		if ((int)$params->get('imageMemoryLimit'))
		{
			ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
		}
		$existingImage = JRequest::getVar('existingImage');
		if (($files['image']['error'] === 0 || $existingImage) && !JRequest::getBool('del_image'))
		{

			if ($files['image']['error'] === 0)
			{
				$image = $files['image'];
			}
			else
			{
				$image = JPATH_SITE.DS.JPath::clean($existingImage);
			}

			$handle = new Upload($image);
			$handle->allowed = array('image/*');
			$handle->forbidden = array('image/tiff');

			if ($handle->file_is_image && $handle->uploaded)
			{
				//Image params
				$category = JTable::getInstance('K2Category', 'Table');
				$category->load($row->catid);
				$cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

				if ($cparams->get('inheritFrom'))
				{
					$masterCategoryID = $cparams->get('inheritFrom');
					$query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
					$db->setQuery($query, 0, 1);
					$masterCategory = $db->loadObject();
					$cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
				}

				$params->merge($cparams);

				//Original image
				$savepath = JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'src';
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = 100;
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = md5("Image".$row->id);
				$handle->Process($savepath);

				$filename = $handle->file_dst_name_body;
				$savepath = JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache';

				//XLarge image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_XL';
				if (JRequest::getInt('itemImageXL'))
				{
					$imageWidth = JRequest::getInt('itemImageXL');
				}
				else
				{
					$imageWidth = $params->get('itemImageXL', '800');
				}
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				//Large image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_L';
				if (JRequest::getInt('itemImageL'))
				{
					$imageWidth = JRequest::getInt('itemImageL');
				}
				else
				{
					$imageWidth = $params->get('itemImageL', '600');
				}
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				//Medium image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_M';
				if (JRequest::getInt('itemImageM'))
				{
					$imageWidth = JRequest::getInt('itemImageM');
				}
				else
				{
					$imageWidth = $params->get('itemImageM', '400');
				}
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				//Small image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_S';
				if (JRequest::getInt('itemImageS'))
				{
					$imageWidth = JRequest::getInt('itemImageS');
				}
				else
				{
					$imageWidth = $params->get('itemImageS', '200');
				}
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				//XSmall image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_XS';
				if (JRequest::getInt('itemImageXS'))
				{
					$imageWidth = JRequest::getInt('itemImageXS');
				}
				else
				{
					$imageWidth = $params->get('itemImageXS', '100');
				}
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				//Generic image
				$handle->image_resize = true;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->jpeg_quality = $params->get('imagesQuality');
				$handle->file_auto_rename = false;
				$handle->file_overwrite = true;
				$handle->file_new_name_body = $filename.'_Generic';
				$imageWidth = $params->get('itemImageGeneric', '300');
				$handle->image_x = $imageWidth;
				$handle->Process($savepath);

				if ($files['image']['error'] === 0)
					$handle->Clean();

			}
			else
			{
				$mainframe->enqueueMessage(JText::_('K2_IMAGE_WAS_NOT_UPLOADED'), 'notice');
			}

		}

		if (JRequest::getBool('del_image'))
		{

			$current = JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);
			$filename = md5("Image".$current->id);

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filename.'.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filename.'.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XS.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XS.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_S.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_S.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_M.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_M.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_L.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_L.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XL.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XL.jpg');
			}

			if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_Generic.jpg'))
			{
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_Generic.jpg');
			}

			$row->image_caption = '';
			$row->image_credits = '';

		}

		//Attachments
		$attachments = JRequest::getVar('attachment_file', NULL, 'FILES', 'array');
		$attachments_names = JRequest::getVar('attachment_name', '', 'POST', 'array');
		$attachments_titles = JRequest::getVar('attachment_title', '', 'POST', 'array');
		$attachments_title_attributes = JRequest::getVar('attachment_title_attribute', '', 'POST', 'array');
		$attachments_existing_files = JRequest::getVar('attachment_existing_file', '', 'POST', 'array');

		$attachmentFiles = array();

		if (count($attachments))
		{

			foreach ($attachments as $k => $l)
			{
				foreach ($l as $i => $v)
				{
					if (!array_key_exists($i, $attachmentFiles))
						$attachmentFiles[$i] = array();
					$attachmentFiles[$i][$k] = $v;
				}

			}

			$path = $params->get('attachmentsFolder', NULL);
			if (is_null($path))
			{
				$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
			}
			else
			{
				$savepath = $path;
			}

			$counter = 0;

			foreach ($attachmentFiles as $key => $file)
			{

				if ($file["tmp_name"] || $attachments_existing_files[$key])
				{
					if ($attachments_existing_files[$key])
					{
						$src = JPATH_SITE.DS.JPath::clean($attachments_existing_files[$key]);
						$copyName = basename($src);
						$dest = $savepath.DS.$copyName;
						if (JFile::exists($dest))
						{
							$existingFileName = JFile::getName($dest);
							$ext = JFile::getExt($existingFileName);
							$basename = JFile::stripExt($existingFileName);
							$newFilename = $basename.'_'.time().'.'.$ext;
							$copyName = $newFilename;
							$dest = $savepath.DS.$newFilename;
						}
						JFile::copy($src, $dest);
						$attachment = JTable::getInstance('K2Attachment', 'Table');
						$attachment->itemID = $row->id;
						$attachment->filename = $copyName;
						$attachment->title = ( empty($attachments_titles[$counter])) ? $filename : $attachments_titles[$counter];
						$attachment->titleAttribute = ( empty($attachments_title_attributes[$counter])) ? $filename : $attachments_title_attributes[$counter];
						$attachment->store();
					}
					else
					{
						$handle = new Upload($file);
						if ($handle->uploaded)
						{
							$handle->file_auto_rename = true;
							$handle->allowed[] = 'application/x-zip';
							$handle->allowed[] = 'application/download';
							$handle->Process($savepath);
							$filename = $handle->file_dst_name;
							$handle->Clean();
							$attachment = JTable::getInstance('K2Attachment', 'Table');
							$attachment->itemID = $row->id;
							$attachment->filename = $filename;
							$attachment->title = ( empty($attachments_titles[$counter])) ? $filename : $attachments_titles[$counter];
							$attachment->titleAttribute = ( empty($attachments_title_attributes[$counter])) ? $filename : $attachments_title_attributes[$counter];
							$attachment->store();
						}
						else
						{
							$mainframe->enqueueMessage($handle->error, 'error');
							$mainframe->redirect('index.php?option=com_k2&view=items');
						}

					}

				}

				$counter++;
			}

		}

		//Gallery
		$flickrGallery = JRequest::getVar('flickrGallery');
		if ($flickrGallery)
		{
			$row->gallery = '{gallery}'.$flickrGallery.'{/gallery}';
		}

		if (isset($files['gallery']) && $files['gallery']['error'] == 0 && !JRequest::getBool('del_gallery'))
		{
			$handle = new Upload($files['gallery']);
			$handle->file_auto_rename = true;
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries';
			$handle->allowed = array(
				"application/download",
				"application/rar",
				"application/x-rar-compressed",
				"application/arj",
				"application/gnutar",
				"application/x-bzip",
				"application/x-bzip2",
				"application/x-compressed",
				"application/x-gzip",
				"application/x-zip-compressed",
				"application/zip",
				"multipart/x-zip",
				"multipart/x-gzip",
				"application/x-unknown",
				"application/x-zip"
			);

			if ($handle->uploaded)
			{

				$handle->Process($savepath);
				$handle->Clean();

				if (JFolder::exists($savepath.DS.$row->id))
				{
					JFolder::delete($savepath.DS.$row->id);
				}

				if (!JArchive::extract($savepath.DS.$handle->file_dst_name, $savepath.DS.$row->id))
				{
					$mainframe->enqueueMessage(JText::_('K2_GALLERY_UPLOAD_ERROR_CANNOT_EXTRACT_ARCHIVE'), 'error');
					$mainframe->redirect('index.php?option=com_k2&view=items');
				}
				else
				{
					$row->gallery = '{gallery}'.$row->id.'{/gallery}';
				}
				JFile::delete($savepath.DS.$handle->file_dst_name);
				$handle->Clean();

			}
			else
			{
				$mainframe->enqueueMessage($handle->error, 'error');
				$mainframe->redirect('index.php?option=com_k2&view=items');
			}
		}

		if (JRequest::getBool('del_gallery'))
		{

			$current = JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);

			if (JFolder::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$current->id))
			{
				JFolder::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$current->id);
			}
			$row->gallery = '';
		}

		//Video
		if (!JRequest::getBool('del_video'))
		{
			if (isset($files['video']) && $files['video']['error'] == 0)
			{

				$videoExtensions = array(
					"flv",
					"mp4",
					"ogv",
					"webm",
					"f4v",
					"m4v",
					"3gp",
					"3g2",
					"mov",
					"mpeg",
					"mpg",
					"avi",
					"wmv",
					"divx"
				);
				$audioExtensions = array(
					"mp3",
					"aac",
					"m4a",
					"ogg",
					"wma"
				);
				$validExtensions = array_merge($videoExtensions, $audioExtensions);
				$filetype = JFile::getExt($files['video']['name']);

				if (!in_array($filetype, $validExtensions))
				{
					$mainframe->enqueueMessage(JText::_('K2_INVALID_VIDEO_FILE'), 'error');
					$mainframe->redirect('index.php?option=com_k2&view=items');
				}

				if (in_array($filetype, $videoExtensions))
				{
					$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos';
				}
				else
				{
					$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio';
				}

				$filename = JFile::stripExt($files['video']['name']);

				JFile::upload($files['video']['tmp_name'], $savepath.DS.$row->id.'.'.$filetype);
				$filetype = JFile::getExt($files['video']['name']);
				$row->video = '{'.$filetype.'}'.$row->id.'{/'.$filetype.'}';

			}

		}
		else
		{

			$current = JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);

			preg_match_all("#^{(.*?)}(.*?){#", $current->video, $matches, PREG_PATTERN_ORDER);
			$videotype = $matches[1][0];
			$videofile = $matches[2][0];

			if (in_array($videotype, $videoExtensions))
			{
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype))
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype);
			}

			if (in_array($videotype, $audioExtensions))
			{
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype))
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype);
			}

			$row->video = '';
			$row->video_caption = '';
			$row->video_credits = '';
		}

		//Extra fields
		$objects = array();
		$variables = JRequest::get('post', 2);
		foreach ($variables as $key => $value)
		{
			if (( bool )JString::stristr($key, 'K2ExtraField_'))
			{
				$object = new JObject;
				$object->set('id', JString::substr($key, 13));
				if (is_string($value))
				{
					$value = trim($value);
				}
				$object->set('value', $value);
				unset($object->_errors);
				$objects[] = $object;
			}
		}

		$csvFiles = JRequest::get('files');
		foreach ($csvFiles as $key => $file)
		{
			if (( bool )JString::stristr($key, 'K2ExtraField_'))
			{
				$object = new JObject;
				$object->set('id', JString::substr($key, 13));
				$csvFile = $file['tmp_name'][0];
				if (!empty($csvFile) && JFile::getExt($file['name'][0]) == 'csv')
				{
					$handle = @fopen($csvFile, 'r');
					$csvData = array();
					while (($data = fgetcsv($handle, 1000)) !== FALSE)
					{
						$csvData[] = $data;
					}
					fclose($handle);
					$object->set('value', $csvData);
				}
				else
				{
					require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
					$json = new Services_JSON;
					$object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
					if (JRequest::getBool('K2ResetCSV_'.$object->id))
						$object->set('value', null);
				}
				unset($object->_errors);
				$objects[] = $object;
			}
		}

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		$row->extra_fields = $json->encode($objects);

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extrafield.php');
		$extraFieldModel = K2Model::getInstance('ExtraField', 'K2Model');
		$row->extra_fields_search = '';

		foreach ($objects as $object)
		{
			$row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
			$row->extra_fields_search .= ' ';
		}

		//Tags
		if ($user->gid < 24 && $params->get('lockTags'))
			$params->set('taggingSystem', 0);
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__k2_tags_xref WHERE itemID={intval($row->id)}";
		$db->setQuery($query);
		$db->query();

		if ($params->get('taggingSystem'))
		{

			if ($user->gid < 24 && $params->get('lockTags'))
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));

			$tags = JRequest::getVar('tags', NULL, 'POST', 'array');
			if (count($tags))
			{
				$tags = array_unique($tags);
				foreach ($tags as $tag)
				{

					$tag = JString::trim($tag);
					if ($tag)
					{
						$tagID = false;
						$K2Tag = JTable::getInstance('K2Tag', 'Table');
						$K2Tag->name = $tag;
						// Tag has been filtred and does not exist
						if ($K2Tag->check())
						{
							$K2Tag->published = 1;
							if ($K2Tag->store())
							{
								$tagID = $K2Tag->id;
							}
						}
						// Tag has been filtred and exists so try to find it's id
						else if ($K2Tag->name)
						{
							$query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($K2Tag->name);
							$db->setQuery($query);
							$tagID = $db->loadResult();
						}
						if ($tagID)
						{
							$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tagID)}, {intval($row->id)})";
							$db->setQuery($query);
							$db->query();
						}
					}
				}
			}

		}
		else
		{
			$tags = JRequest::getVar('selectedTags', NULL, 'POST', 'array');
			if (count($tags))
			{
				foreach ($tags as $tagID)
				{
					$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tagID)}, {intval($row->id)})";
					$db->setQuery($query);
					$db->query();
				}
			}

		}

		// If we are in front-end check publishing permissions properly.
		if ($front)
		{
			// New items require the "Publish items" permission.
			if ($isNew && $row->published && !K2HelperPermissions::canPublishItem($row->catid))
			{
				$row->published = 0;
				$mainframe->enqueueMessage(JText::_('K2_YOU_DONT_HAVE_THE_PERMISSION_TO_PUBLISH_ITEMS'), 'notice');
			}

			// Existing items require either the "Publish items" or the "Allow editing of already published items" permission.
			if (!$isNew && $row->published)
			{
				$canEditPublished = $isAlreadyPublished && K2HelperPermissions::canEditPublished($row->catid);
				if (!K2HelperPermissions::canPublishItem($row->catid) && (!$canEditPublished))
				{
					$row->published = 0;
					$mainframe->enqueueMessage(JText::_('K2_YOU_DONT_HAVE_THE_PERMISSION_TO_PUBLISH_ITEMS'), 'notice');
				}
			}

			// If user has cannot publish the item then also cannot make it featured
			if (!K2HelperPermissions::canPublishItem($row->catid))
			{
				if ($isNew)
				{
					$row->featured = 0;
				}
				else
				{
					$row->featured = $currentFeaturedState;
				}
			}

		}

		$query = "UPDATE #__k2_items SET video_caption = ".$db->Quote($row->video_caption).", video_credits = ".$db->Quote($row->video_credits).", ";

		if (!is_null($row->video))
		{
			$query .= " video = ".$db->Quote($row->video).", ";
		}
		if (!is_null($row->gallery))
		{
			$query .= " gallery = ".$db->Quote($row->gallery).", ";
		}
		$query .= " extra_fields = ".$db->Quote($row->extra_fields).", extra_fields_search = ".$db->Quote($row->extra_fields_search)." , published = ".$db->Quote($row->published)." WHERE id = ".$row->id;
		$db->setQuery($query);

		if (!$db->query())
		{
			$mainframe->enqueueMessage($db->getErrorMsg(), 'error');
			$mainframe->redirect('index.php?option=com_k2&view=items');
		}

		$row->checkin();

		$cache = JFactory::getCache('com_k2');
		$cache->clean();

		$dispatcher->trigger('onAfterK2Save', array(
			&$row,
			$isNew
		));
		JPluginHelper::importPlugin('content');
		if (K2_JVERSION != '15')
		{
			$dispatcher->trigger('onContentAfterSave', array(
				'com_k2.item',
				&$row,
				$isNew
			));
		}
		else
		{
			$dispatcher->trigger('onAfterContentSave', array(
				&$row,
				$isNew
			));
		}

		//Trigger the finder after save event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');
		$results = $dispatcher->trigger('onFinderAfterSave', array(
			'com_k2.item',
			$row,
			$isNew
		));

		switch (JRequest::getCmd('task'))
		{
			case 'apply' :
				$msg = JText::_('K2_CHANGES_TO_ITEM_SAVED');
				$link = 'index.php?option=com_k2&view=item&cid='.$row->id;
				break;
			case 'saveAndNew' :
				$msg = JText::_('K2_ITEM_SAVED');
				$link = 'index.php?option=com_k2&view=item';
				break;
			case 'save' :
			default :
				$msg = JText::_('K2_ITEM_SAVED');
				if ($front)
					$link = 'index.php?option=com_k2&view=item&task=edit&cid='.$row->id.'&tmpl=component&Itemid='.JRequest::getInt('Itemid');
				else
					$link = 'index.php?option=com_k2&view=items';
				break;
		}
		$mainframe->enqueueMessage($msg);
		$mainframe->redirect($link);
	}

	function cancel()
	{

		$mainframe = JFactory::getApplication();
		$cid = JRequest::getInt('id');

		if ($cid)
		{
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load($cid);
			$row->checkin();
		}
    else
    {
      // Clean up SigPro
      $sigProFolder = JRequest::getCmd('sigProFolder');
      if($sigProFolder && !is_numeric($sigProFolder) && JFolder::exists(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder))
      {
        JFolder::delete(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder);
      }
    }

		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function getVideoProviders()
	{

		if (K2_JVERSION != '15')
		{
			$file = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'jw_allvideos'.DS.'includes'.DS.'sources.php';
		}
		else
		{
			$file = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'includes'.DS.'sources.php';
		}

		jimport('joomla.filesystem.file');
		if (JFile::exists($file))
		{
			require $file;
			$thirdPartyProviders = array_slice($tagReplace, 40);
			$providersTmp = array_keys($thirdPartyProviders);
			$providers = array();
			foreach ($providersTmp as $providerTmp)
			{

				if (stristr($providerTmp, 'google|google.co.uk|google.com.au|google.de|google.es|google.fr|google.it|google.nl|google.pl') !== false)
				{
					$provider = 'google';
				}
				elseif (stristr($providerTmp, 'spike|ifilm') !== false)
				{
					$provider = 'spike';
				}
				else
				{
					$provider = $providerTmp;
				}
				$providers[] = $provider;
			}
			return $providers;
		}
		else
		{
			return array();
		}

	}

	function download()
	{

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		jimport('joomla.filesystem.file');
		$params = JComponentHelper::getParams('com_k2');
		$id = JRequest::getInt('id');

		JPluginHelper::importPlugin('k2');
		$dispatcher = JDispatcher::getInstance();

		$attachment = JTable::getInstance('K2Attachment', 'Table');
		if ($mainframe->isSite())
		{
			$token = JRequest::getVar('id');
			$check = JString::substr($token, JString::strpos($token, '_') + 1);
			$hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($id) : JUtility::getHash($id);
			if ($check != $hash)
			{
				JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			}
		}
		$attachment->load($id);

		// For front-end ensure that user has access to the item
		if ($mainframe->isSite())
		{
			$item = JTable::getInstance('K2Item', 'Table');
			$item->load($attachment->itemID);
			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($item->catid);
			if (!$item->id || !$category->id)
			{
				JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			}

			if (K2_JVERSION == '15' && ($item->access > $user->get('aid', 0) || $category->access > $user->get('aid', 0)))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}

			if (K2_JVERSION != '15' && (!in_array($category->access, $user->getAuthorisedViewLevels()) || !in_array($item->access, $user->getAuthorisedViewLevels())))
			{
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}

		}

		$dispatcher->trigger('onK2BeforeDownload', array(
			&$attachment,
			&$params
		));

		$path = $params->get('attachmentsFolder', NULL);
		if (is_null($path))
		{
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
		}
		else
		{
			$savepath = $path;
		}
		$file = $savepath.DS.$attachment->filename;

		if (JFile::exists($file))
		{
			require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'class.upload.php');
			$handle = new Upload($file);
			$dispatcher->trigger('onK2AfterDownload', array(
				&$attachment,
				&$params
			));
			if ($mainframe->isSite())
			{
				$attachment->hit();
			}
			$len = filesize($file);
			$filename = basename($file);
			ob_end_clean();
			JResponse::clearHeaders();
			JResponse::setHeader('Pragma', 'public', true);
			JResponse::setHeader('Expires', '0', true);
			JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
			JResponse::setHeader('Content-Type', $handle->file_src_mime, true);
			JResponse::setHeader('Content-Disposition', 'attachment; filename='.$filename.';', true);
			JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
			JResponse::setHeader('Content-Length', $len, true);
			JResponse::sendHeaders();
			echo JFile::read($file);

		}
		else
		{
			echo JText::_('K2_FILE_DOES_NOT_EXIST');
		}
		$mainframe->close();
	}

	function getAttachments($itemID)
	{

		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_attachments WHERE itemID=".(int)$itemID;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($row->id) : JUtility::getHash($row->id);
			$row->link = JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$row->id.'_'.$hash);
		}
		return $rows;

	}

	function deleteAttachment()
	{

		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		jimport('joomla.filesystem.file');
		$id = JRequest::getInt('id');
		$itemID = JRequest::getInt('cid');

		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__k2_attachments WHERE itemID={$itemID} AND id={$id}";
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result)
		{
			$mainframe->close();
		}

		$row = JTable::getInstance('K2Attachment', 'Table');
		$row->load($id);

		$path = $params->get('attachmentsFolder', NULL);
		if (is_null($path))
		{
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
		}
		else
		{
			$savepath = $path;
		}

		if (JFile::exists($savepath.DS.$row->filename))
		{
			JFile::delete($savepath.DS.$row->filename);
		}

		$row->delete($id);
		$mainframe->close();
	}

	function getAvailableTags($itemID = NULL)
	{

		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_tags as tags";
		if (!is_null($itemID))
			$query .= " WHERE tags.id NOT IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID=".(int)$itemID.")";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function getCurrentTags($itemID)
	{

		$db = JFactory::getDBO();
		$itemID = (int)$itemID;
		$query = "SELECT tags.* FROM #__k2_tags AS tags JOIN #__k2_tags_xref AS xref ON tags.id = xref.tagID WHERE xref.itemID = ".(int)$itemID." ORDER BY xref.id ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function resetHits()
	{
		$mainframe = JFactory::getApplication();
		$id = JRequest::getInt('id');
		$db = JFactory::getDBO();
		$query = "UPDATE #__k2_items SET hits=0 WHERE id={$id}";
		$db->setQuery($query);
		$db->query();
		if ($mainframe->isAdmin())
			$url = 'index.php?option=com_k2&view=item&cid='.$id;
		else
			$url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
		$mainframe->enqueueMessage(JText::_('K2_SUCCESSFULLY_RESET_ITEM_HITS'));
		$mainframe->redirect($url);
	}

	function resetRating()
	{
		$mainframe = JFactory::getApplication();
		$id = JRequest::getInt('id');
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__k2_rating WHERE itemID={$id}";
		$db->setQuery($query);
		$db->query();
		if ($mainframe->isAdmin())
			$url = 'index.php?option=com_k2&view=item&cid='.$id;
		else
			$url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
		$mainframe->enqueueMessage(JText::_('K2_SUCCESSFULLY_RESET_ITEM_RATING'));
		$mainframe->redirect($url);
	}

	function getRating()
	{
		$id = JRequest::getInt('cid');
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_rating WHERE itemID={$id}";
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();
		return $row;
	}

	function checkSIG()
	{
		$mainframe = JFactory::getApplication();
		if (K2_JVERSION != '15')
		{
			$check = JPATH_PLUGINS.DS.'content'.DS.'jw_sigpro'.DS.'jw_sigpro.php';
		}
		else
		{
			$check = JPATH_PLUGINS.DS.'content'.DS.'jw_sigpro.php';
		}
		if (JFile::exists($check))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function checkAllVideos()
	{
		$mainframe = JFactory::getApplication();
		if (K2_JVERSION != '15')
		{
			$check = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'jw_allvideos.php';
		}
		else
		{
			$check = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos.php';
		}
		if (JFile::exists($check))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function cleanText($text)
	{
		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			$text = JComponentHelper::filterText($text);
		}
		else if (version_compare(JVERSION, '2.5.0', 'lt') && version_compare(JVERSION, '1.6.0', 'ge'))
		{
			JLoader::register('ContentHelper', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'content.php');
			$text = ContentHelper::filterText($text);
		}
		else
		{
			$config = JComponentHelper::getParams('com_content');
			$user = JFactory::getUser();
			$gid = $user->get('gid');
			$filterGroups = $config->get('filter_groups');

			// convert to array if one group selected
			if ((!is_array($filterGroups) && (int)$filterGroups > 0))
			{
				$filterGroups = array($filterGroups);
			}

			if (is_array($filterGroups) && in_array($gid, $filterGroups))
			{
				$filterType = $config->get('filter_type');
				$filterTags = preg_split('#[,\s]+#', trim($config->get('filter_tags')));
				$filterAttrs = preg_split('#[,\s]+#', trim($config->get('filter_attritbutes')));
				switch ($filterType)
				{
					case 'NH' :
						$filter = new JFilterInput();
						break;
					case 'WL' :
						$filter = new JFilterInput($filterTags, $filterAttrs, 0, 0, 0);
						break;
					case 'BL' :
					default :
						$filter = new JFilterInput($filterTags, $filterAttrs, 1, 1);
						break;
				}
				$text = $filter->clean($text);
			}
			elseif (empty($filterGroups) && $gid != '25')
			{
				// no default filtering for super admin (gid=25)
				$filter = new JFilterInput( array(), array(), 1, 1);
				$text = $filter->clean($text);
			}
		}

		return $text;

	}

}
