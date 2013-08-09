<?php
/**
 * @version		$Id: default.php 1822 2013-01-18 16:47:59Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton){
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		if (\$K2.trim(\$K2('#name').val()) == '') {
			alert( '".JText::_('K2_A_CATEGORY_MUST_AT_LEAST_HAVE_A_TITLE', true)."' );
		} else {
			".$this->onSave."
			submitform( pressbutton );
		}
	}
");

?>

<form action="index.php" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
	<table cellspacing="0" cellpadding="0" border="0" class="adminFormK2Container adminK2Category table">
		<tbody>
			<tr>
				<td>
					<table class="adminFormK2 table">
						<tr>
							<td class="adminK2LeftCol">
								<label for="name"><?php echo JText::_('K2_TITLE'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<input class="text_area k2TitleBox" type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" maxlength="250" />
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label for="alias"><?php echo JText::_('K2_TITLE_ALIAS'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<input class="text_area k2TitleAliasBox" type="text" name="alias" value="<?php echo $this->row->alias; ?>" maxlength="250" />
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label for="parent"><?php echo JText::_('K2_PARENT_CATEGORY'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<?php echo $this->lists['parent']; ?>
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label for="paramsinheritFrom"><?php echo JText::_('K2_INHERIT_PARAMETER_OPTIONS_FROM_CATEGORY'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<?php echo $this->lists['inheritFrom']; ?> <span class="hasTip k2Notice" title="<?php echo JText::_('K2_INHERIT_PARAMETER_OPTIONS_FROM_CATEGORY'); ?>::<?php echo JText::_('K2_SETTING_THIS_OPTION_WILL_MAKE_THIS_CATEGORY_INHERIT_ALL_PARAMETERS_FROM_ANOTHER_CATEGORY_THUS_YOU_DONT_HAVE_TO_RESET_ALL_OPTIONS_IN_THIS_ONE_IF_THEY_ARE_THE_SAME_WITH_ANOTHER_CATEGORYS_THIS_SETTING_IS_VERY_USEFUL_WHEN_YOU_ARE_CREATING_CHILD_CATEGORIES_WHICH_SHARE_THE_SAME_PARAMETERS_WITH_THEIR_PARENT_CATEGORY_EG_IN_THE_CASE_OF_A_CATALOG_OR_A_NEWS_PORTALMAGAZINE'); ?>"><?php echo JText::_('K2_LEARN_WHAT_THIS_MEANS'); ?></span>
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label for="extraFieldsGroup"><?php echo JText::_('K2_ASSOCIATED_EXTRA_FIELDS_GROUP');	?></label>
							</td>
							<td class="adminK2RightCol">
								<?php echo $this->lists['extraFieldsGroup']; ?>
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label><?php echo JText::_('K2_PUBLISHED');	?></label>
							</td>
							<td class="adminK2RightCol k2RadioButtonContainer">
								<?php echo $this->lists['published']; ?>
							</td>
						</tr>
						<tr>
							<td class="adminK2LeftCol">
								<label for="access"><?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<?php echo $this->lists['access']; ?>
							</td>
						</tr>
						<?php if(isset($this->lists['language'])): ?>
						<tr>
							<td class="adminK2LeftCol">
								<label><?php echo JText::_('K2_LANGUAGE'); ?></label>
							</td>
							<td class="adminK2RightCol">
								<?php echo $this->lists['language']; ?>
							</td>
						</tr>
						<?php endif; ?>
					</table>
					
					<!-- Tabs start here -->
					<div class="simpleTabs" id="k2Tabs">
						<ul class="simpleTabsNavigation">
							<li id="tabContent"><a href="#k2Tab1"><?php echo JText::_('K2_DESCRIPTION'); ?></a></li>
							<li id="tabImage"><a href="#k2Tab2"><?php echo JText::_('K2_IMAGE'); ?></a></li>
						</ul>
						
						<!-- Tab content -->
						<div class="simpleTabsContent" id="k2Tab1">
							<div class="k2ItemFormEditor"> <span class="k2ItemFormEditorTitle"> <?php echo JText::_('K2_CATEGORY_DESCRIPTION'); ?> </span> <?php echo $this->editor; ?>
								<div class="dummyHeight"></div>
								<div class="clr"></div>
							</div>
							<div class="clr"></div>
						</div>
						
						<!-- Tab image -->
						<div class="simpleTabsContent" id="k2Tab2">
							<table class="admintable table">
								<tr>
									<td align="right" class="key">
										<?php echo JText::_('K2_CATEGORY_IMAGE'); ?>
									</td>
									<td>
										<input type="file" name="image" class="fileUpload" />
										<i>(<?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?>)</i>
										<br />
										<br />
										<input type="text" name="existingImage" id="existingImageValue" class="text_area" readonly />
										<input type="button" value="<?php echo JText::_('K2_BROWSE_SERVER'); ?>" id="k2ImageBrowseServer"  />
										<br />
										<br />
										<?php if (!empty($this->row->image)): ?>
										<img src="<?php echo JURI::root(true); ?>/media/k2/categories/<?php echo $this->row->image; ?>" alt="<?php echo $this->row->name; ?>" class="k2AdminImage" />
										<input type="checkbox" name="del_image" id="del_image" />
										<label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
										<?php endif; ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<!-- Tabs end here --> 
					
					<!-- K2 Category Plugins -->
					<?php if (count($this->K2Plugins)): ?>
					<div class="itemPlugins">
						<?php foreach ($this->K2Plugins as $K2Plugin): ?>
						<?php if(!is_null($K2Plugin)): ?>
						<fieldset class="adminform">
							<legend><?php echo $K2Plugin->name; ?></legend>
							<?php echo $K2Plugin->fields; ?>
						</fieldset>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<div class="clr"></div>
				</td>
				<td id="adminFormK2Sidebar" class="xmlParamsFields">
					<div id="k2Accordion">
						<h3><a href="#"><?php echo JText::_('K2_CATEGORY_ITEM_LAYOUT'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('category-item-layout') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'category-item-layout'); ?>
							<?php endif; ?>
						</div>
						<h3><a href="#"><?php echo JText::_('K2_CATEGORY_VIEW_OPTIONS'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('category-view-options') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'category-view-options'); ?>
							<?php endif; ?>
						</div>
						<h3><a href="#"><?php echo JText::_('K2_ITEM_IMAGE_OPTIONS'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('item-image-options') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'item-image-options'); ?>
							<?php endif; ?>
						</div>
						<h3><a href="#"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS_IN_CATEGORY_LISTINGS'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('item-view-options-listings') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'item-view-options-listings'); ?>
							<?php endif; ?>
						</div>
						<h3><a href="#"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('item-view-options') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'item-view-options'); ?>
							<?php endif; ?>
						</div>
						<h3><a href="#"><?php echo JText::_('K2_METADATA_INFORMATION'); ?></a></h3>
						<div>
							<?php if(K2_JVERSION != '15'): ?>
							<fieldset class="panelform">
								<ul class="adminformlist">
									<?php foreach ($this->form->getFieldset('category-metadata-information') as $field): ?>
									<li>
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
								</ul>
							</fieldset>
							<?php else: ?>
							<?php echo $this->form->render('params', 'category-metadata-information'); ?>
							<?php endif; ?>
						</div>
						<?php if($this->aceAclFlag): ?>
						<h3><a href="#"><?php echo JText::_('AceACL') . ' ' . JText::_('COM_ACEACL_COMMON_PERMISSIONS'); ?></a></h3>
						<div><?php AceaclApi::getWidget('com_k2.category.'.$this->row->id, true); ?></div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="category" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
