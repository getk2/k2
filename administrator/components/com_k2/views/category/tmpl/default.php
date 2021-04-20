<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="k2CategoryForm">
    <!-- Top Nav Tabs START here -->
    <div id="k2FormTopNav" class="k2Tabs">
        <div class="k2NavTabsWrapper">
            <h2><?php echo JText::_('K2_EDIT_CATEGORY'); ?></h2>
            <ul class="k2NavTabs">
                <li id="tabContent"><a href="#k2TabBasic"><i class="fa fa-home"></i><?php echo JText::_('K2_BASIC'); ?></a></li>
                <li><a href="#k2TabPubAndMeta"><i class="fa fa-info-circle"></i><?php echo JText::_('K2_PUBLISHING_AND_METADATA'); ?></a></li>
                <li id="tabContent"><a href="#k2TabDisplaySet"><i class="fa fa-desktop"></i><?php echo JText::_('K2_DISPLAY_SETTINGS'); ?></a></li>
            </ul>
        </div>

        <!-- BASIC [start] -->
        <div class="k2NavTabContent" id="k2TabBasic">
            <?php if($this->row->id): ?>
            <div id="k2ID"><strong><?php echo JText::_('K2_ID'); ?></strong> <?php echo $this->row->id; ?></div>
            <?php endif; ?>

            <div class="k2ui-table-basic">
                <div class="k2ui-field-label">
                    <label for="name"><?php echo JText::_('K2_TITLE'); ?></label>
                </div>
                <div class="k2ui-field-value k2ui-title">
                    <input type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" maxlength="250" />
                </div>
                <div class="k2ui-field-label">
                    <label for="alias"><?php echo JText::_('K2_TITLE_ALIAS'); ?></label>
                </div>
                <div class="k2ui-field-value k2ui-title-alias">
                    <input type="text" name="alias" value="<?php echo $this->row->alias; ?>" maxlength="250" />
                </div>
            </div>
            <div class="k2ui-table-additional">
                <div class="k2ui-field-label">
                    <label for="parent"><?php echo JText::_('K2_PARENT_CATEGORY'); ?></label>
                </div>
                <div class="k2ui-field-value">
                    <?php echo $this->lists['parent']; ?>
                </div>
                <div class="k2ui-field-label">
                    <label for="paramsinheritFrom" class="hasTip" title="<?php echo JText::_('K2_INHERIT_PARAMETER_OPTIONS_FROM_CATEGORY'); ?>::<?php echo JText::_('K2_SETTING_THIS_OPTION_WILL_MAKE_THIS_CATEGORY_INHERIT_ALL_PARAMETERS_FROM_ANOTHER_CATEGORY_THUS_YOU_DONT_HAVE_TO_RESET_ALL_OPTIONS_IN_THIS_ONE_IF_THEY_ARE_THE_SAME_WITH_ANOTHER_CATEGORYS_THIS_SETTING_IS_VERY_USEFUL_WHEN_YOU_ARE_CREATING_CHILD_CATEGORIES_WHICH_SHARE_THE_SAME_PARAMETERS_WITH_THEIR_PARENT_CATEGORY_EG_IN_THE_CASE_OF_A_CATALOG_OR_A_NEWS_PORTALMAGAZINE'); ?>">
                        <?php echo JText::_('K2_INHERIT_PARAMETER_OPTIONS_FROM_CATEGORY'); ?>
                        <div class="k2ui-notice"><?php echo JText::_('K2_LEARN_WHAT_THIS_MEANS'); ?> <i class="fa fa-question-circle" aria-hidden="true"></i></div>
                    </label>
                </div>
                <div class="k2ui-field-value">
                    <?php echo $this->lists['inheritFrom']; ?>
                </div>
                <div class="k2ui-field-label">
                    <label for="extraFieldsGroup"><?php echo JText::_('K2_ASSOCIATED_EXTRA_FIELDS_GROUP');  ?></label>
                </div>
                <div class="k2ui-field-value">
                    <?php echo $this->lists['extraFieldsGroup']; ?>
                </div>
                <div class="k2ui-field-label">
                    <label><?php echo JText::_('K2_PUBLISHED'); ?></label>
                </div>
                <div class="k2ui-field-value k2ui-button-set">
                    <?php echo $this->lists['published']; ?>
                </div>
                <div class="k2ui-field-label">
                    <label for="access"><?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
                </div>
                <div class="k2ui-field-value">
                    <?php echo $this->lists['access']; ?>
                </div>
                <?php if(isset($this->lists['language'])): ?>
                <div class="k2ui-field-label">
                    <label><?php echo JText::_('K2_LANGUAGE'); ?></label>
                </div>
                <div class="k2ui-field-value">
                    <?php echo $this->lists['language']; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="clr"></div>

            <!-- Tabs start here -->
            <div class="k2Tabs" id="k2Tabs">
                <ul class="k2TabsNavigation">
                    <li id="tabContent">
                        <a href="#k2TabContent"><i class="fa fa-file-text-o"></i><?php echo JText::_('K2_DESCRIPTION'); ?></a>
                    </li>
                    <li id="tabImage">
                        <a href="#k2TabImage"><i class="fa fa-camera"></i><?php echo JText::_('K2_IMAGE'); ?></a>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="k2TabsContent" id="k2TabContent">
                    <div class="k2ItemFormEditor">
                        <!--<span class="k2ItemFormEditorTitle"><?php echo JText::_('K2_CATEGORY_DESCRIPTION'); ?></span>-->
                        <?php echo $this->editor; ?>
                        <div class="dummyHeight"></div>
                        <div class="clr"></div>
                    </div>
                    <div class="clr"></div>
                </div>

                <!-- Tab image -->
                <div class="k2TabsContent k2TabsContentLower" id="k2TabImage">
                    <div class="itemAdditionalField">
                        <div class="itemAdditionalValue">
                            <label><?php echo JText::_('K2_CATEGORY_IMAGE'); ?></label>
                        </div>
                        <div class="itemAdditionalData">
                            <input type="file" name="image" class="fileUpload k2Selector" accept="image/*" />
                            <i>(<?php echo JText::_('K2_MAX_UPLOAD_SIZE'); ?>: <?php echo ini_get('upload_max_filesize'); ?>)</i>
                            <span class="sep"><?php echo JText::_('K2_OR'); ?></span>
                            <input type="text" name="existingImage" id="existingImageValue" class="text_area" readonly />
                            <input type="button" value="<?php echo JText::_('K2_BROWSE_SERVER'); ?>" id="k2ImageBrowseServer"  />
                        </div>
                    </div>
                    <?php if (!empty($this->row->image)): ?>
                    <div class="itemAdditionalField">
                        <div class="itemAdditionalValue">
                            <label><?php echo JText::_('K2_ITEM_IMAGE_PREVIEW'); ?></label>
                        </div>
                        <div class="itemAdditionalData">
                            <a data-fancybox="images" data-caption="<?php echo $this->row->name; ?>" href="<?php echo JURI::root(true); ?>/media/k2/categories/<?php echo $this->row->image; ?>" title="<?php echo JText::_('K2_CLICK_ON_IMAGE_TO_PREVIEW_IN_ORIGINAL_SIZE'); ?>">
                                <img src="<?php echo JURI::root(true); ?>/media/k2/categories/<?php echo $this->row->image; ?>" alt="<?php echo $this->row->name; ?>" class="k2AdminImage" />
                            </a>
                            <br />
                            <input type="checkbox" name="del_image" id="del_image" />
                            <label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- image tab ends here -->
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
        </div>
        <!-- BASIC [finish] -->

        <!-- META [start] -->
        <div class="k2NavTabContent" id="k2TabPubAndMeta">
            <div class="xmlParamsFields limitWidth">
                <h3><?php echo JText::_('K2_METADATA_INFORMATION'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('category-metadata-information') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <?php foreach($this->form->getParams('params', 'category-metadata-information') as $param): ?>
                        <li>
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
        <!-- META [finish] -->

        <!-- DISPLAY SETTINGS [start] -->
        <div class="k2NavTabContent" id="k2TabDisplaySet">
            <ul class="k2ScrollSpyMenu">
                <li><a href="#catLayoutOptions"><?php echo JText::_('K2_CATEGORY_ITEM_LAYOUT'); ?><i class="right fa fa-angle-right"></i></a></li>
                <li><a href="#catViewOptions"><?php echo JText::_('K2_CATEGORY_VIEW_OPTIONS'); ?><i class="right fa fa-angle-right"></i></a></li>
                <li><a href="#catImageOptions"><?php echo JText::_('K2_ITEM_IMAGE_OPTIONS'); ?><i class="right fa fa-angle-right"></i></a></li>
                <li><a href="#catItemsOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS_IN_CATEGORY_LISTINGS'); ?><i class="right fa fa-angle-right"></i></a></li>
                <li><a href="#catItemOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS'); ?><i class="right fa fa-angle-right"></i></a></li>
            </ul>

            <div class="k2ScrollingContent xmlParamsFields">
            <h3 id="catLayoutOptions"><?php echo JText::_('K2_CATEGORY_ITEM_LAYOUT'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('category-item-layout') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <?php foreach($this->form->getParams('params', 'category-item-layout') as $param): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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

                <h3 id="catViewOptions"><?php echo JText::_('K2_CATEGORY_VIEW_OPTIONS'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('category-view-options') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <?php foreach($this->form->getParams('params', 'category-view-options') as $param): ?>
                        <li>
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

                <h3 id="catImageOptions"><?php echo JText::_('K2_ITEM_IMAGE_OPTIONS'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('item-image-options') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <?php foreach($this->form->getParams('params', 'item-image-options') as $param): ?>
                        <li>
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

                <h3 id="catItemsOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS_IN_CATEGORY_LISTINGS'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('item-view-options-listings') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <li>
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

                <h3 id="catItemOptions"><?php echo JText::_('K2_ITEM_VIEW_OPTIONS'); ?></h3>
                <fieldset class="panelform">
                    <ul class="adminformlist">
                        <?php if(version_compare( JVERSION, '1.6.0', 'ge' )): ?>
                        <?php foreach ($this->form->getFieldset('item-view-options') as $field): ?>
                        <li<?php if($field->type=='header') echo ' class="headerElement"';?>>
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
                        <li>
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
        <!-- DISPLAY SETTINGS [finish] -->
    </div>
    <!-- Top Nav Tabs FINISH here -->

    <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="category" />
    <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
    <?php echo JHTML::_('form.token'); ?>
</form>
