<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
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
                    <label><?php echo JText::_('K2_GENDER'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->lists['gender']; ?>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_USER_GROUP'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->lists['userGroup']; ?>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_DESCRIPTION'); ?></label>
                </div>
                <div class="paramValue">
                    <?php echo $this->editor; ?>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_USER_IMAGE_AVATAR'); ?></label>
                </div>
                <div class="paramValue">
                    <input type="file" name="image" accept="image/*" />
                    <?php if ($this->row->image):
                        $avatarTimestamp = '';
                        $avatarFile = JPATH_SITE.'/media/k2/users/'.$this->row->image;
                        if (file_exists($avatarFile) && filemtime($avatarFile)) {
                            $avatarTimestamp = '?t='.date("Ymd_Hi", filemtime($avatarFile));
                        }
                        $avatar = JURI::root(true).'/media/k2/users/'.$this->row->image.$avatarTimestamp;
                    ?>
                    <div class="k2ImagePreview">
                        <a href="<?php echo $avatar; ?>" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" data-fancybox="gallery" data-caption="<?php echo $this->row->name; ?>">
                            <img class="k2AdminImage" src="<?php echo $avatar; ?>" alt="<?php echo $this->row->name; ?>" />
                        </a>
                        <br />
                        <input type="checkbox" name="del_image" id="del_image" />
                        <label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
                    </div>
                    <?php endif; ?>
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_URL'); ?></label>
                </div>
                <div class="paramValue">
                    <input type="text" size="50" value="<?php echo $this->row->url; ?>" name="url" />
                </div>
            </li>
            <li>
                <div class="paramLabel">
                    <label><?php echo JText::_('K2_NOTES'); ?></label>
                </div>
                <div class="paramValue">
                    <textarea name="notes" cols="60" rows="5"><?php echo $this->row->notes; ?></textarea>
                </div>
            </li>
            <?php if (count(array_filter($this->K2Plugins))): ?>
            <?php foreach ($this->K2Plugins as $K2Plugin): ?>
            <?php if (!is_null($K2Plugin)): ?>
            <li>
                <div class="userPlugins pluginIs<?php echo preg_replace('/[^\p{L}\p{N}_]/u', '', ucwords(strtolower($K2Plugin->name))); ?>">
                    <h3><?php echo $K2Plugin->name; ?></h3>
                    <div class="userPluginFields">
                        <?php echo $K2Plugin->fields; ?>
                    </div>
                </div>
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
