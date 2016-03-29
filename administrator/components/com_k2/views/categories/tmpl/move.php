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
			alert( '".JText::_('K2_YOU_MUST_SELECT_A_PARENT_CATEGORY', true)."' );
		} else {
			submitform( pressbutton );
		}
	};
");

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset>
		<legend><?php echo JText::_('K2_PARENT_CATEGORY'); ?></legend>
		<?php echo $this->lists['categories']; ?>
	</fieldset>
	<fieldset>
		<legend>(<?php echo count($this->rows); ?>) <?php echo JText::_('K2_CATEGORIES_BEING_MOVED'); ?></legend>
		<ol>
			<?php foreach ($this->rows as $row): ?>
			<li><?php echo $row->name; ?><input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" /></li>
			<?php endforeach; ?>
		</ol>
	</fieldset>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
</form>
