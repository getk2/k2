<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset style="float:left;">
		<legend><?php echo JText::_('K2_TARGET_JOOMLA_USER_GROUP'); ?></legend>
		<?php echo $this->lists['group']; ?>
	</fieldset>
	<fieldset style="float:left;">
		<legend><?php echo JText::_('K2_TARGET_K2_USER_GROUP'); ?></legend>
		<?php echo $this->lists['k2group']; ?>
	</fieldset>
	<fieldset style="clear:both;">
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
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
