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

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2UsersBlock<?php if ($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach($users as $key=>$user): ?>
        <li class="<?php echo ($key%2) ? "odd" : "even"; if (count($users)==$key+1) echo ' lastItem'; ?>">
            <?php if ($userAvatar && !empty($user->avatar)): ?>
            <a class="k2Avatar ubUserAvatar" rel="author" href="<?php echo $user->link; ?>" title="<?php echo K2HelperUtilities::cleanHtml($user->name); ?>">
                <img src="<?php echo $user->avatar; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($user->name); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
            </a>
            <?php endif; ?>

            <?php if ($userName): ?>
            <a class="ubUserName" rel="author" href="<?php echo $user->link; ?>" title="<?php echo K2HelperUtilities::cleanHtml($user->name); ?>">
                <?php echo $user->name; ?>
            </a>
            <?php endif; ?>

            <?php if ($userDescription && $user->description): ?>
            <div class="ubUserDescription">
                <?php if ($userDescriptionWordLimit): ?>
                <?php echo K2HelperUtilities::wordLimit($user->description, $userDescriptionWordLimit) ?>
                <?php else: ?>
                <?php echo $user->description; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($userFeed || ($userURL && $user->url) || $userEmail): ?>
            <div class="ubUserAdditionalInfo">
                <?php if ($userFeed): ?>
                <!-- RSS feed icon -->
                <a class="ubUserFeedIcon" href="<?php echo $user->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_USERS_RSS_FEED'); ?>">
                    <span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_USERS_RSS_FEED'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($userURL && $user->url): ?>
                <a class="ubUserURL" rel="me" href="<?php echo $user->url; ?>" title="<?php echo JText::_('K2_WEBSITE'); ?>" target="_blank">
                    <span><?php echo JText::_('K2_WEBSITE'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($userEmail): ?>
                <span class="ubUserEmail" title="<?php echo JText::_('K2_EMAIL'); ?>">
                    <?php echo JHTML::_('Email.cloak', $user->email); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($userItemCount && count($user->items)): ?>
            <h3><?php echo JText::_('K2_RECENT_ITEMS'); ?></h3>
            <ul class="ubUserItems">
                <?php foreach ($user->items as $item): ?>
                <li>
                    <a href="<?php echo $item->link; ?>" title="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>">
                        <?php echo $item->title; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="clr"></div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
