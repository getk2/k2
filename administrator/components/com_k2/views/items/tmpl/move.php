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

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		if (\$K2.trim(\$K2('#category').val()) == '') {
			alert( '".JText::_('K2_YOU_MUST_SELECT_A_TARGET_CATEGORY', true)."' );
		} else {
			submitform( pressbutton );
		}
	};
");

?>

<form action="index.php" method="post" id="adminForm" name="adminForm">
	<div class="header-alt margin">
		<div class="row row-nomax">
			<h3><?php echo JText::_('K2_MOVE'); ?></h3>
		</div>
	</div>
	<div class="row">
		<div class="column small-12 large-6 small-centered">
			<div class="row">
				<div class="column small-12 large-6 action-alt">
					<label class="label-alt margin">
						<i class="fa fa-folder"></i>
						<?php echo JText::_('K2_TARGET_CATEGORY'); ?>
					</label>
					<?php echo $this->lists['categories']; ?>
				</div>
				<div class="column small-12 large-6 clearfix action-alt">
					<label class="label-alt">
						<i class="fa fa-arrows"></i>
						<?php echo count($this->rows); ?> <?php echo JText::_('K2_ITEMS_BEING_MOVED'); ?>
					</label>
					<ol>
						<?php foreach ($this->rows as $row): ?>
						<li><?php echo $row->title; ?><input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" /></li>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
</form>
