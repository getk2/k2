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

?>

<?php if($this->mainframe->isSite()): ?>
<!-- Frontend Item Editing (Modal View) -->
<div id="k2ModalContainer">
	<div id="k2ModalHeader">
		<h2 id="k2ModalLogo"><?php echo (JRequest::getInt('cid')) ? JText::_('K2_EDIT_ITEM') : JText::_('K2_ADD_ITEM'); ?></h2>
		<table id="k2ModalToolbar" cellpadding="2" cellspacing="4">
			<tr>
				<td id="toolbar-save" class="button">
					<a href="#" onclick="Joomla.submitbutton('save');return false;">
						<i class="fa fa-check" aria-hidden="true"></i> <?php echo JText::_('K2_SAVE'); ?>
					</a>
				</td>
				<td id="toolbar-cancel" class="button">
					<a href="#">
						<i class="fa fa-times-circle" aria-hidden="true"></i> <?php echo JText::_('K2_CLOSE'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">

	<?php if($this->mainframe->isSite() && !$this->permissions->get('publish')): ?>
	<div id="k2ModalPermissionsNotice">
		<p><?php echo JText::_('K2_FRONTEND_PERMISSIONS_NOTICE'); ?></p>
	</div>
	<?php endif; ?>

	<!-- Top Nav Tabs START here -->
	<div id="k2FormTopNav" class="k2Tabs">

		<?php if($this->row->id): ?>
		<div id="k2ID"><strong><?php echo JText::_('K2_ID'); ?></strong> <?php echo $this->row->id; ?></div>
		<?php endif; ?>

		<ul class="k2NavTabs">
			<li id="tabContent"><a href="#k2TabBasic"><i class="fa fa-home"></i><?php echo JText::_('K2_BASIC'); ?></a></li>
			<li id="tabContent"><a href="#k2TabPubAndMeta"><i class="fa fa-info-circle"></i><?php echo JText::_('K2_PUBLISHING_AND_METADATA'); ?></a></li>
			<?php if($this->mainframe->isAdmin()): ?>
			<li id="tabContent"><a href="#k2TabDisplaySet"><i class="fa fa-desktop"></i><?php echo JText::_('K2_DISPLAY_SETTINGS'); ?></a></li>
			<?php endif; ?>
		</ul>

		<!-- Top Nav Tabs content -->
		<div class="k2NavTabContent" id="k2TabBasic">

			<div class="k2Table">
				<div class="k2TableLabel">
					<label for="title"><?php echo JText::_('K2_TITLE'); ?></label>
				</div>
				<div class="k2TableValue">
					<input class="text_area k2TitleBox" type="text" name="title" id="title" maxlength="250" value="<?php echo $this->row->title; ?>" />
				</div>

				<div class="k2TableLabel">
					<label for="alias"><?php echo JText::_('K2_TITLE_ALIAS'); ?></label>
				</div>
				<div class="k2TableValue">
					<input class="text_area k2TitleAliasBox" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->row->alias; ?>" />
				</div>

				<div class="k2TableLabel">
					<label><?php echo JText::_('K2_CATEGORY'); ?></label>
				</div>
				<div class="k2TableValue">
					<?php echo $this->lists['categories']; ?>

					<?php if($this->mainframe->isAdmin() || ($this->mainframe->isSite() && $this->permissions->get('publish'))): ?>
					<div class="k2SubTable k2TableRight k2TableRightTop">
						<div class="k2SubTableLabel">
							<label for="featured"><?php echo JText::_('K2_IS_IT_FEATURED'); ?></label>
						</div>
						<div class="k2SubTableValue">
							<?php echo $this->lists['featured']; ?>
						</div>

						<div class="k2SubTableLabel">
							<label><?php echo JText::_('K2_PUBLISHED'); ?></label>
						</div>
						<div class="k2SubTableValue">
							<?php echo $this->lists['published']; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<div class="k2TableLabel">
					<label for="tags"><?php echo JText::_('K2_TAGS'); ?></label>
				</div>
				<div class="k2TableValue">
					<?php if($this->params->get('taggingSystem')): ?>
					<!-- Free tagging -->
					<ul class="tags">
						<?php if(isset($this->row->tags) && count($this->row->tags)): ?>
						<?php foreach($this->row->tags as $tag): ?>
						<li class="tagAdded">
							<?php echo $tag->name; ?>
							<span title="<?php echo JText::_('K2_CLICK_TO_REMOVE_TAG'); ?>" class="tagRemove">&times;</span>
							<input type="hidden" name="tags[]" value="<?php echo $tag->name; ?>" />
						</li>
						<?php endforeach; ?>
						<?php endif; ?>
						<li class="tagAdd">
							<input type="text" id="search-field" />
						</li>
						<li class="clr"></li>
					</ul>
					<p class="k2TagsNotice">
						<?php echo JText::_('K2_WRITE_A_TAG_AND_PRESS_RETURN_OR_COMMA_TO_ADD_IT'); ?>
					</p>
					<?php else: ?>
					<!-- Selection based tagging -->
					<?php if( !$this->params->get('lockTags') || $this->user->gid>23): ?>
					<div style="float:left;">
						<input type="text" name="tag" id="tag" />
						<input type="button" id="newTagButton" value="<?php echo JText::_('K2_ADD'); ?>" />
					</div>
					<div id="tagsLog"></div>
					<div class="clr"></div>
					<span class="k2Note">
						<?php echo JText::_('K2_WRITE_A_TAG_AND_PRESS_ADD_TO_INSERT_IT_TO_THE_AVAILABLE_TAGS_LISTNEW_TAGS_ARE_APPENDED_AT_THE_BOTTOM_OF_THE_AVAILABLE_TAGS_LIST_LEFT'); ?>
					</span>
					<?php endif; ?>
					<table cellspacing="0" cellpadding="0" border="0" id="tagLists">
						<tr>
							<td id="tagListsLeft">
								<span><?php echo JText::_('K2_AVAILABLE_TAGS'); ?></span> <?php echo $this->lists['tags'];	?>
							</td>
							<td id="tagListsButtons">
								<input type="button" id="addTagButton" value="<?php echo JText::_('K2_ADD'); ?> &raquo;" />
								<br />
								<br />
								<input type="button" id="removeTagButton" value="&laquo; <?php echo JText::_('K2_REMOVE'); ?>" />
							</td>
							<td id="tagListsRight">
								<span><?php echo JText::_('K2_SELECTED_TAGS'); ?></span> <?php echo $this->lists['selectedTags']; ?>
							</td>
						</tr>
					</table>
					<?php endif; ?>
				</div>

				<div class="k2TableLabel">
					<label><?php echo JText::_('K2_AUTHOR'); ?></label>
				</div>
				<div class="k2TableValue">
					<div class="k2SubTable k2TableInline">
						<div class="k2SubTableValue">
							<span id="k2Author">
								<?php echo $this->row->author; ?>
								<input type="hidden" name="created_by" value="<?php echo $this->row->created_by; ?>" />
							</span>
							<?php if($this->mainframe->isAdmin() || ($this->mainframe->isSite() && $this->permissions->get('editAll'))): ?>
							<a data-k2-modal="iframe" class="k2Selector" href="index.php?option=com_k2&amp;view=users&amp;tmpl=component&amp;context=modalselector&amp;fid=k2Author&amp;fname=created_by">
								<i class="fa fa-pencil"></i>
							</a>

							<?php endif; ?>
						</div>

						<div class="k2SubTableLabel">
							<label><?php echo JText::_('K2_AUTHOR_ALIAS'); ?></label>
						</div>
						<div class="k2SubTableValue">
							<input class="text_area" type="text" name="created_by_alias" maxlength="250" value="<?php echo $this->row->created_by_alias; ?>" />
						</div>
					</div>

					<div class="k2SubTable k2ItemTableRight k2ItemTableRightPad">
						<div class="k2SubTableLabel">
							<label><?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
						</div>
						<div class="k2SubTableValue">
							<?php echo $this->lists['access']; ?>
						</div>
						<?php if(isset($this->lists['language'])): ?>
						<div class="k2SubTableLabel">
							<label><?php echo JText::_('K2_LANGUAGE'); ?></label>
						</div>
						<div class="k2SubTableValue">
							<?php echo $this->lists['language']; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Required extra field warning -->
			<div id="k2ExtraFieldsValidationResults">
				<h3><?php echo JText::_('K2_THE_FOLLOWING_FIELDS_ARE_REQUIRED'); ?></h3>
				<ul id="k2ExtraFieldsMissing">
					<li><?php echo JText::_('K2_LOADING'); ?></li>
				</ul>
			</div>

			<!-- Tabs start here -->
			<div class="k2Tabs" id="k2Tabs">
				<ul class="k2TabsNavigation">
					<li id="tabContent"><a href="#k2TabContent"><i class="fa fa-file-text-o"></i><?php echo JText::_('K2_CONTENT'); ?></a></li>
					<?php if ($this->params->get('showImageTab')): ?>
					<li id="tabImage"><a href="#k2TabImage"><i class="fa fa-camera"></i><?php echo JText::_('K2_IMAGE'); ?></a></li>
					<?php endif; ?>
					<?php if ($this->params->get('showImageGalleryTab')): ?>
					<li id="tabImageGallery"><a href="#k2TabImageGallery"><i class="fa fa-file-image-o"></i><?php echo JText::_('K2_IMAGE_GALLERY'); ?></a></li>
					<?php endif; ?>
					<?php if ($this->params->get('showVideoTab')): ?>
					<li id="tabVideo"><a href="#k2TabMedia"><i class="fa fa-file-video-o"></i><?php echo JText::_('K2_MEDIA'); ?></a></li>
					<?php endif; ?>
					<?php if ($this->params->get('showExtraFieldsTab')): ?>
					<li id="tabExtraFields"><a href="#k2TabExtraFields"><i class="fa fa-gear"></i><?php echo JText::_('K2_EXTRA_FIELDS'); ?></a></li>
					<?php endif; ?>
					<?php if ($this->params->get('showAttachmentsTab')): ?>
					<li id="tabAttachments"><a href="#k2TabAttachments"><i class="fa fa-file-o"></i><?php echo JText::_('K2_ATTACHMENTS'); ?></a></li>
					<?php endif; ?>
					<?php if(count(array_filter($this->K2PluginsItemOther)) && $this->params->get('showK2Plugins')): ?>
					<li id="tabPlugins"><a href="#k2TabPlugins"><i class="fa fa-wrench"></i><?php echo JText::_('K2_PLUGINS'); ?></a></li>
					<?php endif; ?>
				</ul>

				<!-- Tab content -->
				<div class="k2TabsContent" id="k2TabContent">
					<?php if($this->params->get('mergeEditors')): ?>
					<div class="k2ItemFormEditor">
						<?php echo $this->text; ?>
						<div class="dummyHeight"></div>
						<div class="clr"></div>
					</div>
					<?php else: ?>
					<div class="k2ItemFormEditor">
						<span class="k2ItemFormEditorTitle"><?php echo JText::_('K2_INTROTEXT_TEASER_CONTENTEXCERPT'); ?></span>
						<?php echo $this->introtext; ?>
						<div class="dummyHeight"></div>
						<div class="clr"></div>
					</div>
					<div class="k2ItemFormEditor">
						<span class="k2ItemFormEditorTitle"><?php echo JText::_('K2_FULLTEXT_MAIN_CONTENT'); ?></span>
						<?php echo $this->fulltext; ?>
						<div class="dummyHeight"></div>
						<div class="clr"></div>
					</div>
					<?php endif; ?>
					<?php if (count($this->K2PluginsItemContent)): ?>
					<div class="itemPlugins itemPluginsContent">
						<?php foreach($this->K2PluginsItemContent as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<legend><?php echo $K2Plugin->name; ?></legend>
							<div class="itemAdditionalData">
								<?php echo $K2Plugin->fields; ?>
							</div>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<div class="clr"></div>
				</div>
				<?php if ($this->params->get('showImageTab')): ?>
				<!-- Tab image -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabImage">

					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_ITEM_IMAGE'); ?></label>
						</div>

						<div class="itemAdditionalData">
							<input type="file" name="image" class="fileUpload" />
							<i>(<?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?>)</i>

							<span class="sep"><?php echo JText::_('K2_OR'); ?></span>

							<input type="text" name="existingImage" id="existingImageValue" class="text_area" readonly />
							<input type="button" value="<?php echo JText::_('K2_BROWSE_SERVER'); ?>" id="k2ImageBrowseServer" />
						</div>
					</div>

					<?php if (!empty($this->row->image)): ?>
					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_ITEM_IMAGE_PREVIEW'); ?></label>
						</div>

						<div class="itemAdditionalData">
							<a data-fancybox="images" data-caption="<?php echo $this->row->title; ?>" href="<?php echo $this->row->image; ?>" title="<?php echo JText::_('K2_CLICK_ON_IMAGE_TO_PREVIEW_IN_ORIGINAL_SIZE'); ?>">
								<img class="k2AdminImage" src="<?php echo $this->row->thumb; ?>" alt="<?php echo $this->row->title; ?>" />
							</a>
							<input type="checkbox" name="del_image" id="del_image" />
							<label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
						</div>
					</div>
					<?php endif; ?>

					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_ITEM_IMAGE_CAPTION'); ?></label>
						</div>
						<div class="itemAdditionalData">
							<input type="text" name="image_caption" size="30" class="text_area" value="<?php echo $this->row->image_caption; ?>" />
						</div>
					</div>

					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_ITEM_IMAGE_CREDITS'); ?></label>
						</div>
						<div class="itemAdditionalData">
							<input type="text" name="image_credits" size="30" class="text_area" value="<?php echo $this->row->image_credits; ?>" />
						</div>
					</div>

					<?php if (count($this->K2PluginsItemImage)): ?>
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemImage as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<fieldset>
								<div class="k2FLeft k2Right itemAdditionalValue">
									<label><?php echo $K2Plugin->name; ?></label>
								</div>
								<div class="itemAdditionalData">
									<?php echo $K2Plugin->fields; ?>
								</div>
							</fieldset>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ($this->params->get('showImageGalleryTab')): ?>

				<!-- Tab image gallery -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabImageGallery">
					<?php if ($this->lists['checkSIG']): ?>
					<div id="item_gallery_content" class="itemAdditionalField">

						<?php if($this->sigPro): ?>
						<div class="itemGalleryBlock">
							<label><?php echo JText::_('K2_COM_BE_ITEM_SIGPRO_UPLOAD_NOTE'); ?></label>
							<a class="k2Button modal" rel="{handler: 'iframe', size: {x: 940, y: 560}}" href="index.php?option=com_sigpro&view=galleries&task=create&newFolder=<?php echo $this->sigProFolder; ?>&type=k2&tmpl=component">
								<?php echo JText::_('K2_COM_BE_ITEM_SIGPRO_UPLOAD'); ?>
							</a>
							<br />
							<input name="sigProFolder" type="hidden" value="<?php echo $this->sigProFolder; ?>" />
						</div>

						<div class="itemGalleryBlock separator">
							<?php echo JText::_('K2_OR'); ?>
						</div>
						<?php endif; ?>

						<div class="itemGalleryBlock">
							<label><?php echo JText::_('K2_UPLOAD_A_ZIP_FILE_WITH_IMAGES'); ?></label>
							<input type="file" name="gallery" class="fileUpload" />
							<span class="hasTip k2GalleryNotice" title="
								<?php echo JText::_('K2_UPLOAD_A_ZIP_FILE_HELP_HEADER'); ?>::<?php echo JText::_('K2_UPLOAD_A_ZIP_FILE_HELP_TEXT'); ?>">
								<?php echo JText::_('K2_UPLOAD_A_ZIP_FILE_HELP'); ?>
							</span>
							<label><?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?></label>
						</div>

						<div class="itemGalleryBlock separator">
							<?php echo JText::_('K2_OR'); ?>
						</div>

						<div class="itemGalleryBlock">
							<label><?php echo JText::_('K2_OR_ENTER_A_FLICKR_SET_URL'); ?></label>
							<input type="text" name="flickrGallery" size="50" value="<?php echo ($this->row->galleryType == 'flickr') ? $this->row->galleryValue : ''; ?>" />
							<span class="hasTip k2GalleryNotice" title="<?php echo JText::_('K2_VALID_FLICK_API_KEY_HELP_HEADER'); ?>::<?php echo JText::_('K2_VALID_FLICK_API_KEY_HELP_TEXT'); ?>">
								<?php echo JText::_('K2_UPLOAD_A_ZIP_FILE_HELP'); ?>
							</span>
						</div>

						<?php if (!empty($this->row->gallery)): ?>
						<!-- Preview -->
						<div id="itemGallery" class="itemGalleryBlock">
							<?php echo $this->row->gallery; ?>
							<div class="clr"></div>
							<input type="checkbox" name="del_gallery" id="del_gallery" />
							<label for="del_gallery"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_GALLERY_OR_JUST_UPLOAD_A_NEW_IMAGE_GALLERY_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
						</div>
						<?php endif; ?>

					</div>

					<?php
					// SIGPro is not present
					else: ?>
						<?php if (K2_JVERSION == '15'): ?>
						<dl id="system-message">
							<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
							<dd class="notice message fade">
								<ul>
									<li><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_SIMPLE_IMAGE_GALLERY_PRO_PLUGIN_IF_YOU_WANT_TO_USE_THE_IMAGE_GALLERY_FEATURES_OF_K2'); ?></li>
								</ul>
							</dd>
						</dl>
						<?php elseif(K2_JVERSION == '25'): ?>
						<div id="system-message-container">
							<dl id="system-message">
								<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
								<dd class="notice message">
									<ul>
										<li><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_SIMPLE_IMAGE_GALLERY_PRO_PLUGIN_IF_YOU_WANT_TO_USE_THE_IMAGE_GALLERY_FEATURES_OF_K2'); ?></li>
									</ul>
								</dd>
							</dl>
						</div>
						<?php else: ?>
						<div class="alert">
							<h4 class="alert-heading"><?php echo JText::_('K2_NOTICE'); ?></h4>
							<div><p><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_SIMPLE_IMAGE_GALLERY_PRO_PLUGIN_IF_YOU_WANT_TO_USE_THE_IMAGE_GALLERY_FEATURES_OF_K2'); ?></p></div>
						</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (count($this->K2PluginsItemGallery)): ?>
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemGallery as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<fieldset>
								<legend><?php echo $K2Plugin->name; ?></legend>
								<div class="itemAdditionalData">
									<?php echo $K2Plugin->fields; ?>
								</div>
							</fieldset>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if ($this->params->get('showVideoTab')): ?>

				<!-- Tab video -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabMedia">
					<?php if ($this->lists['checkAllVideos']): ?>
					<div id="item_video_content">
						<div class="itemAdditionalField">
							<div class="k2FLeft k2Right itemAdditionalValue">
								<label><?php echo JText::_('K2_MEDIA_SOURCE'); ?></label>
							</div>

							<div class="itemAdditionalData">
								<div id="k2MediaTabs" class="k2Tabs">
									<ul class="k2TabsNavigation">
										<li><a href="#k2MediaTab1"><?php echo JText::_('K2_UPLOAD'); ?></a></li>
										<li><a href="#k2MediaTab2"><?php echo JText::_('K2_BROWSE_SERVERUSE_REMOTE_MEDIA'); ?></a></li>
										<li><a href="#k2MediaTab3"><?php echo JText::_('K2_MEDIA_USE_ONLINE_VIDEO_SERVICE'); ?></a></li>
										<li><a href="#k2MediaTab4"><?php echo JText::_('K2_EMBED'); ?></a></li>
									</ul>

									<div id="k2MediaTab1" class="k2TabsContent k2TabsContentLower">
										<div class="panel" id="Upload_video">
											<input type="file" name="video" class="fileUpload" />
											<i>(<?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?>)</i>
										</div>
									</div>

									<div id="k2MediaTab2" class="k2TabsContent k2TabsContentLower">
										<div class="panel" id="Remote_video">
											<div class="itemAdditionalBlock">
												<a id="k2MediaBrowseServer" class="k2Button" href="index.php?option=com_k2&amp;view=media&amp;type=video&amp;tmpl=component&amp;fieldID=remoteVideo">
													<?php echo JText::_('K2_BROWSE_VIDEOS_ON_SERVER')?>
												</a>
											</div>

											<div class="itemAdditionalBlock sep">
												<label><?php echo JText::_('K2_OR'); ?></label>
											</div>

											<div class="itemAdditionalBlock">
												<label><?php echo JText::_('K2_PASTE_REMOTE_VIDEO_URL'); ?></label>
											</div>

											<div class="itemAdditionalBlock">
												<input type="text" size="50" name="remoteVideo" id="remoteVideo" value="<?php echo $this->lists['remoteVideo'] ?>" />
											</div>
										</div>
									</div>

									<div id="k2MediaTab3" class="k2TabsContent k2TabsContentLower">

										<div class="panel" id="Video_from_provider">

											<div class="itemAdditionalBlock">
												<label><?php echo JText::_('K2_SELECT_VIDEO_PROVIDER'); ?></label>
											</div>
											<div class="itemAdditionalBlock">
												<?php echo $this->lists['providers']; ?>
											</div>

											<div class="itemAdditionalBlock">
												<label><?php echo JText::_('K2_AND_ENTER_VIDEO_ID'); ?></label>
											</div>
											<div class="itemAdditionalBlock">
												<input type="text" size="50" name="videoID" value="<?php echo $this->lists['providerVideo'] ?>" />
											</div>

											<div class="k2Right k2DocLink">
												<a data-k2-modal="iframe" href="https://www.joomlaworks.net/allvideos-documentation">
													<i class="fa fa-info"></i>
													<span><?php echo JText::_('K2_READ_THE_ALLVIDEOS_DOCUMENTATION_FOR_MORE'); ?></span>
												</a>
											</div>
										</div>
									</div>
									<div id="k2MediaTab4" class="k2TabsContent k2TabsContentLower">
										<div class="itemAdditionalField panel" id="embedVideo">
											<div class="k2FLeft k2Right itemAdditionalValue">
												<label><?php echo JText::_('K2_PASTE_HTML_EMBED_CODE_BELOW'); ?></label>
											</div>
											<div class="itemAdditionalData">
												<textarea name="embedVideo" rows="5" cols="50" class="textarea"><?php echo $this->lists['embedVideo']; ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<?php if($this->row->video): ?>
						<div class="itemAdditionalField">
							<div class="k2FLeft k2Right itemAdditionalValue">
								<label><?php echo JText::_('K2_MEDIA_PREVIEW'); ?></label>
							</div>
							<div class="itemAdditionalData">
								<?php echo $this->row->video; ?>
								<div class="clr"></div>
								<input type="checkbox" name="del_video" id="del_video" />
								<label for="del_video"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_VIDEO_OR_USE_THE_FORM_ABOVE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
							</div>
						</div>
						<?php endif; ?>

						<div class="itemAdditionalField">
							<div class="k2FLeft k2Right itemAdditionalValue">
								<label><?php echo JText::_('K2_MEDIA_CAPTION'); ?></label>
							</div>
							<div class="itemAdditionalData">
								<input type="text" name="video_caption" size="50" class="text_area" value="<?php echo $this->row->video_caption; ?>" />
							</div>
						</div>

						<div class="itemAdditionalField">
							<div class="k2FLeft k2Right itemAdditionalValue">
								<label><?php echo JText::_('K2_MEDIA_CREDITS'); ?></label>
							</div>
							<div class="itemAdditionalData">
								<input type="text" name="video_credits" size="50" class="text_area" value="<?php echo $this->row->video_credits; ?>" />
							</div>
						</div>

					</div>
					<?php
					// No AllVideos - Show default fields
					else: ?>

					<!-- No AllVideos alert goes here -->
					<?php if (K2_JVERSION == '15'): ?>
					<dl id="system-message">
						<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
						<dd class="notice message fade">
							<ul>
								<li><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_ALLVIDEOS_PLUGIN_IF_YOU_WANT_TO_USE_THE_FULL_VIDEO_FEATURES_OF_K2'); ?></li>
							</ul>
						</dd>
					</dl>
					<?php elseif(K2_JVERSION == '25'): ?>
					<div id="system-message-container">
						<dl id="system-message">
							<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
							<dd class="notice message">
								<ul>
									<li><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_ALLVIDEOS_PLUGIN_IF_YOU_WANT_TO_USE_THE_FULL_VIDEO_FEATURES_OF_K2'); ?></li>
								</ul>
							</dd>
						</dl>
					</div>
					<?php else: ?>
					<div class="alert">
						<h4 class="alert-heading"><?php echo JText::_('K2_NOTICE'); ?></h4>
						<div><p><?php echo JText::_('K2_NOTICE_PLEASE_INSTALL_JOOMLAWORKS_ALLVIDEOS_PLUGIN_IF_YOU_WANT_TO_USE_THE_FULL_VIDEO_FEATURES_OF_K2'); ?></p></div>
					</div>
					<?php endif; ?>
					<!-- End of the alert -->

					<div id="k2MediaTabs" class="k2Tabs">
						<ul class="k2TabsNavigation">
							<li><a href="#k2MediaTab4"><?php echo JText::_('K2_EMBED'); ?></a></li>
						</ul>

						<div class="k2TabsContent" id="k2MediaTab4">
							<div class="panel" id="embedVideo">
								<?php echo JText::_('K2_PASTE_HTML_EMBED_CODE_BELOW'); ?>
								<br />
								<textarea name="embedVideo" rows="5" cols="50" class="textarea"><?php echo $this->lists['embedVideo']; ?></textarea>
							</div>
						</div>
					</div>

					<?php if($this->row->video): ?>
					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_MEDIA_PREVIEW'); ?></label>
						</div>
						<div class="itemAdditionalData">
							<?php echo $this->row->video; ?>
							<input type="checkbox" name="del_video" id="del_video" />
							<label for="del_video"><?php echo JText::_('K2_USE_THE_FORM_ABOVE_TO_REPLACE_THE_EXISTING_VIDEO_OR_CHECK_THIS_BOX_TO_DELETE_CURRENT_VIDEO'); ?></label>
						</div>
					</div>
					<?php endif; ?>

					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_MEDIA_CAPTION'); ?></label>
						</div>
						<div class="itemAdditionalData">
							<input type="text" name="video_caption" size="50" class="text_area" value="<?php echo $this->row->video_caption; ?>" />
						</div>
					</div>

					<div class="itemAdditionalField">
						<div class="k2FLeft k2Right itemAdditionalValue">
							<label><?php echo JText::_('K2_MEDIA_CREDITS'); ?></label>
						</div>
						<div class="itemAdditionalData">
							<input type="text" name="video_credits" size="50" class="text_area" value="<?php echo $this->row->video_credits; ?>" />
						</div>
					</div>
					<?php endif; ?>
					<!-- END of the AllVideos check -->

					<?php if (count($this->K2PluginsItemVideo)): ?>
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemVideo as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<legend><?php echo $K2Plugin->name; ?></legend>
							<div class="itemAdditionalData">
								<?php echo $K2Plugin->fields; ?>
							</div>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

				</div>
				<?php endif; ?>
				<?php if ($this->params->get('showExtraFieldsTab')): ?>
				<!-- Tab extra fields -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabExtraFields">
					<div id="extraFieldsContainer">

						<?php if (count($this->extraFields)): ?>
						<div id="extraFields">
							<?php foreach($this->extraFields as $extraField): ?>
							<div class="itemAdditionalField">

								<?php if($extraField->type == 'header'): ?>
								<h4 class="k2ExtraFieldHeader"><?php echo $extraField->name; ?></h4>
								<?php else: ?>

								<div class="k2Right k2FLeft itemAdditionalValue">
									<label for="K2ExtraField_<?php echo $extraField->id; ?>"><?php echo $extraField->name; ?></label>
								</div>
								<div class="itemAdditionalData">
									<?php echo $extraField->element; ?>
								</div>
								<?php endif; ?>
							</div>
							<?php endforeach; ?>
						</div>

						<?php else: ?>
							<?php if (K2_JVERSION == '15'): ?>
								<dl id="system-message">
									<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
									<dd class="notice message fade">
										<ul>
											<li><?php echo JText::_('K2_PLEASE_SELECT_A_CATEGORY_FIRST_TO_RETRIEVE_ITS_RELATED_EXTRA_FIELDS'); ?></li>
										</ul>
									</dd>
								</dl>
							<?php elseif (K2_JVERSION == '25'): ?>
							<div id="system-message-container">
								<dl id="system-message">
									<dt class="notice"><?php echo JText::_('K2_NOTICE'); ?></dt>
									<dd class="notice message">
										<ul>
											<li><?php echo JText::_('K2_PLEASE_SELECT_A_CATEGORY_FIRST_TO_RETRIEVE_ITS_RELATED_EXTRA_FIELDS'); ?></li>
										</ul>
									</dd>
								</dl>
							</div>
							<?php else: ?>
							<div class="alert">
								<h4 class="alert-heading"><?php echo JText::_('K2_NOTICE'); ?></h4>
								<div>
									<p><?php echo JText::_('K2_PLEASE_SELECT_A_CATEGORY_FIRST_TO_RETRIEVE_ITS_RELATED_EXTRA_FIELDS'); ?></p>
								</div>
							</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<?php if (count($this->K2PluginsItemExtraFields)): ?>
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemExtraFields as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<legend><?php echo $K2Plugin->name; ?></legend>
							<div class="itemAdditionalData"><?php echo $K2Plugin->fields; ?></div>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if ($this->params->get('showAttachmentsTab')): ?>
				<!-- Tab attachements -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabAttachments">
					<div class="itemAttachments table-responsive">
						<?php if (count($this->row->attachments)): ?>
						<table class="itemAttachmentsTable">
							<tr>
								<th>
									<?php echo JText::_('K2_FILENAME'); ?>
								</th>
								<th>
									<?php echo JText::_('K2_TITLE'); ?>
								</th>
								<th>
									<?php echo JText::_('K2_TITLE_ATTRIBUTE'); ?>
								</th>
								<th>
									<?php echo JText::_('K2_DOWNLOADS'); ?>
								</th>
								<th class="k2Center">
									<?php echo JText::_('K2_OPERATIONS'); ?>
								</th>
							</tr>
							<?php foreach($this->row->attachments as $attachment): ?>
							<tr>
								<td class="attachment_entry">
									<?php echo $attachment->filename; ?>
								</td>
								<td>
									<?php echo $attachment->title; ?>
								</td>
								<td>
									<?php echo $attachment->titleAttribute; ?>
								</td>
								<td>
									<?php echo $attachment->hits; ?>
								</td>
								<td class="k2Center">
									<a class="downloadAttachmentButton" href="<?php echo $attachment->link; ?>" title="<?php echo JText::_('K2_DOWNLOAD'); ?>">
										<i class="fa fa-download"></i>
										<span class="hidden"><?php echo JText::_('K2_DOWNLOAD'); ?></span>
									</a>
									<a class="deleteAttachmentButton" title="<?php echo JText::_('K2_DELETE'); ?>" href="<?php echo JURI::base(true); ?>/index.php?option=com_k2&amp;view=item&amp;task=deleteAttachment&amp;id=<?php echo $attachment->id?>&amp;cid=<?php echo $this->row->id; ?>">
										<i class="fa fa-remove"></i>
										<span class="hidden"><?php echo JText::_('K2_DELETE'); ?></span>
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
						<?php endif; ?>
					</div>
					<div id="addAttachment">
						<input type="button" id="addAttachmentButton" class="k2Selector k2Button" value="<?php echo JText::_('K2_ADD_ATTACHMENT_FIELD'); ?>" />
						<i>(<?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?>)</i>
					</div>
					<div id="itemAttachments"></div>

					<?php if (count($this->K2PluginsItemAttachments)): ?>
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemAttachments as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<div class="itemAdditionalField">
							<legend><?php echo $K2Plugin->name; ?></legend>
							<div class="itemAdditionalData"><?php echo $K2Plugin->fields; ?></div>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if(count(array_filter($this->K2PluginsItemOther)) && $this->params->get('showK2Plugins')): ?>
				<!-- Tab other plugins -->
				<div class="k2TabsContent k2TabsContentLower" id="k2TabPlugins">
					<div class="itemPlugins">
						<?php foreach($this->K2PluginsItemOther as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<fieldset>
							<legend><?php echo $K2Plugin->name; ?></legend>
							<?php echo $K2Plugin->fields; ?>
						</fieldset>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<!-- Lower Tabs end here -->

			<input type="hidden" name="isSite" value="<?php echo (int) $this->mainframe->isSite(); ?>" />
			<?php if($this->mainframe->isSite()): ?>
			<input type="hidden" name="lang" value="<?php echo JRequest::getCmd('lang'); ?>" />
			<?php endif; ?>
			<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
			<input type="hidden" name="option" value="com_k2" />
			<input type="hidden" name="view" value="item" />
			<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
			<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
			<?php echo JHTML::_('form.token'); ?>

		</div>
		<!-- End of the basic parameters -->

		<div class="k2NavTabContent" id="k2TabPubAndMeta">

			<ul class="k2ScrollSpyMenu">
				<?php if($this->row->id): ?>
				<li><a href="#iteminfo"><?php echo JText::_('K2_ITEM_INFO'); ?></a></li>
				<?php endif; ?>
				<li><a href="#publishing"><?php echo JText::_('K2_PUBLISHING'); ?></a></li>
				<li><a href="#metadata"><?php echo JText::_('K2_METADATA'); ?></a></li>
			</ul>

			<div class="k2ScrollingContent">

				<?php if($this->row->id): ?>
				<h3><?php echo JText::_('K2_ITEM_INFO'); ?></h3>
				<div class="row-resposive">
					<div class="col col6">
						<a id="iteminfo"></a>
						<ul class="additionalParams">
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_ITEM_ID'); ?></label>
								<label class="k2FRight"><?php echo $this->row->id; ?></label>
							</li>

							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_PUBLISHED'); ?></label>
								<label class="k2FRight"><?php echo ($this->row->published > 0) ? JText::_('K2_YES') : JText::_('K2_NO'); ?></label>
							</li>
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_FEATURED'); ?></label>
								<label class="k2FRight"><?php echo ($this->row->featured > 0) ? JText::_('K2_YES'):	JText::_('K2_NO'); ?></label>
							</li>
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_CREATED_DATE'); ?></label>
								<label class="k2FRight"><?php echo $this->lists['created']; ?></label>
							</li>
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_CREATED_BY'); ?></label>
								<label class="k2FRight"><?php echo $this->row->author; ?></label>
							</li>
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_MODIFIED_DATE'); ?></label>
								<label class="k2FRight"><?php echo $this->lists['modified']; ?></label>
							</li>
							<?php if($this->row->moderator): ?>
							<li>
								<label class="k2FLeft"><?php echo JText::_('K2_MODIFIED_BY'); ?></label>
								<label class="k2FRight"><?php echo $this->row->moderator; ?></label>
							</li>
							<?php endif; ?>
						</ul>
					</div>

					<div class="col col3">
						<div class="itemHits">
							<?php echo JText::_('K2_HITS'); ?>

							<span><?php echo $this->row->hits; ?></span>
							<?php if($this->row->hits): ?>
							<div class="itemHitsReset">
								<input id="resetHitsButton" type="button" value="<?php echo JText::_('K2_RESET'); ?>" class="button" name="resetHits" />
							</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="col col3">
						<div class="itemRating">
							<?php echo JText::_('K2_RATING'); ?>

							<?php if($this->row->ratingCount): ?>
							<span><?php echo number_format(($this->row->ratingSum/$this->row->ratingCount), 2); ?>/5.00</span>
							<?php else: ?>
							<span>0.00/5.00</span>
							<?php endif; ?>

							<?php echo $this->row->ratingCount; ?> <?php echo ($this->row->ratingCount == 1) ? JText::_('K2_VOTE') : JText::_('K2_VOTES'); ?>

							<div class="itemRatingReset">
								<input id="resetRatingButton" type="button" value="<?php echo JText::_('K2_RESET'); ?>" class="button" name="resetRating" />
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<div class="xmlParamsFields">
					<a id="publishing"></a>
					<h3><?php echo JText::_('K2_PUBLISHING'); ?></h3>
					<ul class="adminformlist">
						<li>
						    <div class="paramLabel">
								<label><?php echo JText::_('K2_CREATION_DATE'); ?></label>
							</div>
							<div class="paramValue k2DateTimePickerControl">
								<input type="text" data-k2-datetimepicker id="created" name="created" value="<?php echo $this->lists['createdCalendar']; ?>" autocomplete="off" />
								<i class="fa fa-calendar" aria-hidden="true"></i>
							</div>
						<li>
						    <div class="paramLabel">
								<label><?php echo JText::_('K2_START_PUBLISHING'); ?></label>
							</div>
							<div class="paramValue k2DateTimePickerControl">
								<input type="text" data-k2-datetimepicker id="publish_up" name="publish_up" value="<?php echo $this->lists['publish_up']; ?>" autocomplete="off" />
								<i class="fa fa-calendar" aria-hidden="true"></i>
							</div>
						</li>
						<li>
						    <div class="paramLabel">
								<label><?php echo JText::_('K2_FINISH_PUBLISHING'); ?></label>
							</div>
							<div class="paramValue k2DateTimePickerControl">
								<input type="text" data-k2-datetimepicker id="publish_down" name="publish_down" value="<?php echo $this->lists['publish_down']; ?>" autocomplete="off" />
								<i class="fa fa-calendar" aria-hidden="true"></i>
							</div>
						</li>
					</ul>

					<div class="clr"></div>
					<a id="metadata"></a>

					<h3><?php echo JText::_('K2_METADATA'); ?></h3>
					<ul class="adminformlist">
						<li>
							<div class="paramLabel">
								<label><?php echo JText::_('K2_DESCRIPTION'); ?></label>
							</div>
							<div class="paramValue">
								<textarea name="metadesc" rows="5" cols="20" data-k2-chars="160"><?php echo $this->row->metadesc; ?></textarea>
							</div>
						</li>

						<li>
							<div class="paramLabel">
								<label><?php echo JText::_('K2_KEYWORDS'); ?></label>
							</div>
							<div class="paramValue">
								<textarea name="metakey" rows="5" cols="20"><?php echo $this->row->metakey; ?></textarea>
							</div>
						</li>
						<li>
							<div class="paramLabel">
								<label><?php echo JText::_('K2_ROBOTS'); ?></label>
							</div>
							<div class="paramValue">
								<?php echo $this->lists['metarobots']; ?>
							</div>
						</li>
						<li>
							<div class="paramLabel">
								<label><?php echo JText::_('K2_AUTHOR'); ?></label>
							</div>
							<div class="paramValue">
								<input type="text" name="meta[author]" value="<?php echo $this->lists['metadata']->get('author'); ?>" />
							</div>
						</li>
					<ul>
				</div>
			</div>
		</div>

		<?php if($this->mainframe->isAdmin()): ?>
		<div class="k2NavTabContent" id="k2TabDisplaySet">

			<ul class="k2ScrollSpyMenu">
				<li><a href="#catViewOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS_IN_CATEGORY_LISTINGS'); ?></a></li>
				<li><a href="#itemViewOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS'); ?></a></li>
			</ul>

			<div class="k2ScrollingContent">
				<a id="catViewOptions"></a>
				<h3><?php echo JText::_('K2_ITEM_VIEW_OPTIONS_IN_CATEGORY_LISTINGS'); ?></h3>
				<div class="xmlParamsFields">
					<fieldset class="panelform">
						<ul class="adminformlist">
							<?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
							<?php foreach($this->form->getFieldset('item-view-options-listings') as $field): ?>
							<li<?php if($field->type=='header') echo ' class="headerElement"'; ?>>
								<?php if($field->type=='header'): ?>
								<div class="paramValueHeader"><?php echo $field->input; ?></div>
								<?php elseif($field->type=='Spacer'): ?>
								<div class="paramValueSpacer">&nbsp;</div>
								<div class="clr"></div>
								<?php else: ?>
								<div class="paramLabel"><?php echo $field->label; ?></div>
								<div class="paramValue"><?php echo $field->input; ?></div>
								<div class="clr"></div>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
							<?php else: ?>
							<?php foreach($this->form->getParams('params', 'item-view-options-listings') as $param): ?>
							<li<?php if((string)$param[1]=='' || $param[5] == '') echo ' class="headerElement"'; ?>>
								<?php if((string)$param[1]=='' || $param[5] == ''): ?>
								<div class="paramValueHeader"><?php echo $param[1]; ?></div>
								<?php else: ?>
								<div class="paramLabel"><?php echo $param[0]; ?></div>
								<div class="paramValue"><?php echo $param[1]; ?></div>
								<div class="clr"></div>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</fieldset>
				</div>
				<a id="itemViewOptions"></a>
				<h3><?php echo JText::_('K2_ITEM_VIEW_OPTIONS'); ?></h3>
				<div class="xmlParamsFields">
					<fieldset class="panelform">
						<ul class="adminformlist">
							<?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
							<?php foreach($this->form->getFieldset('item-view-options') as $field): ?>
							<li<?php if($field->type=='header') echo ' class="headerElement"'; ?>>
								<?php if($field->type=='header'): ?>
								<div class="paramValueHeader"><?php echo $field->input; ?></div>
								<?php elseif($field->type=='Spacer'): ?>
								<div class="paramValueSpacer">&nbsp;</div>
								<div class="clr"></div>
								<?php else: ?>
								<div class="paramLabel"><?php echo $field->label; ?></div>
								<div class="paramValue"><?php echo $field->input; ?></div>
								<div class="clr"></div>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
							<?php else: ?>
							<?php foreach($this->form->getParams('params', 'item-view-options') as $param): ?>
							<li<?php if((string)$param[1]=='' || $param[5] == '') echo ' class="headerElement"'; ?>>
								<?php if((string)$param[1]=='' || $param[5] == ''): ?>
								<div class="paramValueHeader"><?php echo $param[1]; ?></div>
								<?php else: ?>
								<div class="paramLabel"><?php echo $param[0]; ?></div>
								<div class="paramValue"><?php echo $param[1]; ?></div>
								<div class="clr"></div>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</fieldset>
				</div>

			</div>

		</div>
		<?php endif; ?>
	</div>
	<!-- Top Nav Tabs END here -->
</form>

<?php if($this->mainframe->isSite()): ?>
</div>
<?php endif; ?>
