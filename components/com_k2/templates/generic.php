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

<!-- Start K2 Generic (date/search) Layout -->
<div id="k2Container" class="genericView">
    <?php if (JRequest::getCmd('task') == 'search'): ?>
    <form action="<?php echo $this->form->action; ?>" method="get" autocomplete="off" class="genericSearchForm">
        <input type="text" value="<?php echo $this->form->input; ?>" name="searchword" class="k2-input" placeholder="<?php echo JText::_('K2_SEARCH'); ?>" />
        <input type="submit" value="<?php echo JText::_('K2_SEARCH'); ?>" class="k2-submit" />
        <?php echo $this->form->attributes; /* outputs hidden fields for form processing - do not delete */ ?>
    </form>
    <?php endif; ?>

    <?php if ($this->params->get('genericTitle', 1)): ?>
    <!-- Title for date & search listings -->
    <h1><?php echo (JRequest::getCmd('task') == 'date') ? JText::_('K2_ITEMS_FILTERED_BY_DATE') : JText::_('K2_SEARCH_RESULTS_FOR'); ?> <?php echo $this->title; ?></h1>
    <?php endif; ?>

    <?php if ($this->params->get('genericFeedIcon',1) && isset($this->items) && count($this->items)): ?>
    <!-- RSS feed icon -->
    <div class="k2FeedIcon">
        <a href="<?php echo $this->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
            <span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
        </a>
        <div class="clr"></div>
    </div>
    <?php endif; ?>

    <?php if (isset($this->items) && count($this->items)): ?>

    <div class="genericItemList">
        <?php foreach($this->items as $item): ?>
        <!-- Start K2 Item Layout -->
        <div class="genericItemView">
            <div class="genericItemHeader">
                <?php if ($this->params->get('genericItemDateCreated')): ?>
                <!-- Date created -->
                <span class="genericItemDateCreated">
                    <?php echo JHTML::_('date', $item->created , JText::_('K2_DATE_FORMAT_LC2')); ?>
                </span>
                <?php endif; ?>

                <?php if ($this->params->get('genericItemTitle')): ?>
                <!-- Item title -->
                <h2 class="genericItemTitle">
                    <?php if ($this->params->get('genericItemTitleLinked')): ?>
                    <a href="<?php echo $item->link; ?>">
                        <?php echo $item->title; ?>
                    </a>
                    <?php else: ?>
                    <?php echo $item->title; ?>
                    <?php endif; ?>
                </h2>
                <?php endif; ?>
            </div>

            <div class="genericItemBody">
                <?php if ($this->params->get('genericItemImage') && !empty($item->imageGeneric)): ?>
                <!-- Item Image -->
                <div class="genericItemImageBlock">
                    <span class="genericItemImage">
                        <a href="<?php echo $item->link; ?>" title="<?php if (!empty($item->image_caption)) echo K2HelperUtilities::cleanHtml($item->image_caption); else echo K2HelperUtilities::cleanHtml($item->title); ?>">
                            <img src="<?php echo $item->imageGeneric; ?>" alt="<?php if (!empty($item->image_caption)) echo K2HelperUtilities::cleanHtml($item->image_caption); else echo K2HelperUtilities::cleanHtml($item->title); ?>" style="width:<?php echo $this->params->get('itemImageGeneric'); ?>px; height:auto;" />
                        </a>
                    </span>
                    <div class="clr"></div>
                </div>
                <?php endif; ?>

                <?php if ($this->params->get('genericItemIntroText')): ?>
                <!-- Item introtext -->
                <div class="genericItemIntroText">
                    <?php echo $item->introtext; ?>
                </div>
                <?php endif; ?>

                <div class="clr"></div>
            </div>

            <div class="clr"></div>

            <?php if ($this->params->get('genericItemExtraFields') && isset($item->extra_fields) && count($item->extra_fields)): ?>
            <!-- Item extra fields -->
            <div class="genericItemExtraFields">
                <h4><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h4>
                <ul>
                    <?php foreach ($item->extra_fields as $key => $extraField): ?>
                    <?php if ($extraField->value != ''): ?>
                    <li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?> alias<?php echo ucfirst($extraField->alias); ?>">
                        <?php if ($extraField->type == 'header'): ?>
                        <h4 class="genericItemExtraFieldsHeader"><?php echo $extraField->name; ?></h4>
                        <?php else: ?>
                        <span class="genericItemExtraFieldsLabel"><?php echo $extraField->name; ?></span>
                        <span class="genericItemExtraFieldsValue"><?php echo $extraField->value; ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="clr"></div>
            </div>
            <?php endif; ?>

            <?php if ($this->params->get('genericItemCategory')): ?>
            <!-- Item category name -->
            <div class="genericItemCategory">
                <span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
                <a href="<?php echo $item->category->link; ?>"><?php echo $item->category->name; ?></a>
            </div>
            <?php endif; ?>

            <?php if ($this->params->get('genericItemReadMore')): ?>
            <!-- Item "read more..." link -->
            <div class="genericItemReadMore">
                <a class="k2ReadMore" href="<?php echo $item->link; ?>">
                    <?php echo JText::_('K2_READ_MORE'); ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="clr"></div>
        </div>
        <!-- End K2 Item Layout -->
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($this->pagination->getPagesLinks()): ?>
    <div class="k2Pagination">
        <div class="k2PaginationLinks">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
        <div class="k2PaginationCounter">
            <?php echo $this->pagination->getPagesCounter(); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>

    <!-- No results found -->
    <div id="genericItemListNothingFound">
        <p><?php echo JText::_('K2_NO_RESULTS_FOUND'); ?></p>
    </div>

    <?php endif; ?>
</div>
<!-- End K2 Generic (date/search) Layout -->
