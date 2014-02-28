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

<form action="index.php" method="post" name="adminForm">
	<fieldset>
		<div style="float:right;">
			<button onclick="submitbutton('save');window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 700);" type="button"><?php echo JText::_('K2_SAVE'); ?></button>
			<button onclick="window.parent.document.getElementById('sbox-window').close();" type="button"><?php echo JText::_('K2_CANCEL'); ?></button>
		</div>
		<div class="configuration">
			<?php echo JText::_('K2_PARAMETERS')?>
		</div>
		<div class="clr"></div>
	</fieldset>
	<?php echo $this->pane->startPane('settings'); ?>
	<?php foreach($this->params->getGroups() as $group=>$value): ?>
	<?php echo $this->pane->startPanel(JText::_($group), $group.'-tab'); ?>
	<?php echo $this->params->render('params', $group); ?>
	<?php echo $this->pane->endPanel(); ?>
	<?php endforeach; ?>
	<?php echo $this->pane->endPane(); ?>
	
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="settings" />
	<input type="hidden" id="task" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
