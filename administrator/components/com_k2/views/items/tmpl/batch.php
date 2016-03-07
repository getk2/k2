<?php
/**
 * @version		2.7.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" method="post" id="adminForm" name="adminForm">

	<div>
	    <h3><?php echo JText::_('K2_BATCH_OPERATIONS'); ?></h3>
	    <span>
	        <strong><?php echo count($this->ids); ?></strong>
	        <?php echo JText::_('K2_SELECTED_ITEMS'); ?>
	    </span>
	</div>

	<input type="radio" name="batchMode" value="apply" id="assign" checked="checked" />
	<label for="assign"><?php echo JText::_('K2_ASSIGN'); ?></label>

	<input type="radio" name="batchMode" value="clone" id="clone" />
	<label for="clone"><?php echo JText::_('K2_CREATE_DUPLICATE'); ?></label>

	<label><?php echo JText::_('K2_CATEGORY'); ?></label>
	<?php echo $this->lists['categories']; ?>

	<label><?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
	<?php echo $this->lists['access']; ?>

	<label><?php echo JText::_('K2_AUTHOR'); ?></label>
	<?php echo $this->lists['author']; ?>

	<?php if(isset($this->lists['language'])): ?>
		<label><?php echo JText::_('K2_LANGUAGE'); ?></label>
		<?php echo $this->lists['language']; ?>
	<?php endif; ?>

	<?php foreach ($this->ids as $id): ?>
	<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
	<?php endforeach; ?>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="items" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>

</form>
