<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
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
		if (\$K2.trim(\$K2('#name').val())=='') {
			alert( '".JText::_('K2_TAG_CANNOT_BE_EMPTY', true)."' );
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
              <?php echo JText::_('K2_EDIT_TAG'); ?>
          <?php else: ?>
              <?php echo JText::_('K2_ADD_NEW_TAG'); ?>
          <?php endif; ?>
        </h3>

        <ul class="adminformlist">
            <li>
            	<div class="paramLabel">
					<label><?php echo JText::_('K2_NAME'); ?></label>
				</div>
				<div class="paramValue">
					<input class="text_area k2TitleBox" type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" size="50" maxlength="250" />
				</div>
			</li>
			<li>
				<div class="paramLabel">
					<label><?php echo JText::_('K2_PUBLISHED'); ?></label>
				</div>
				<div class="paramValue">
					<?php echo $this->lists['published']; ?>
				</div>
			</li>
		</ul>
	</div>

	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="tag" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
