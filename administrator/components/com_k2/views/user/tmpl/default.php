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

?>

<form action="index.php" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
  <div class="xmlParamsFields k2GenericForm">
    <h3><?php echo JText::_('K2_EDIT_USER'); ?></h3>
    <ul class="adminformlist">
      <li>
        <div class="paramLabel">
          <label><?php echo JText::_('K2_NAME'); ?></label>
        </div>
        <div class="paramValue">
          <?php echo $this->row->name; ?>
        </div>
      </li>
      <li>
        <div class="paramLabel">
          <label><?php	echo JText::_('K2_GENDER'); ?></label>
        </div>
        <div class="paramValue">
          <fieldset class="k2RadioButtonContainer"><?php echo $this->lists['gender']; ?></fieldset>
        </div>
      </li>
      <li>
        <div class="paramLabel">
          <label><?php	echo JText::_('K2_USER_GROUP'); ?></label>
        </div>
        <div class="paramValue">
          <?php echo $this->lists['userGroup']; ?>
        </div>
      </li>
      <li>
        <div class="paramLabel">
          <label><?php echo JText::_('K2_DESCRIPTION'); ?></label>
        </div>
        <div class="k2ItemFormEditor">
          <label><?php echo $this->editor; ?></label>
          <div class="dummyHeight"></div>
          <div class="clr"></div>
        </div>
      </li>
      <li>
        <div class="paramLabel">
          <label><?php echo JText::_('K2_USER_IMAGE_AVATAR'); ?></label>
        </div>
        <div class="paramValue">
          <input type="file" name="image" />
        </div>
      </li>
      <?php if($this->row->image): ?>
      <li>  
        <img class="k2AdminImage k2UserAvatar" src="<?php echo JURI::root().'media/k2/users/'.$this->row->image; ?>" alt="<?php echo $this->row->name; ?>" />
        <div class="paramLabel">
          <input type="checkbox" name="del_image" id="del_image" />
          <label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
        </div>
      </li>
      <?php endif; ?>
      <li>
        <div class="paramLabel">
          <label><?php	echo JText::_('K2_URL'); ?></label>
        </div>
        <div class="paramValue">
          <input type="text" size="50" value="<?php echo $this->row->url; ?>" name="url" />
        </div>
      </li>
      <li>
        <div class="paramLabel">
          <label><?php	echo JText::_('K2_NOTES'); ?></label>
        </div>
        <div class="paramValue">
          <textarea name="notes" cols="60" rows="5"><?php echo $this->row->notes; ?></textarea>
        </div>
      </li>
      <?php if(count(array_filter($this->K2Plugins))): ?>
      <?php foreach ($this->K2Plugins as $K2Plugin): ?>
      <?php if(!is_null($K2Plugin)): ?>
      <li>
      	<fieldset class="adminform">
      		<legend><?php echo $K2Plugin->name; ?></legend>
      		<?php echo $K2Plugin->fields; ?>
      	</fieldset>
      </li>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
  <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
  <input type="hidden" name="option" value="com_k2" />
  <input type="hidden" name="view" value="user" />
  <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
  <input type="hidden" name="userID" value="<?php echo $this->row->userID; ?>" />
  <input type="hidden" name="ip" value="<?php echo $this->row->ip; ?>" />
  <input type="hidden" name="hostname" value="<?php echo $this->row->hostname; ?>" />
  <?php echo JHTML::_('form.token'); ?>
</form>