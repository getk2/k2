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

<div class="clr"></div>

<?php if($modLogo): ?>
<div id="k2QuickIconsTitle">
	<a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=0'); ?>" title="<?php echo JText::_('K2_DASHBOARD'); ?>">
		<span>K2</span>
	</a>
</div>
<?php endif; ?>

<div id="k2QuickIcons<?php if(K2_JVERSION=='15') echo '15'; ?>"<?php if(!$modLogo): ?> class="k2NoLogo"<?php endif; ?>>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=item'); ?>">
		    <img alt="<?php echo JText::_('K2_ADD_NEW_ITEM'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/item-new.png" />
		    <span><?php echo JText::_('K2_ADD_NEW_ITEM'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=0'); ?>">
		    <img alt="<?php echo JText::_('K2_ITEMS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/items.png" />
		    <span><?php echo JText::_('K2_ITEMS'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=1&amp;filter_trash=0'); ?>">
		    <img alt="<?php echo JText::_('K2_FEATURED_ITEMS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/items-featured.png" />
		    <span><?php echo JText::_('K2_FEATURED_ITEMS'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=items&amp;filter_featured=-1&amp;filter_trash=1'); ?>">
		    <img alt="<?php echo JText::_('K2_TRASHED_ITEMS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/items-trashed.png" />
		    <span><?php echo JText::_('K2_TRASHED_ITEMS'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=categories&amp;filter_trash=0'); ?>">
		    <img alt="<?php echo JText::_('K2_CATEGORIES'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/categories.png" />
		    <span><?php echo JText::_('K2_CATEGORIES'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=categories&amp;filter_trash=1'); ?>">
		    <img alt="<?php echo JText::_('K2_TRASHED_CATEGORIES'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/categories-trashed.png" />
		    <span><?php echo JText::_('K2_TRASHED_CATEGORIES'); ?></span>
	    </a>
    </div>
  </div>
	<?php if(!$componentParams->get('lockTags') || $user->gid>23): ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=tags'); ?>">
		    <img alt="<?php echo JText::_('K2_TAGS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/tags.png" />
		    <span><?php echo JText::_('K2_TAGS'); ?></span>
	    </a>
    </div>
  </div>
  <?php endif; ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=comments'); ?>">
		    <img alt="<?php echo JText::_('K2_COMMENTS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/comments.png" />
		    <span><?php echo JText::_('K2_COMMENTS'); ?></span>
	    </a>
    </div>
  </div>
  <?php if ($user->gid>23): ?>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=extrafields'); ?>">
		    <img alt="<?php echo JText::_('K2_EXTRA_FIELDS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/extra-fields.png" />
		    <span><?php echo JText::_('K2_EXTRA_FIELDS'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=extrafieldsgroups'); ?>">
		    <img alt="<?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/extra-field-groups.png" />
		    <span><?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?></span>
	    </a>
    </div>
  </div>
  <?php endif; ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2&amp;view=media'); ?>">
		    <img alt="<?php echo JText::_('K2_MEDIA_MANAGER'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/mediamanager.png" />
		    <span><?php echo JText::_('K2_MEDIA_MANAGER'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
    	<a id="k2OnlineImageEditor" target="_blank" href="<?php echo $onlineImageEditorLink; ?>">
		    <img alt="<?php echo JText::_('K2_ONLINE_IMAGE_EDITOR'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/image-editing.png" />
		    <span><?php echo JText::_('K2_ONLINE_IMAGE_EDITOR'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/documentation/">
    		<img alt="<?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/documentation.png" />
    		<span><?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?></span>
    	</a>
    </div>
  </div>
  <?php if ($user->gid>23): ?>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/extend/">
    		<img alt="<?php echo JText::_('K2_EXTEND'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/extend.png" />
    		<span><?php echo JText::_('K2_EXTEND'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/community/">
    		<img alt="<?php echo JText::_('K2_COMMUNITY'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/help.png" />
    		<span><?php echo JText::_('K2_COMMUNITY'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" href="http://joomlareader.com/" title="<?php echo JText::_('K2_JOOMLA_NEWS_FROM_MORE_THAN_200_SOURCES_WORLDWIDE'); ?>">
		    <img alt="<?php echo JText::_('K2_JOOMLA_NEWS_FROM_MORE_THAN_200_SOURCES_WORLDWIDE'); ?>" src="<?php echo JURI::root(true); ?>/media/k2/assets/images/dashboard/joomlareader.png" />
		    <span><?php echo JText::_('K2_JOOMLAREADERCOM'); ?></span>
	    </a>
    </div>
  </div>
  <div style="clear: both;"></div>
  <?php endif; ?>
</div>
