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

?>

<form action="index.php" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
    <div class="xmlParamsFields k2GenericForm">
        <h3>
            <?php if($this->row->id): ?>
            <?php echo JText::_('K2_EDIT_EXTRA_FIELD'); ?>
            <?php else: ?>
            <?php echo JText::_('K2_ADD_EXTRA_FIELD'); ?>
            <?php endif; ?>
        </h3>
        <ul class="adminformlist">
            <li>
                <div class="paramLabel">
                    <label for="name"><?php echo JText::_('K2_NAME'); ?></label>
                </div>
                <div class="paramValue">
                    <input class="text_area k2TitleBox" type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" size="50" maxlength="250" />
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label for="alias"><?php echo JText::_('K2_ALIAS'); ?></label>
                </div>
                <div class="paramValue">
                    <input type="text" name="alias" id="alias" value="<?php echo $this->row->alias; ?>" />
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_PUBLISHED'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->lists['published']; ?>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_GROUP'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->lists['group']; ?>
                    <div id="groupContainer">
                        <input id="group" type="text" name="group" value="<?php echo $this->row->group; ?>" placeholder="<?php echo JText::_('K2_NEW_GROUP_NAME'); ?>" />
                    </div>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_TYPE'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->lists['type']; ?>
                </div>
            </li>
            <li id="k2app-ef-header-flag" <?php if($this->row->type == 'header') echo ' style="display:none;"'; ?>>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_REQUIRED'); ?></label>
                </div>
                <div class="paramValue">
                    <input id="required-no" type="radio" name="required" value="0" <?php if(!$this->row->required) echo ' checked="checked"'; ?> />
                    <label for="required-no"><?php echo JText::_('K2_NO'); ?></label>
                    <input id="required-yes" type="radio" name="required" value="1" <?php if($this->row->required) echo ' checked="checked"'; ?> />
                    <label for="required-yes"><?php echo JText::_('K2_YES'); ?></label>
                </div>
            </li>
            <li id="k2ExtraFieldsShowNullFlag" <?php if($this->row->type != 'select' && $this->row->type != 'multipleSelect') echo ' style="display: none;"'; ?>>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_SHOW_NULL'); ?></label>
                </div>
                <div class="paramValue">
                    <input id="showNull-no" type="radio" name="showNull" value="0" <?php if(!$this->row->showNull) echo ' checked="checked"'; ?> />
                    <label for="showNull-no"><?php echo JText::_('K2_NO'); ?></label>
                    <input id="showNull-yes" type="radio" name="showNull" value="1" <?php if($this->row->showNull) echo ' checked="checked"'; ?> />
                    <label for="showNull-yes"><?php echo JText::_('K2_YES'); ?></label>
                </div>
            </li>
            <li id="k2ExtraFieldsDisplayInFrontEndFlag" <?php if($this->row->type != 'header') echo ' style="display:none;"'; ?>>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_DISPLAY_IN_THE_FRONTEND'); ?></label>
                </div>
                <div class="paramValue">
                    <input id="displayInFrontEnd-no" type="radio" name="displayInFrontEnd" value="0" <?php if(!$this->row->displayInFrontEnd) echo ' checked="checked"'; ?> />
                    <label for="displayInFrontEnd-no"><?php echo JText::_('K2_NO'); ?></label>
                    <input id="displayInFrontEnd-yes" type="radio" name="displayInFrontEnd" value="1" <?php if($this->row->displayInFrontEnd) echo ' checked="checked"'; ?> />
                    <label for="displayInFrontEnd-yes"><?php echo JText::_('K2_YES'); ?></label>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_DEFAULT_VALUES'); ?></label>
                </div>
                <div class="paramValue">
                    <div id="k2app-ef-type-data"></div>
                </div>
            </li>
        </ul>
    </div>

    <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    <input type="hidden" name="isNew" id="isNew" value="<?php echo ($this->row->group) ? '0':'1'; ?>" />
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
    <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
    <input type="hidden" id="value" name="value" value="<?php echo htmlentities($this->row->value); ?>" />
    <?php echo JHTML::_('form.token'); ?>
</form>
