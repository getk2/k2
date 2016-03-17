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

<!-- Start K2 Latest Layout -->
<div id="k2Container" class="latestView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

	<?php if($this->params->get('show_page_title')): ?>
	<!-- Page title -->
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
	<?php endif; ?>

	<?php foreach($this->blocks as $key=>$block): ?>
	<div class="latestItemsContainer" style="width:<?php echo number_format(100/$this->params->get('latestItemsCols'), 1); ?>%;">

		<?php if($this->source=='categories'): $category=$block; ?>

		<?php if($this->params->get('categoryFeed') || $this->params->get('categoryImage') || $this->params->get('categoryTitle') || $this->params->get('categoryDescription')): ?>
		<!-- Start K2 Category block -->
		<div class="latestItemsCategory">
			<?php if($this->params->get('categoryFeed')): ?>
			<!-- RSS feed icon -->
			<div class="k2FeedIcon">
				<a href="<?php echo $category->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
					<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
				</a>
				<div class="clr"></div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('categoryImage') && !empty($category->image)): ?>
			<div class="latestItemsCategoryImage">
				<img src="<?php echo $category->image; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($category->name); ?>" style="width:<?php echo $this->params->get('catImageWidth'); ?>px;height:auto;" />
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('categoryTitle')): ?>
			<h2><a href="<?php echo $category->link; ?>"><?php echo $category->name; ?></a></h2>
			<?php endif; ?>

			<?php if ($this->params->get('categoryDescription') && isset($category->description)): ?>
			<p><?php echo $category->description; ?></p>
			<?php endif; ?>

			<div class="clr"></div>

			<!-- K2 Plugins: K2CategoryDisplay -->
			<?php echo $category->event->K2CategoryDisplay; ?>
			<div class="clr"></div>
		</div>
		<!-- End K2 Category block -->
		<?php endif; ?>

		<?php else: $user=$block; ?>

		<?php if ($this->params->get('userFeed') || $this->params->get('userImage') || $this->params->get('userName') || $this->params->get('userDescription') || $this->params->get('userURL') || $this->params->get('userEmail')): ?>
		<!-- Start K2 User block -->
		<div class="latestItemsUser">

			<?php if($this->params->get('userFeed')): ?>
			<!-- RSS feed icon -->
			<div class="k2FeedIcon">
				<a href="<?php echo $user->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
					<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
				</a>
				<div class="clr"></div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('userImage') && !empty($user->avatar)): ?>
			<img src="<?php echo $user->avatar; ?>" alt="<?php echo $user->name; ?>" style="width:<?php echo $this->params->get('userImageWidth'); ?>px;height:auto;" />
			<?php endif; ?>

			<?php if ($this->params->get('userName')): ?>
			<h2><a rel="author" href="<?php echo $user->link; ?>"><?php echo $user->name; ?></a></h2>
			<?php endif; ?>

			<?php if ($this->params->get('userDescription') && isset($user->profile->description)): ?>
			<p class="latestItemsUserDescription"><?php echo $user->profile->description; ?></p>
			<?php endif; ?>

			<?php if ($this->params->get('userURL') || $this->params->get('userEmail')): ?>
			<p class="latestItemsUserAdditionalInfo">
				<?php if ($this->params->get('userURL') && isset($user->profile->url)): ?>
				<span class="latestItemsUserURL">
					<?php echo JText::_('K2_WEBSITE_URL'); ?>: <a rel="me" href="<?php echo $user->profile->url; ?>" target="_blank"><?php echo $user->profile->url; ?></a>
				</span>
				<?php endif; ?>

				<?php if ($this->params->get('userEmail')): ?>
				<span class="latestItemsUserEmail">
					<?php echo JText::_('K2_EMAIL'); ?>: <?php echo JHTML::_('Email.cloak', $user->email); ?>
				</span>
				<?php endif; ?>
			</p>
			<?php endif; ?>

			<div class="clr"></div>

			<?php echo $user->event->K2UserDisplay; ?>

			<div class="clr"></div>
		</div>
		<!-- End K2 User block -->
		<?php endif; ?>

		<?php endif; ?>

		<!-- Start Items list -->
		<div class="latestItemList">
			<?php if($this->params->get('latestItemsDisplayEffect')=="first"): ?>

			<?php foreach ($block->items as $itemCounter=>$item): K2HelperUtilities::setDefaultImage($item, 'latest', $this->params); ?>
			<?php if($itemCounter==0): ?>
			<?php $this->item=$item; echo $this->loadTemplate('item'); ?>
			<?php else: ?>
			<h2 class="latestItemTitleList">
				<?php if ($item->params->get('latestItemTitleLinked')): ?>
				<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
				<?php else: ?>
				<?php echo $item->title; ?>
				<?php endif; ?>
			</h2>
			<?php endif; ?>
			<?php endforeach; ?>

			<?php else: ?>

			<?php foreach ($block->items as $item): K2HelperUtilities::setDefaultImage($item, 'latest', $this->params); ?>
			<?php $this->item=$item; echo $this->loadTemplate('item'); ?>
			<?php endforeach; ?>

			<?php endif; ?>
		</div>
		<!-- End Item list -->

	</div>

	<?php if(($key+1)%($this->params->get('latestItemsCols'))==0): ?>
	<div class="clr"></div>
	<?php endif; ?>

	<?php endforeach; ?>
	<div class="clr"></div>
</div>

<!-- End K2 Latest Layout -->
