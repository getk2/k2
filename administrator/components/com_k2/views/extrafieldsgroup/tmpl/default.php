<?php
/**
 * @version    2.11.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div class="xmlParamsFields k2GenericForm">
        <h3>
            <?php if($this->row->id): ?>
            <?php echo JText::_('K2_EDIT_EXTRA_FIELD_GROUP'); ?>
            <?php else: ?>
            <?php echo JText::_('K2_ADD_EXTRA_FIELD_GROUP'); ?>
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
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="extrafieldsgroup" />
    <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
    <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    <?php echo JHTML::_('form.token'); ?>
</form>
