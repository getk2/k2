<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2020 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="k2GenericForm">
        <div class="xmlParamsFields">
            <h3>
                <?php if ($this->row->id): ?>
                <?php echo JText::_('K2_EDIT_USER_GROUP'); ?>
                <?php else: ?>
                <?php echo JText::_('K2_ADD_USER_GROUP'); ?>
                <?php endif; ?>
            </h3>
            <ul class="adminformlist">
                <li>
                    <div class="paramLabel">
                        <label for="name"><?php echo JText::_('K2_GROUP_NAME'); ?></label>
                    </div>
                    <div class="paramValue">
                        <input class="text_area k2TitleBox" type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" size="50" maxlength="250" />
                    </div>
                </li>
            </ul>
        </div>
        <div class="xmlParamsFields">
            <h3 class="paramHeader"><?php echo JText::_('K2_ASSIGN_PERMISSIONS_FOR_THIS_GROUP'); ?></h3>
            <?php if (K2_JVERSION == '15'): ?>
            <?php echo $this->form->render('params'); ?>
            <?php else: ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <?php foreach($this->form->getFieldset('user-permissions') as $field): ?>
                    <li>
                        <?php if ($field->type=='header'): ?>
                        <div class="paramValueHeader"><?php echo $field->input; ?></div>
                        <?php elseif ($field->type=='Spacer'): ?>
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
            <?php endif; ?>
        </div>
        <div class="xmlParamsFields">
            <h3 class="paramHeader"><?php echo JText::_('K2_ASSIGN_GROUP_PERMISSIONS_TO_THESE_CATEGORIES'); ?></h3>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li>
                        <div class="paramLabel"><label><?php echo JText::_('K2_FILTER'); ?></label></div>
                        <div class="paramValue">
                            <input id="categories-all" type="radio" name="categories" value="all" <?php if ($this->categories == 'all') echo ' checked="checked"'; ?> />
                            <label for="categories-all"><?php echo JText::_('K2_ALL'); ?></label>
                            <input id="categories-none" type="radio" name="categories" value="none" <?php if ($this->categories == 'none') echo ' checked="checked"'; ?> />
                            <label for="categories-none"><?php echo JText::_('K2_NONE'); ?></label>
                            <input id="categories-select" type="radio" name="categories" value="select" <?php if ($this->categories != 'all' && $this->categories != 'none') echo ' checked="checked"'; ?> />
                            <label for="categories-select"><?php echo JText::_('K2_SELECT_FROM_LIST'); ?></label>
                        </div>
                        <div class="clr"></div>
                    </li>
                    <li>
                        <div class="paramLabel"><span class="editlinktip"><label for="paramscategories" id="paramscategories-lbl"><?php echo JText::_('K2_CATEGORIES'); ?></label></span></div>
                        <div class="paramValue"><?php echo $this->lists['categories']; ?></div>
                        <div class="clr"></div>
                    </li>
                    <li>
                        <div class="paramLabel"><span class="editlinktip"><label for="paramsinheritance" id="paramsinheritanceh-lbl"><?php echo JText::_('K2_AUTOMATICALLY_ASSIGN_GROUP_PERMISSIONS_TO_THE_CHILDREN_OF_SELECTED_CATEGORIES'); ?></label></span></div>
                        <div class="paramValue"><?php echo $this->lists['inheritance']; ?></div>
                        <div class="clr"></div>
                    </li>
                </ul>
            </fieldset>
        </div>
    </div>
    <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="usergroup" />
    <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
    <?php echo JHTML::_('form.token'); ?>
</form>
