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

?>

<div class="clr"></div>

<?php if($modLogo): ?>
<div id="k2QuickIconsTitle">
	<a class="dashicon k2logo" href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=0'); ?>" title="<?php echo JText::_('K2_DASHBOARD'); ?>">
		<span>K2</span>
	</a>
</div>
<?php endif; ?>

<div id="k2QuickIcons<?php if(K2_JVERSION=='15') echo '15'; ?>"<?php if(!$modLogo): ?> class="k2NoLogo"<?php endif; ?>>
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
    	<a id="k2OnlineImageEditor" target="_blank" href="<?php echo $onlineImageEditorLink; ?>">
				<i class="dashicon image-editing"></i>
		    <span><?php echo JText::_('K2_ONLINE_IMAGE_EDITOR'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="https://getk2.org/documentation/">
				<i class="dashicon documentation"></i>
    		<span><?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?></span>
    	</a>
    </div>
  </div>
  <?php if ($user->gid>23): ?>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="https://getk2.org/extend/">
				<i class="dashicon extend"></i>
    		<span><?php echo JText::_('K2_EXTEND'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="https://getk2.org/community/">
				<i class="dashicon help"></i>
    		<span><?php echo JText::_('K2_COMMUNITY'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" href="http://joomlareader.com/" title="<?php echo JText::_('K2_JOOMLA_NEWS_FROM_MORE_THAN_200_SOURCES_WORLDWIDE'); ?>">
				<i class="dashicon joomlareader"></i>
		    <span><?php echo JText::_('K2_JOOMLAREADERCOM'); ?></span>
	    </a>
    </div>
  </div>
  <?php endif; ?>
  <div style="clear: both;"></div>
</div>
