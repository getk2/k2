<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton){
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		if (\$K2.trim(\$K2('#name').val()) == '') {
			alert( '".JText::_('K2_GROUP_NAME_CANNOT_BE_EMPTY', true)."' );
		} else {
			submitform( pressbutton );
		}
	};
");

?>

<form action="index.php" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
	<div class="xmlParamsFields k2GenericForm">
		<h3>
			<?php if($this->row->id): ?>
				<?php echo JText::_('K2_EDIT_GROUP'); ?>
			<?php else: ?>
				<?php echo JText::_('K2_ADD_NEW_GROUP'); ?>
			<?php endif; ?>
        </h3>
        <ul class="adminformlist">
            <li>
                <div class="paramLabel">
                	<?php echo JText::_('K2_GROUP_NAME'); ?>
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
