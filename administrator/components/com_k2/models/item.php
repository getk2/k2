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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelItem extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Item', 'Table');
        $row->load($cid);
        return $row;
    }

    public function save($front = false)
    {
        $app = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.archive');
        require_once(JPATH_SITE.'/media/k2/assets/vendors/verot/class.upload.php/src/class.upload.php');
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $row = JTable::getInstance('K2Item', 'Table');
        $params = JComponentHelper::getParams('com_k2');
        $nullDate = $db->getNullDate();

        // Plugin Events
        JPluginHelper::importPlugin('k2');
        JPluginHelper::importPlugin('content');
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();

        if (!$row->bind(JRequest::get('post'))) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=items');
        }

        if ($front && $row->id == null) {
            JLoader::register('K2HelperPermissions', JPATH_SITE.'/components/com_k2/helpers/permissions.php');
            if (!K2HelperPermissions::canAddItem($row->catid)) {
                $app->enqueueMessage(JText::_('K2_YOU_ARE_NOT_ALLOWED_TO_POST_TO_THIS_CATEGORY_SAVE_FAILED'), 'error');
                $app->redirect('index.php?option=com_k2&view=item&task=add&tmpl=component');
            }
        }

        $isNew = ($row->id) ? false : true;

        // If the item is not new, retrieve its saved data
        $savedRow = new stdClass();
        if (!$isNew) {
            $id = JRequest::getInt('id');
            $savedRow = JTable::getInstance('K2Item', 'Table');
            $savedRow->load($id);
            // Frontend only
            if ($front) {
                $published = $savedRow->published;
                $featured = $savedRow->featured;
            }
        }

        if ($params->get('mergeEditors')) {
            $text = JRequest::getVar('text', '', 'post', 'string', 2);
            if ($params->get('xssFiltering')) {
                $filter = new JFilterInput(array(), array(), 1, 1, 0);
                $text = $filter->clean($text);
            }
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $tagPos = preg_match($pattern, $text);
            if ($tagPos == 0) {
                $row->introtext = $text;
                $row->fulltext = '';
            } else {
                list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
            }
        } else {
            $row->introtext = JRequest::getVar('introtext', '', 'post', 'string', 2);
            $row->fulltext = JRequest::getVar('fulltext', '', 'post', 'string', 2);
            if ($params->get('xssFiltering')) {
                $filter = new JFilterInput(array(), array(), 1, 1, 0);
                $row->introtext = $filter->clean($row->introtext);
                $row->fulltext = $filter->clean($row->fulltext);
            }
        }

        if ($row->id) {
            $datenow = JFactory::getDate();
            $row->modified = (K2_JVERSION == '15') ? $datenow->toMySQL() : $datenow->toSql();
            $row->modified_by = $user->get('id');
        } else {
            $row->ordering = $row->getNextOrder("catid = ".(int) $row->catid." AND trash = 0");
            if ($row->featured) {
                $row->featured_ordering = $row->getNextOrder("featured = 1 AND trash = 0", 'featured_ordering');
            }
        }

        // Author
        $row->created_by = ($row->created_by) ? $row->created_by : $user->get('id');
        if ($front) {
            $K2Permissions = K2Permissions::getInstance();
            if (!$K2Permissions->permissions->get('editAll')) {
                $row->created_by = $user->get('id');
            }
        }

        if ($row->created && strlen(trim($row->created)) <= 10) {
            $row->created .= ' 00:00:00';
        }

        $config = JFactory::getConfig();
        $tzoffset = K2_JVERSION == '30' ? $config->get('offset') : $config->getValue('config.offset');
        $date = JFactory::getDate($row->created, $tzoffset);
        $row->created = (K2_JVERSION == '15') ? $date->toMySQL() : $date->toSql();

        if (strlen(trim($row->publish_up)) <= 10) {
            $row->publish_up .= ' 00:00:00';
        }

        $date = JFactory::getDate($row->publish_up, $tzoffset);
        $row->publish_up = (K2_JVERSION == '15') ? $date->toMySQL() : $date->toSql();

        if (trim($row->publish_down) == JText::_('K2_NEVER') || trim($row->publish_down) == '') {
            $row->publish_down = $nullDate;
        } else {
            if (strlen(trim($row->publish_down)) <= 10) {
                $row->publish_down .= ' 00:00:00';
            }
            $date = JFactory::getDate($row->publish_down, $tzoffset);
            $row->publish_down = (K2_JVERSION == '15') ? $date->toMySQL() : $date->toSql();
        }

        $metadata = JRequest::getVar('meta', null, 'post', 'array');
        if (is_array($metadata)) {
            $txt = array();
            foreach ($metadata as $k => $v) {
                if ($k == 'description') {
                    $row->metadesc = $v;
                } elseif ($k == 'keywords') {
                    $row->metakey = $v;
                } else {
                    $txt[] = "$k=$v";
                }
            }
            $row->metadata = implode("\n", $txt);
        }

        if (!$row->check()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=item&cid='.$row->id);
        }

        // Trigger K2 plugins
        $result = $dispatcher->trigger('onBeforeK2Save', array(&$row, $isNew));

        if (in_array(false, $result, true)) {
            JError::raiseError(500, $row->getError());
            return false;
        }

        // Trigger content & finder plugins before the save event
        $dispatcher->trigger('onContentBeforeSave', array('com_k2.item', $row, $isNew));
        $dispatcher->trigger('onFinderBeforeSave', array('com_k2.item', $row, $isNew));

        // JoomFish front-end editing compatibility
        if ($app->isSite() && JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php')) {
            if (version_compare(phpversion(), '5.0') < 0) {
                $tmpRow = $row;
            } else {
                $tmpRow = clone($row);
            }
        }

        if (!$row->store()) {
            $app->enqueueMessage($row->getError(), 'error');
            $app->redirect('index.php?option=com_k2&view=items');
        }

        // JoomFish front-end editing compatibility
        if ($app->isSite() && JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/joomfish.php')) {
            $itemID = $row->id;
            $row = $tmpRow;
            $row->id = $itemID;
        }

        if (!$params->get('disableCompactOrdering')) {
            $row->reorder("catid = ".(int) $row->catid." AND trash = 0");
        }
        if ($row->featured && !$params->get('disableCompactOrdering')) {
            $row->reorder("featured = 1 AND trash = 0", 'featured_ordering');
        }

        // Tags
        if ($params->get('taggingSystem') === '0' || $params->get('taggingSystem') === '1') {
            // B/C - Convert old options
            $whichTaggingSystem = ($params->get('taggingSystem')) ? 'free' : 'selection';
            $params->set('taggingSystem', $whichTaggingSystem);
        }
        if ($user->gid < 24 && $params->get('lockTags')) {
            $params->set('taggingSystem', 'selection');
        }
        $db->setQuery("DELETE FROM #__k2_tags_xref WHERE itemID=".(int) $row->id);
        $db->query();

        if ($params->get('taggingSystem') == 'free') {
            if ($user->gid < 24 && $params->get('lockTags')) {
                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }

            $tags = JRequest::getVar('tags', null, 'POST', 'array');
            if (is_array($tags) && count($tags)) {
                $tags = array_unique($tags);
                foreach ($tags as $tag) {
                    $tag = JString::trim($tag);
                    if ($tag) {
                        $tagID = false;
                        $K2Tag = JTable::getInstance('K2Tag', 'Table');
                        $K2Tag->name = $tag;
                        // Tag has been filtered and does not exist
                        if ($K2Tag->check()) {
                            $K2Tag->published = 1;
                            if ($K2Tag->store()) {
                                $tagID = $K2Tag->id;
                            }
                        }
                        // Tag has been filtered and it exists so try to find its ID
                        elseif ($K2Tag->name) {
                            $db->setQuery("SELECT id FROM #__k2_tags WHERE name=".$db->Quote($K2Tag->name));
                            $tagID = $db->loadResult();
                        }
                        if ($tagID) {
                            $db->setQuery("INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, ".(int) $tagID.", ".(int) $row->id.")");
                            $db->query();
                        }
                    }
                }
            }
        } else {
            $tags = JRequest::getVar('selectedTags', null, 'POST', 'array');
            if (is_array($tags) && count($tags)) {
                foreach ($tags as $tagID) {
                    $db->setQuery("INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, ".(int) $tagID.", ".(int) $row->id.")");
                    $db->query();
                }
            }
        }

        // File Uploads
        $files = JRequest::get('files');

        // Image
        if ((int) $params->get('imageMemoryLimit')) {
            ini_set('memory_limit', (int) $params->get('imageMemoryLimit').'M');
        }

        $existingImage = JRequest::getVar('existingImage');

        if (($files['image']['error'] == 0 || $existingImage) && !JRequest::getBool('del_image')) {
            if ($files['image']['error'] == 0) {
                $image = $files['image'];
            } else {
                $image = JPATH_SITE.'/'.JPath::clean($existingImage);
            }

            $handle = new Upload($image);
            $handle->allowed = array('image/*');
            $handle->forbidden = array('image/tiff');

            if ($handle->file_is_image && $handle->uploaded) {
                // Image params
                $category = JTable::getInstance('K2Category', 'Table');
                $category->load($row->catid);
                $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

                if ($cparams->get('inheritFrom')) {
                    $masterCategoryID = $cparams->get('inheritFrom');
                    $db->setQuery("SELECT * FROM #__k2_categories WHERE id=".(int) $masterCategoryID, 0, 1);
                    $masterCategory = $db->loadObject();
                    $cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                }

                $params->merge($cparams);

                // Original image
                $savepath = JPATH_SITE.'/media/k2/items/src';
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = 100;
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = md5("Image".$row->id);
                $handle->process($savepath);

                $filename = $handle->file_dst_name_body;
                $savepath = JPATH_SITE.'/media/k2/items/cache';

                // XLarge image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_XL';
                if (JRequest::getInt('itemImageXL')) {
                    $imageWidth = JRequest::getInt('itemImageXL');
                } else {
                    $imageWidth = $params->get('itemImageXL', '800');
                }
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                // Large image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_L';
                if (JRequest::getInt('itemImageL')) {
                    $imageWidth = JRequest::getInt('itemImageL');
                } else {
                    $imageWidth = $params->get('itemImageL', '600');
                }
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                // Medium image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_M';
                if (JRequest::getInt('itemImageM')) {
                    $imageWidth = JRequest::getInt('itemImageM');
                } else {
                    $imageWidth = $params->get('itemImageM', '400');
                }
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                // Small image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_S';
                if (JRequest::getInt('itemImageS')) {
                    $imageWidth = JRequest::getInt('itemImageS');
                } else {
                    $imageWidth = $params->get('itemImageS', '200');
                }
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                // XSmall image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_XS';
                if (JRequest::getInt('itemImageXS')) {
                    $imageWidth = JRequest::getInt('itemImageXS');
                } else {
                    $imageWidth = $params->get('itemImageXS', '100');
                }
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                // Generic image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_Generic';
                $imageWidth = $params->get('itemImageGeneric', '300');
                $handle->image_x = $imageWidth;
                $handle->process($savepath);

                if ($files['image']['error'] == 0) {
                    $handle->clean();
                }
            } else {
                $app->enqueueMessage(JText::_('K2_IMAGE_WAS_NOT_UPLOADED'), 'notice');
            }
        }

        if (JRequest::getBool('del_image')) {
            $filename = md5("Image".$savedRow->id);

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/src/'.$filename.'.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/src/'.$filename.'.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_XS.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_XS.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_S.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_S.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_M.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_M.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_L.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_L.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_XL.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_XL.jpg');
            }

            if (JFile::exists(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_Generic.jpg')) {
                JFile::delete(JPATH_ROOT.'/media/k2/items/cache/'.$filename.'_Generic.jpg');
            }

            $row->image_caption = '';
            $row->image_credits = '';
        }

        // Gallery
        if (empty($savedRow->gallery)) {
            $row->gallery = '';
        }

        $flickrGallery = JRequest::getVar('flickrGallery');
        if ($flickrGallery) {
            $row->gallery = '{gallery}'.$flickrGallery.'{/gallery}';
        }

        if (isset($files['gallery']) && $files['gallery']['error'] == 0 && !JRequest::getBool('del_gallery')) {
            $handle = new Upload($files['gallery']);
            $handle->file_auto_rename = true;
            $savepath = JPATH_ROOT.'/media/k2/galleries';
            $handle->allowed = array(
                "application/gnutar",
                "application/gzip",
                "application/x-bzip",
                "application/x-bzip2",
                "application/x-compressed",
                "application/x-gtar",
                "application/x-gzip",
                "application/x-tar",
                "application/x-zip-compressed",
                "application/zip",
                "multipart/x-gzip",
                "multipart/x-zip",
            );

            if ($handle->uploaded) {
                $handle->process($savepath);
                $handle->clean();

                if (JFolder::exists($savepath.'/'.$row->id)) {
                    JFolder::delete($savepath.'/'.$row->id);
                }

                if (!JArchive::extract($savepath.'/'.$handle->file_dst_name, $savepath.'/'.$row->id)) {
                    $app->enqueueMessage(JText::_('K2_GALLERY_UPLOAD_ERROR_CANNOT_EXTRACT_ARCHIVE'), 'error');
                    $app->redirect('index.php?option=com_k2&view=items');
                } else {
                    $imageDir = $savepath.'/'.$row->id;
                    $galleryDir = opendir($imageDir);
                    while ($filename = readdir($galleryDir)) {
                        if ($filename != "." && $filename != "..") {
                            $file = str_replace(" ", "_", $filename);
                            $safefilename = JFile::makeSafe($file);
                            rename($imageDir.'/'.$filename, $imageDir.'/'.$safefilename);
                        }
                    }
                    closedir($galleryDir);
                    $row->gallery = '{gallery}'.$row->id.'{/gallery}';
                }
                JFile::delete($savepath.'/'.$handle->file_dst_name);
                $handle->clean();
            } else {
                $app->enqueueMessage($handle->error, 'error');
                $app->redirect('index.php?option=com_k2&view=items');
            }
        }

        if (JRequest::getBool('del_gallery')) {
            if (JFolder::exists(JPATH_ROOT.'/media/k2/galleries/'.$savedRow->id)) {
                JFolder::delete(JPATH_ROOT.'/media/k2/galleries/'.$savedRow->id);
            }
            $row->gallery = '';
        }

        // === Media ===

        // Allowed filetypes for uploading
        $videoExtensions = array(
            "avi",
            "m4v",
            "mkv",
            "mp4",
            "ogv",
            "webm"
        );
        $audioExtensions = array(
            "flac",
            "m4a",
            "mp3",
            "oga",
            "ogg",
            "wav"
        );
        $validExtensions = array_merge($videoExtensions, $audioExtensions);

        // No stored media & form fields empty for media
        if (empty($savedRow->video) && !JRequest::getVar('embedVideo') && !JRequest::getVar('videoID') && !JRequest::getVar('remoteVideo') && !JRequest::getVar('uploadedVideo')) {
            $row->video = '';
        }

        // There is stored media
        if (!empty($savedRow->video)) {
            $row->video = $savedRow->video;
        }

        // Embed
        if (JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW)) {
            $row->video = JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW);
        }

        // Third-party Media Service
        if (JRequest::getVar('videoID')) {
            $provider = JRequest::getWord('videoProvider');
            $videoID = JRequest::getVar('videoID');
            $row->video = '{'.$provider.'}'.$videoID.'{/'.$provider.'}';
        }

        // Browse server or remote media
        if (JRequest::getVar('remoteVideo')) {
            $fileurl = JRequest::getVar('remoteVideo');
            $filetype = JFile::getExt($fileurl);
            $allVideosTagSuffix = 'remote';
            $row->video = '{'.$filetype.$allVideosTagSuffix.'}'.$fileurl.'{/'.$filetype.$allVideosTagSuffix.'}';
        }

        // Upload media
        if (isset($files['video']) && $files['video']['error'] == 0 && !JRequest::getBool('del_video')) {
            $filetype = JFile::getExt($files['video']['name']);
            if (!in_array($filetype, $validExtensions)) {
                $app->enqueueMessage(JText::_('K2_INVALID_VIDEO_FILE'), 'error');
                $app->redirect('index.php?option=com_k2&view=items');
            }
            if (in_array($filetype, $videoExtensions)) {
                $savepath = JPATH_ROOT.'/media/k2/videos';
            } else {
                $savepath = JPATH_ROOT.'/media/k2/audio';
            }
            $filename = JFile::stripExt($files['video']['name']);
            JFile::upload($files['video']['tmp_name'], $savepath.'/'.$row->id.'.'.$filetype);
            $filetype = JFile::getExt($files['video']['name']);

            $row->video = '{'.$filetype.'}'.$row->id.'{/'.$filetype.'}';
        }

        // Delete media
        if (JRequest::getBool('del_video')) {
            preg_match_all("#^{(.*?)}(.*?){#", $savedRow->video, $matches, PREG_PATTERN_ORDER);

            $mediaType = $matches[1][0];
            $mediaFile = $matches[2][0];

            if (in_array($mediaType, $videoExtensions)) {
                if (JFile::exists(JPATH_ROOT.'/media/k2/videos/'.$mediaFile.'.'.$mediaType)) {
                    JFile::delete(JPATH_ROOT.'/media/k2/videos/'.$mediaFile.'.'.$mediaType);
                }
            }

            if (in_array($mediaType, $audioExtensions)) {
                if (JFile::exists(JPATH_ROOT.'/media/k2/audio/'.$mediaFile.'.'.$mediaType)) {
                    JFile::delete(JPATH_ROOT.'/media/k2/audio/'.$mediaFile.'.'.$mediaType);
                }
            }

            $row->video = '';
        }

        // Media Caption & Credits
        if (!$row->video) {
            $row->video_caption = '';
            $row->video_credits = '';
        }

        // === Extra fields ===
        if ($params->get('showExtraFieldsTab') || $app->isAdmin()) {
            $objects = array();
            $variables = JRequest::get('post', 2);
            foreach ($variables as $key => $value) {
                if (( bool )JString::stristr($key, 'K2ExtraField_')) {
                    $object = new stdClass();
                    $object->id = substr($key, 13);
                    if (is_string($value)) {
                        $value = trim($value);
                    }
                    $object->value = $value;
                    $objects[] = $object;
                }
            }

            $csvFiles = JRequest::get('files');
            foreach ($csvFiles as $key => $file) {
                if ((bool) JString::stristr($key, 'K2ExtraField_')) {
                    $object = new stdClass();
                    $object->id = substr($key, 13);
                    $csvFile = $file['tmp_name'][0];
                    if (!empty($csvFile) && JFile::getExt($file['name'][0]) == 'csv') {
                        $handle = @fopen($csvFile, 'r');
                        $csvData = array();
                        while (($data = fgetcsv($handle, 1000)) !== false) {
                            $csvData[] = $data;
                        }
                        fclose($handle);
                        $object->value = $csvData;
                    } else {
                        $object->value = json_decode(JRequest::getVar('K2CSV_'.$object->id));
                        if (JRequest::getBool('K2ResetCSV_'.$object->id)) {
                            $object->value = null;
                        }
                    }
                    $objects[] = $object;
                }
            }

            $row->extra_fields = json_encode($objects);

            require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/extrafield.php');
            $extraFieldModel = K2Model::getInstance('ExtraField', 'K2Model');
            $row->extra_fields_search = '';
            foreach ($objects as $object) {
                $row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
                $row->extra_fields_search .= ' ';
            }
        }

        // Attachments
        $path = $params->get('attachmentsFolder', null);
        if (is_null($path)) {
            $savepath = JPATH_ROOT.'/media/k2/attachments';
        } else {
            $savepath = $path;
        }

        $attPost = JRequest::getVar('attachment', null, 'POST', 'array');
        $attFiles = JRequest::getVar('attachment', null, 'FILES', 'array');

        if (is_array($attPost) && count($attPost)) {
            foreach ($attPost as $key => $attachment) { /* Use the POST array's key as reference */
                if (!empty($attachment['existing'])) {
                    $src = JPATH_SITE.'/'.JPath::clean($attachment['existing']);
                    $filename = basename($src);
                    $dest = $savepath.'/'.$filename;

                    if (JFile::exists($dest)) {
                        $existingFileName = JFile::getName($dest);
                        $ext = JFile::getExt($existingFileName);
                        $basename = JFile::stripExt($existingFileName);
                        $newFilename = $basename.'_'.time().'.'.$ext;
                        $filename = $newFilename;
                        $dest = $savepath.'/'.$newFilename;
                    }

                    JFile::copy($src, $dest);

                    $attachmentToSave = JTable::getInstance('K2Attachment', 'Table');
                    $attachmentToSave->itemID = $row->id;
                    $attachmentToSave->filename = $filename;
                    $attachmentToSave->title = (empty($attachment['title'])) ? $filename : $attachment['title'];
                    $attachmentToSave->titleAttribute = (empty($attachment['title_attribute'])) ? $filename : $attachment['title_attribute'];
                    $attachmentToSave->store();
                } else {
                    $handle = new Upload($attFiles['tmp_name'][$key]['upload']);
                    $filename = $attFiles['name'][$key]['upload'];
                    if ($handle->uploaded) {
                        $handle->file_auto_rename = true;
                        $handle->file_new_name_body = JFile::stripExt($filename);
                        $handle->file_new_name_ext = JFile::getExt($filename);
                        $handle->file_safe_name = true;
                        $handle->forbidden = array(
                            "application/java-archive",
                            "application/x-httpd-php",
                            "application/x-sh",
                        );
                        $handle->process($savepath);
                        $dstName = $handle->file_dst_name;
                        $handle->clean();

                        $attachmentToSave = JTable::getInstance('K2Attachment', 'Table');
                        $attachmentToSave->itemID = $row->id;
                        $attachmentToSave->filename = $dstName;
                        $attachmentToSave->title = (empty($attachment['title'])) ? $filename : $attachment['title'];
                        $attachmentToSave->titleAttribute = (empty($attachment['title_attribute'])) ? $filename : $attachment['title_attribute'];
                        $attachmentToSave->store();
                    } else {
                        $app->enqueueMessage($handle->error, 'error');
                        $app->redirect('index.php?option=com_k2&view=items');
                    }
                }
            }
        }

        // Check publishing permissions in frontend editing
        if ($front) {
            $newPublishedState = $row->published;
            $row->published = 0;

            // "Allow editing of already published items" permission check
            if (!$isNew && K2HelperPermissions::canEditPublished($row->catid)) {
                $row->published = $published;
            }

            // "Publish items" permission check
            if (K2HelperPermissions::canPublishItem($row->catid)) {
                $row->published = $newPublishedState;
            }

            if (!K2HelperPermissions::canEditPublished($row->catid) && !K2HelperPermissions::canPublishItem($row->catid) && $newPublishedState) {
                $app->enqueueMessage(JText::_('K2_YOU_DONT_HAVE_THE_PERMISSION_TO_PUBLISH_ITEMS'), 'notice');
            }
        }

        $query = "UPDATE #__k2_items SET
            image_caption = ".$db->Quote($row->image_caption).",
            image_credits = ".$db->Quote($row->image_credits).",
            video_caption = ".$db->Quote($row->video_caption).",
            video_credits = ".$db->Quote($row->video_credits).",
            video = ".$db->Quote($row->video).",
            gallery = ".$db->Quote($row->gallery);
        if ($params->get('showExtraFieldsTab') || $app->isAdmin()) {
            $query .= ", extra_fields = ".$db->Quote($row->extra_fields).", extra_fields_search = ".$db->Quote($row->extra_fields_search);
        }
        $query .= ", published = ".$db->Quote($row->published)." WHERE id = ".$row->id;

        $db->setQuery($query);

        if (!$db->query()) {
            $app->enqueueMessage($db->getErrorMsg(), 'error');
            $app->redirect('index.php?option=com_k2&view=items');
        }

        $row->checkin();

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        // Trigger K2 plugins
        $dispatcher->trigger('onAfterK2Save', array(&$row, $isNew));

        // Trigger content & finder plugins after the save event
        if (K2_JVERSION != '15') {
            $dispatcher->trigger('onContentAfterSave', array('com_k2.item', &$row, $isNew));
        } else {
            $dispatcher->trigger('onAfterContentSave', array(&$row, $isNew));
        }
        $results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.item', $row, $isNew));

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_ITEM_SAVED');
                $link = 'index.php?option=com_k2&view=item&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_ITEM_SAVED');
                $link = 'index.php?option=com_k2&view=item';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_ITEM_SAVED');
                if ($front) {
                    $link = 'index.php?option=com_k2&view=item&task=edit&cid='.$row->id.'&tmpl=component&Itemid='.JRequest::getInt('Itemid');
                } else {
                    $link = 'index.php?option=com_k2&view=items';
                }
                break;
        }
        $app->enqueueMessage($msg);
        $app->redirect($link);
    }

    public function cancel()
    {
        $app = JFactory::getApplication();
        $cid = JRequest::getInt('id');
        if ($cid) {
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load($cid);
            $row->checkin();
        } else {
            // Cleanup SIGPro
            $sigProFolder = JRequest::getCmd('sigProFolder');
            if ($sigProFolder && !is_numeric($sigProFolder) && JFolder::exists(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder)) {
                JFolder::delete(JPATH_SITE.'/media/k2/galleries/'.$sigProFolder);
            }
        }
        $app->redirect('index.php?option=com_k2&view=items');
    }

    public function getVideoProviders()
    {
        jimport('joomla.filesystem.file');

        if (K2_JVERSION != '15') {
            $file = JPATH_PLUGINS.'/content/jw_allvideos/jw_allvideos/includes/sources.php';
        } else {
            $file = JPATH_PLUGINS.'/content/jw_allvideos/includes/sources.php';
        }

        $providers = array();

        if (JFile::exists($file)) {
            require $file;
            if (!empty($tagReplace) && is_array($tagReplace)) {
                foreach ($tagReplace as $name => $embed) {
                    if (strpos($embed, '<iframe') !== false || strpos($embed, '<script') !== false) {
                        $providers[] = $name;
                    }
                }
            }
        }

        return $providers;
    }

    public function download()
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        jimport('joomla.filesystem.file');
        $params = JComponentHelper::getParams('com_k2');
        $id = JRequest::getInt('id');

        // Plugin Events
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();

        $attachment = JTable::getInstance('K2Attachment', 'Table');
        if ($app->isSite()) {
            $token = JRequest::getVar('id');
            $check = JString::substr($token, JString::strpos($token, '_') + 1);
            $hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($id) : JUtility::getHash($id);
            if ($check != $hash) {
                JError::raiseError(404, JText::_('K2_NOT_FOUND'));
            }
        }
        $attachment->load($id);

        // Frontend Editing: Ensure the user has access to the item
        if ($app->isSite()) {
            $item = JTable::getInstance('K2Item', 'Table');
            $item->load($attachment->itemID);
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load($item->catid);
            if (!$item->id || !$category->id) {
                JError::raiseError(404, JText::_('K2_NOT_FOUND'));
            }

            if (K2_JVERSION == '15' && ($item->access > $user->get('aid', 0) || $category->access > $user->get('aid', 0))) {
                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }

            if (K2_JVERSION != '15' && (!in_array($category->access, $user->getAuthorisedViewLevels()) || !in_array($item->access, $user->getAuthorisedViewLevels()))) {
                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }

        // Trigger K2 plugins
        $dispatcher->trigger('onK2BeforeDownload', array(&$attachment, &$params));

        $path = $params->get('attachmentsFolder', null);
        if (is_null($path)) {
            $savepath = JPATH_ROOT.'/media/k2/attachments';
        } else {
            $savepath = $path;
        }
        $file = $savepath.'/'.$attachment->filename;

        if (JFile::exists($file)) {
            // Trigger K2 plugins
            $dispatcher->trigger('onK2AfterDownload', array(&$attachment, &$params));

            if ($app->isSite()) {
                $attachment->hit();
            }
            $len = filesize($file);
            $filename = basename($file);
            ob_end_clean();
            JResponse::clearHeaders();
            JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
            JResponse::setHeader('Content-Disposition', 'attachment; filename="'.$filename.'";', true);
            JResponse::setHeader('Content-Length', $len, true);
            JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
            JResponse::setHeader('Content-Type', 'application/octet-stream', true);
            JResponse::setHeader('Expires', '0', true);
            JResponse::setHeader('Pragma', 'public', true);
            JResponse::sendHeaders();
            readfile($file);
        } else {
            echo JText::_('K2_FILE_DOES_NOT_EXIST');
        }
        $app->close();
    }

    public function getAttachments($itemID)
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__k2_attachments WHERE itemID=".(int) $itemID);
        $rows = $db->loadObjectList();
        foreach ($rows as $row) {
            $hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($row->id) : JUtility::getHash($row->id);
            $row->link = JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$row->id.'_'.$hash);
        }
        return $rows;
    }

    public function deleteAttachment()
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        jimport('joomla.filesystem.file');
        $id = JRequest::getInt('id');
        $itemID = JRequest::getInt('cid');

        // Plugin Events
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();

        $db = JFactory::getDbo();
        $db->setQuery("SELECT COUNT(*) FROM #__k2_attachments WHERE itemID={$itemID} AND id={$id}");
        $result = $db->loadResult();

        if (!$result) {
            $app->close();
        }

        $row = JTable::getInstance('K2Attachment', 'Table');
        $row->load($id);

        $path = $params->get('attachmentsFolder', null);
        if (is_null($path)) {
            $savepath = JPATH_ROOT.'/media/k2/attachments';
        } else {
            $savepath = $path;
        }

        if (JFile::exists($savepath.'/'.$row->filename)) {
            JFile::delete($savepath.'/'.$row->filename);
        }

        $row->delete($id);

        // Trigger K2 plugins
        $result = $dispatcher->trigger('onAfterK2DeleteAttachment', array($id, $savepath));

        $app->close();
    }

    public function getAvailableTags($itemID = null)
    {
        $db = JFactory::getDbo();
        $query = "SELECT * FROM #__k2_tags as tags";
        if (!is_null($itemID)) {
            $query .= " WHERE tags.id NOT IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID=".(int) $itemID.")";
        }
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getCurrentTags($itemID)
    {
        $db = JFactory::getDbo();
        $itemID = (int) $itemID;
        $db->setQuery("SELECT tags.* FROM #__k2_tags AS tags JOIN #__k2_tags_xref AS xref ON tags.id = xref.tagID WHERE xref.itemID = ".(int) $itemID." ORDER BY xref.id ASC");
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function resetHits()
    {
        $app = JFactory::getApplication();
        $id = JRequest::getInt('id');
        $db = JFactory::getDbo();
        $db->setQuery("UPDATE #__k2_items SET hits=0 WHERE id={$id}");
        $db->query();
        if ($app->isAdmin()) {
            $url = 'index.php?option=com_k2&view=item&cid='.$id;
        } else {
            $url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
        }
        $app->enqueueMessage(JText::_('K2_SUCCESSFULLY_RESET_ITEM_HITS'));
        $app->redirect($url);
    }

    public function resetRating()
    {
        $app = JFactory::getApplication();
        $id = JRequest::getInt('id');
        $db = JFactory::getDbo();
        $db->setQuery("DELETE FROM #__k2_rating WHERE itemID={$id}");
        $db->query();
        if ($app->isAdmin()) {
            $url = 'index.php?option=com_k2&view=item&cid='.$id;
        } else {
            $url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
        }
        $app->enqueueMessage(JText::_('K2_SUCCESSFULLY_RESET_ITEM_RATING'));
        $app->redirect($url);
    }

    public function getRating()
    {
        $id = JRequest::getInt('cid');
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__k2_rating WHERE itemID={$id}", 0, 1);
        $row = $db->loadObject();
        return $row;
    }

    public function checkSIG()
    {
        $app = JFactory::getApplication();
        if (K2_JVERSION != '15') {
            $check = JPATH_PLUGINS.'/content/jw_sigpro/jw_sigpro.php';
        } else {
            $check = JPATH_PLUGINS.'/content/jw_sigpro.php';
        }
        if (JFile::exists($check)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkAllVideos()
    {
        $app = JFactory::getApplication();
        if (K2_JVERSION != '15') {
            $check = JPATH_PLUGINS.'/content/jw_allvideos/jw_allvideos.php';
        } else {
            $check = JPATH_PLUGINS.'/content/jw_allvideos.php';
        }
        if (JFile::exists($check)) {
            return true;
        } else {
            return false;
        }
    }

    public function cleanText($text)
    {
        if (version_compare(JVERSION, '2.5.0', 'ge')) {
            $text = JComponentHelper::filterText($text);
        } elseif (version_compare(JVERSION, '2.5.0', 'lt') && version_compare(JVERSION, '1.6.0', 'ge')) {
            JLoader::register('ContentHelper', JPATH_ADMINISTRATOR.'/components/com_content/helpers/content.php');
            $text = ContentHelper::filterText($text);
        } else {
            $config = JComponentHelper::getParams('com_content');
            $user = JFactory::getUser();
            $gid = $user->get('gid');
            $filterGroups = $config->get('filter_groups');

            // Convert to array if one group is selected
            if ((!is_array($filterGroups) && (int) $filterGroups > 0)) {
                $filterGroups = array($filterGroups);
            }

            if (is_array($filterGroups) && in_array($gid, $filterGroups)) {
                $filterType = $config->get('filter_type');
                $filterTags = preg_split('#[,\s]+#', trim($config->get('filter_tags')));
                $filterAttrs = preg_split('#[,\s]+#', trim($config->get('filter_attritbutes')));
                switch ($filterType) {
                    case 'NH':
                        $filter = new JFilterInput();
                        break;
                    case 'WL':
                        $filter = new JFilterInput($filterTags, $filterAttrs, 0, 0, 0);
                        break;
                    case 'BL':
                    default:
                        $filter = new JFilterInput($filterTags, $filterAttrs, 1, 1);
                        break;
                }
                $text = $filter->clean($text);
            } elseif (empty($filterGroups) && $gid != '25') {
                // No default filtering for super admin (gid=25)
                $filter = new JFilterInput(array(), array(), 1, 1);
                $text = $filter->clean($text);
            }
        }

        return $text;
    }
}
