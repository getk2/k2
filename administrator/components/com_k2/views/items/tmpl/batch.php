<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" method="post" id="adminForm" name="adminForm">
	<div class="header-alt">
		<div class="row row-nomax">
		    <h3 class="k2FLeft"><?php echo JText::_('K2_BATCH_OPERATIONS'); ?></h3>
		    <span class="k2FRight">
		        <strong><?php echo count($this->ids); ?></strong>
		        <?php echo JText::_('K2_SELECTED_ITEMS'); ?>
		    </span>
		</div>
	</div>
	<div class="subheader-alt">
		<div class="row">
			<div class="column small-12 large-6 small-centered">
				<div class="row">
					<div class="column small-12 large-6">
						<input type="radio" name="batchMode" value="apply" id="assign" checked="checked" />
						<label for="assign"><?php echo JText::_('K2_ASSIGN'); ?></label>
					</div>
					<div class="column small-12 large-6 clearfix">
						<input type="radio" name="batchMode" value="clone" id="clone" />
						<label for="clone"><?php echo JText::_('K2_CREATE_DUPLICATE'); ?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="column small-12 large-6 small-centered">
			<div class="row">
				<div class="column small-12 large-6 action-alt">
					<label class="label-alt"><i class="fa fa-folder-open"></i> <?php echo JText::_('K2_CATEGORY'); ?></label>
					<?php echo $this->lists['categories']; ?>
				</div>
				<div class="column small-12 large-6 clearfix action-alt">
					<label class="label-alt"><i class="fa fa-unlock-alt"></i> <?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
					<?php echo $this->lists['access']; ?>
				</div>
				<div class="column small-12 large-6 action-alt">
					<label class="label-alt"><i class="fa fa-user"></i> <?php echo JText::_('K2_AUTHOR'); ?></label>
					<?php echo $this->lists['author']; ?>
				</div>
				<div class="column small-12 large-6 clearfix action-alt">
					<?php if(isset($this->lists['language'])): ?>
					<label class="label-alt"><i class="fa fa-globe"></i> <?php echo JText::_('K2_LANGUAGE'); ?></label>
					<?php echo $this->lists['language']; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php foreach ($this->ids as $id): ?>
	<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
	<?php endforeach; ?>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="items" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>

</form>
