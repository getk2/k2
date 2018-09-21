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

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2UserBlock<?php if ($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <?php if ($userGreetingText): ?>
    <p class="ubGreeting"><?php echo $userGreetingText; ?></p>
    <?php endif; ?>

    <div class="k2UserBlockDetails">
        <?php if ($params->get('userAvatar')): ?>
        <a class="k2Avatar ubAvatar" href="<?php echo JRoute::_(K2HelperRoute::getUserRoute($user->id)); ?>" title="<?php echo JText::_('K2_MY_PAGE'); ?>">
            <img src="<?php echo K2HelperUtilities::getAvatar($user->id, $user->email); ?>" alt="<?php echo K2HelperUtilities::cleanHtml($user->name); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
        </a>
        <?php endif; ?>
        <span class="ubName"><?php echo $user->name; ?></span>
        <span class="ubCommentsCount"><?php echo JText::_('K2_YOU_HAVE'); ?> <b><?php echo $user->numOfComments; ?></b> <?php if ($user->numOfComments==1) echo JText::_('K2_PUBLISHED_COMMENT'); else echo JText::_('K2_PUBLISHED_COMMENTS'); ?></span>
        <div class="clr"></div>
    </div>

    <ul class="k2UserBlockActions">
        <?php if (isset($addItemLink)): ?>
        <li>
            <a data-k2-modal="edit" href="<?php echo $addItemLink; ?>"><?php echo JText::_('K2_ADD_NEW_ITEM'); ?></a>
        </li>
        <?php endif; ?>
        <li>
            <a href="<?php echo $viewProfileLink; ?>"><?php echo JText::_('K2_MY_PAGE'); ?></a>
        </li>
        <li>
            <a href="<?php echo $editProfileLink; ?>"><?php echo JText::_('K2_MY_ACCOUNT'); ?></a>
        </li>
        <?php if ($K2CommentsEnabled): ?>
        <li>
            <a data-k2-modal="iframe" href="<?php echo $editCommentsLink; ?>"><?php echo JText::_('K2_MODERATE_COMMENTS_TO_MY_PUBLISHED_ITEMS'); ?></a>
        </li>
        <?php endif; ?>
    </ul>

    <ul class="k2UserBlockRenderedMenu">
        <?php $level = 1; foreach($menu as $key => $link): $level++; ?>
        <li class="linkItemId<?php echo $link->id; ?>">
            <?php if ($link->type=='url' && $link->browserNav==0): ?>
            <a href="<?php echo $link->route; ?>"><?php echo $link->name; ?></a>
            <?php elseif (strpos($link->link,'option=com_k2&view=item&layout=itemform') || $link->browserNav==2): ?>
            <a data-k2-modal="edit" href="<?php echo $link->route; ?>"><?php echo $link->name; ?></a>
            <?php else: ?>
            <a href="<?php echo $link->route; ?>"<?php if ($link->browserNav==1) echo ' target="_blank"'; ?>><?php echo $link->name; ?></a>
            <?php endif; ?>

            <?php if (isset($menu[$key+1]) && $menu[$key]->level < $menu[$key+1]->level): ?>
            <ul>
            <?php endif; ?>

            <?php if (isset($menu[$key+1]) && $menu[$key]->level > $menu[$key+1]->level): ?>
            <?php echo str_repeat('</li></ul>', $menu[$key]->level - $menu[$key+1]->level); ?>
            <?php endif; ?>

            <?php if (isset($menu[$key+1]) && $menu[$key]->level == $menu[$key+1]->level): ?>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <form action="<?php echo JURI::root(true); ?>/index.php" method="post">
        <input type="submit" name="Submit" class="button ubLogout" value="<?php echo JText::_('K2_LOGOUT'); ?>" />
        <input type="hidden" name="option" value="<?php echo $option; ?>" />
        <input type="hidden" name="task" value="<?php echo $task; ?>" />
        <input type="hidden" name="return" value="<?php echo $return; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </form>
</div>
