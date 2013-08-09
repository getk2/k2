<?php
/**
 * @version		$Id: default.php 1991 2013-07-04 14:59:52Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
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
		if (\$K2.trim(\$K2('#group').val()) == '') {
			alert( '".JText::_('K2_PLEASE_SELECT_A_GROUP_OR_CREATE_A_NEW_ONE', true)."' );
		}
		else if (\$K2.trim(\$K2('#name').val()) == '') {
			alert( '".JText::_('K2_NAME_CANNOT_BE_EMPTY', true)."' );
		}
		else if (\$K2('#type').val() == '0') {
			alert( '".JText::_('K2_PLEASE_SELECT_THE_TYPE_OF_THE_EXTRA_FIELD', true)."' );
		}
		else {
			submitform( pressbutton );
		}
	}
");

?>

<form action="index.php" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
  <table class="admintable table">
    <tr>
      <td class="key"><?php echo JText::_('K2_NAME'); ?></td>
      <td><input class="text_area k2TitleBox" type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" size="50" maxlength="250" /></td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_('K2_ALIAS'); ?></td>
      <td><input id="alias" type="text" name="alias" value="<?php echo $this->row->alias; ?>" /></td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_('K2_PUBLISHED'); ?></td>
      <td><?php echo $this->lists['published']; ?></td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_('K2_GROUP'); ?></td>
      <td>
        <?php echo $this->lists['group']; ?>
        <div id="groupContainer">
        	<span><?php echo JText::_('K2_NEW_GROUP_NAME'); ?></span>
        	<input id="group" type="text" name="group" value="<?php echo $this->row->group; ?>" />
        </div>
      </td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_('K2_TYPE'); ?></td>
      <td><?php echo $this->lists['type']; ?></td>
    </tr>
    <tr id="k2ExtraFieldsRequiredFlag" <?php if($this->row->type == 'header') { echo 'style="display: none;"'; } ?>>
      <td class="key"><?php echo JText::_('K2_REQUIRED'); ?></td>
      <td>
      	 <input id="required-yes" type="radio" name="required" value="1" <?php if($this->row->required) { echo 'checked="checked"';} ?>/><label for="required-yes"><?php echo JText::_('K2_YES'); ?></label>
      	 <input id="required-no" type="radio" name="required" value="0"<?php if(!$this->row->required) { echo 'checked="checked"';} ?>/><label for="required-no"><?php echo JText::_('K2_NO'); ?></label>
      </td>
    </tr>
    <tr id="k2ExtraFieldsShowNullFlag" <?php if($this->row->type != 'select' && $this->row->type != 'multipleSelect') { echo 'style="display: none;"'; } ?>>
      <td class="key"><?php echo JText::_('K2_SHOW_NULL'); ?></td>
      <td>
      	 <input id="showNull-yes" type="radio" name="showNull" value="1" <?php if($this->row->showNull) { echo 'checked="checked"';} ?>/><label for="showNull-yes"><?php echo JText::_('K2_YES'); ?></label>
      	 <input id="showNull-no" type="radio" name="showNull" value="0"<?php if(!$this->row->showNull) { echo 'checked="checked"';} ?>/><label for="showNull-no"><?php echo JText::_('K2_NO'); ?></label>
      </td>
    </tr>
    <tr id="k2ExtraFieldsDisplayInFrontEndFlag" <?php if($this->row->type != 'header') { echo 'style="display: none;"'; } ?>>
      <td class="key"><?php echo JText::_('K2_DISPLAY_IN_THE_FRONTEND'); ?></td>
      <td>
      	 <input id="displayInFrontEnd-yes" type="radio" name="displayInFrontEnd" value="1" <?php if($this->row->displayInFrontEnd) { echo 'checked="checked"';} ?>/><label for="displayInFrontEnd-yes"><?php echo JText::_('K2_YES'); ?></label>
      	 <input id="displayInFrontEnd-no" type="radio" name="displayInFrontEnd" value="0"<?php if(!$this->row->displayInFrontEnd) { echo 'checked="checked"';} ?>/><label for="displayInFrontEnd-no"><?php echo JText::_('K2_NO'); ?></label>
      </td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_('K2_DEFAULT_VALUES'); ?></td>
      <td><div id="exFieldsTypesDiv"></div></td>
    </tr>
  </table>

  <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
  <input type="hidden" name="isNew" id="isNew" value="<?php echo ($this->row->group)?'0':'1'; ?>" />
  <input type="hidden" name="option" value="com_k2" />
  <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
  <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
  <input type="hidden" id="value" name="value" value="<?php echo htmlentities($this->row->value); ?>" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>
