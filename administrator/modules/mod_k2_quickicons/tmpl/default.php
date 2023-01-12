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

<div class="clr"></div>

<?php if($modLogo): ?>
<div id="k2QuickIconsTitle">
    <a class="dashicon k2logo" href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=0'); ?>" title="<?php echo JText::_('K2_DASHBOARD'); ?>">
        <span>K2</span>
    </a>
</div>
<?php endif; ?>

<div id="k2QuickIcons<?php if(K2_JVERSION=='15') echo '15'; ?>" <?php if(!$modLogo): ?> class="k2NoLogo" <?php endif; ?>>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=item'); ?>">
                <i class="dashicon item-new"></i>
                <span><?php echo JText::_('K2_ADD_NEW_ITEM'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=0'); ?>">
                <i class="dashicon items"></i>
                <span><?php echo JText::_('K2_ITEMS'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=1&amp;filter_trash=0'); ?>">
                <i class="dashicon items-featured"></i>
                <span><?php echo JText::_('K2_FEATURED_ITEMS'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=1'); ?>">
                <i class="dashicon items-trashed"></i>
                <span><?php echo JText::_('K2_TRASHED_ITEMS'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=categories&amp;filter_trash=0'); ?>">
                <i class="dashicon categories"></i>
                <span><?php echo JText::_('K2_CATEGORIES'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=categories&amp;filter_trash=1'); ?>">
                <i class="dashicon categories-trashed"></i>
                <span><?php echo JText::_('K2_TRASHED_CATEGORIES'); ?></span>
            </a>
        </div>
    </div>
    <?php if(!$componentParams->get('lockTags') || $user->gid>23): ?>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=tags'); ?>">
                <i class="dashicon tags"></i>
                <span><?php echo JText::_('K2_TAGS'); ?></span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=comments'); ?>">
                <i class="dashicon comments"></i>
                <span><?php echo JText::_('K2_COMMENTS'); ?></span>
            </a>
        </div>
    </div>
    <?php if ($user->gid>23): ?>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=extrafields'); ?>">
                <i class="dashicon extra-fields"></i>
                <span><?php echo JText::_('K2_EXTRA_FIELDS'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=extrafieldsgroups'); ?>">
                <i class="dashicon extra-field-groups"></i>
                <span><?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?></span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div class="icon-wrapper">
        <div class="icon">
            <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=media'); ?>">
                <i class="dashicon mediamanager"></i>
                <span><?php echo JText::_('K2_MEDIA_MANAGER'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a data-k2-modal="iframe" target="_blank" href="https://getk2.org/documentation/">
                <i class="dashicon documentation"></i>
                <span><?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?></span>
            </a>
        </div>
    </div>
    <?php if ($user->gid>23): ?>
    <div class="icon-wrapper">
        <div class="icon">
            <a data-k2-modal="iframe" target="_blank" href="https://getk2.org/extend/">
                <i class="dashicon extend"></i>
                <span><?php echo JText::_('K2_EXTEND'); ?></span>
            </a>
        </div>
    </div>
    <div class="icon-wrapper">
        <div class="icon">
            <a data-k2-modal="iframe" target="_blank" href="https://www.joomlaworks.net/forum/k2">
                <i class="dashicon help"></i>
                <span><?php echo JText::_('K2_COMMUNITY'); ?></span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div style="clear: both;"></div>
</div>
