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

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="k2MoveDialogContainer">
        <div class="k2NavTabsWrapper">
            <h2><?php echo JText::_('K2_MOVE'); ?></h2>
        </div>
        <div id="k2MoveDialog">
            <fieldset class="K2MoveDialogList">
                <legend><?php echo JText::_('K2_TARGET_JOOMLA_USER_GROUP'); ?></legend>
                <?php echo $this->lists['group']; ?>
            </fieldset>
            <fieldset class="K2MoveDialogList">
                <legend><?php echo JText::_('K2_TARGET_K2_USER_GROUP'); ?></legend>
                <?php echo $this->lists['k2group']; ?>
            </fieldset>
            <fieldset class="K2MoveDialogContents">
                <legend>(<?php echo count($this->rows); ?>) <?php echo JText::_('K2_USERS_BEING_MOVED'); ?></legend>
                <ol>
                    <?php foreach ($this->rows as $row): ?>
                    <li>
                        <?php echo $row->name; ?>
                        <input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
                    </li>
                    <?php endforeach; ?>
                </ol>
            </fieldset>
        </div>
    </div>
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
    <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
    <?php echo JHTML::_('form.token'); ?>
</form>
