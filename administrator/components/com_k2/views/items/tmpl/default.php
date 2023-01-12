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

$app = JFactory::getApplication();
$context = JRequest::getCmd('context');

?>

<?php if($app->isSite() || $context == "modalselector"): ?>
<!-- Modal View -->
<div id="k2ModalContainer">
    <div id="k2ModalHeader">
        <h2 id="k2ModalLogo"><span><?php echo JText::_('K2_ITEMS'); ?></span></h2>
        <table id="k2ModalToolbar" cellpadding="2" cellspacing="4">
            <tr>
                <td id="toolbar-close" class="button">
                    <a href="#" id="k2CloseMfp">
                        <i class="fa fa-times-circle" aria-hidden="true"></i> <?php echo JText::_('K2_CLOSE'); ?>
                    </a>
                </td>
            </tr>
        </table>
    </div>
<?php endif; ?>

    <form action="index.php" method="post" name="adminForm" id="adminForm">
        <table class="k2AdminTableFilters table">
            <tr>
                <td class="k2AdminTableFiltersSearch">
                    <label class="k2ui-not-visible"><?php echo JText::_('K2_FILTER'); ?></label>
                    <div class="btn-wrapper input-append">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="text_area" title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>" placeholder="<?php echo JText::_('K2_FILTER'); ?>" />
                        <button id="k2SubmitButton" class="btn"><?php echo JText::_('K2_GO'); ?></button>
                        <button id="k2ResetButton" class="btn"><?php echo JText::_('K2_RESET'); ?></button>
                    </div>
                </td>
                <td class="k2AdminTableFiltersSelects">
                    <?php echo $this->lists['trash']; ?>
                    <?php echo $this->lists['featured']; ?>
                    <?php echo $this->lists['state']; ?>
                    <?php echo $this->lists['categories']; ?>
                    <?php if (isset($this->lists['tag'])): ?>
                    <?php echo $this->lists['tag']; ?>
                    <?php endif; ?>
                    <?php echo $this->lists['authors']; ?>
                    <?php if (isset($this->lists['language'])): ?>
                    <?php echo $this->lists['language']; ?>
                    <?php endif; ?>

                    <?php foreach ($this->filters as $filter): ?>
                    <?php echo $filter; ?>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <div class="k2AdminTableData">
            <table class="adminlist table table-striped<?php if (isset($this->rows) && count($this->rows) == 0): ?> nocontent<?php endif; ?>" id="k2ItemsList">
                <thead>
                    <tr>
                        <th width="1%" class="k2ui-center k2ui-hide-on-mobile">
                            <?php if (K2_JVERSION == '30'): ?>
                            <?php if ($this->filter_featured == '1'): ?>
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.featured_ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_FEATURED_ORDER'); ?>
                            <?php else: ?>
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
                            <?php endif; ?>
                            <?php else: ?>
                            #
                            <?php endif; ?>
                        </th>
                        <th class="k2ui-center<?php echo ($context == "modalselector") ? ' k2ui-not-visible' : ''; ?>">
                            <input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" />
                        </th>
                        <th class="title">
                            <?php echo JHTML::_('grid.sort', 'K2_TITLE', 'i.title', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center">
                            <?php echo JHTML::_('grid.sort', 'K2_FEATURED', 'i.featured', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center">
                            <?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'i.published', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php if (K2_JVERSION != '30'): ?>
                        <th class="k2ui-order">
                            <?php if ($this->filter_featured == '1'): ?>
                            <?php echo JHTML::_('grid.sort', 'K2_FEATURED_ORDER', 'i.featured_ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                            <?php if ($this->ordering) echo JHTML::_('grid.order', $this->rows, 'filesave.png', 'savefeaturedorder'); ?>
                            <?php else: ?>
                            <?php echo JHTML::_('grid.sort', 'K2_ORDER', 'i.ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                            <?php if ($this->ordering) echo JHTML::_('grid.order', $this->rows); ?>
                            <?php endif; ?>
                        </th>
                        <?php endif; ?>
                        <th class="k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_CATEGORY', 'category', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_AUTHOR', 'author', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_LAST_MODIFIED_BY', 'moderator', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_ACCESS_LEVEL', 'i.access', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_CREATED', 'i.created', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_MODIFIED', 'i.modified', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_HITS', 'i.hits', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JText::_('K2_IMAGE'); ?>
                        </th>
                        <?php if (isset($this->lists['language'])): ?>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_LANGUAGE', 'i.language', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php endif; ?>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_ID', 'i.id', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php foreach ($this->columns as $column): ?>
                        <th>
                            <?php echo JHTML::_('grid.sort', $column->label, $column->property, @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <?php
                    $tfootColspan = 14 + count($this->columns);
                    if (K2_JVERSION != '30') {
                        $tfootColspan++;
                    }
                    if (isset($this->lists['language'])) {
                        $tfootColspan++;
                    }
                ?>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $tfootColspan; ?>">
                            <?php if (K2_JVERSION == '30'): ?>
                            <div class="k2LimitBox">
                                <?php echo $this->page->getLimitBox(); ?>
                            </div>
                            <?php endif; ?>
                            <?php echo $this->page->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (isset($this->rows) && count($this->rows) > 0): ?>
                    <?php foreach ($this->rows as $key => $row): ?>
                    <tr class="row<?php echo $key%2; ?>"<?php echo ($this->filter_featured != '1') ? ' sortable-group-id="'.$row->catid.'"' : ''; ?>>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php if (K2_JVERSION == '30'): ?>
                            <?php if ($row->canChange): ?>
                            <span class="sortable-handler<?php echo ($this->ordering) ? '' : ' inactive tip-top'; ?>" title="<?php echo ($this->ordering) ? '' : JText::_('JORDERINGDISABLED'); ?>" rel="tooltip"><i class="icon-menu"></i></span>
                            <input type="text" style="display:none;" name="order[]" size="5" value="<?php echo ($this->filter_featured != '1') ? $row->ordering : $row->featured_ordering; ?>" class="width-20 text-area-order" />
                            <?php else: ?>
                            <span class="sortable-handler inactive"><i class="icon-menu"></i></span>
                            <?php endif; ?>
                            <?php else: ?>
                            <?php echo $key+1; ?>
                            <?php endif; ?>
                        </td>
                        <td class="k2ui-center<?php echo ($context == "modalselector") ? ' k2ui-not-visible' : ''; ?>"><?php echo @JHTML::_('grid.checkedout', $row, $key); ?></td>
                        <td>
                            <div class="k2ui-list-title">
                                <?php if ($context == "modalselector"): ?>
                                <?php
                                if (JRequest::getCmd('output') == 'list') {
                                    $onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->title).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\', \''.JRequest::getCmd('output').'\'); return false;';
                                } else {
                                    $onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->title).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\'); return false;';
                                }
                                ?>
                                <a class="k2ListItemDisabled" title="<?php echo JText::_('K2_CLICK_TO_ADD_THIS_ENTRY'); ?>" href="#" onclick="<?php echo $onClick; ?>">
                                    <?php echo $row->title; ?>
                                </a>
                                <?php else: ?>
                                <?php if ($this->table->isCheckedOut($this->user->get('id'), $row->checked_out)): ?>
                                <i class="fa fa-lock" aria-hidden="true"></i> <?php echo $row->title; ?>
                                <?php else: ?>
                                <?php if (!$this->filter_trash): ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_k2&view=item&cid='.$row->id); ?>"><?php echo $row->title; ?></a>
                                <?php else: ?>
                                <?php echo $row->title; ?>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="k2ui-show-on-mobile">
                                <div class="k2ui-list-mobile-attribute">
                                    <?php echo JText::_('K2_CATEGORY'); ?>:
                                    <?php if ($context == "modalselector"): ?>
                                    <?php echo $row->category; ?>
                                    <?php else: ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_k2&view=category&cid='.$row->catid); ?>"><?php echo $row->category; ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="k2ui-list-mobile-attribute">
                                    <?php echo JText::_('K2_AUTHOR'); ?>:
                                    <?php if ($this->user->gid > 23 && $context != "modalselector"): ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->created_by); ?>"><?php echo $row->author; ?></a>
                                    <?php else: ?>
                                    <?php echo $row->author; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($row->moderator)): ?>
                                    | <?php echo JText::_('K2_LAST_MODIFIED_BY'); ?>:
                                    <?php if ($this->user->gid > 23 && $context != "modalselector"): ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->modified_by); ?>"><?php echo $row->moderator; ?></a>
                                    <?php else: ?>
                                    <?php echo $row->moderator; ?>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="k2ui-center"><?php echo $row->featuredStatus; ?></td>
                        <td class="k2ui-center"><?php echo $row->status; ?></td>
                        <?php if (K2_JVERSION != '30'): ?>
                        <td class="k2ui-order">
                            <?php if ($this->filter_featured == '1'): ?>
                            <span><?php echo $this->page->orderUpIcon($key, true, 'featuredorderup', 'K2_MOVE_UP', $this->ordering); ?></span>
                            <span><?php echo $this->page->orderDownIcon($key, count($this->rows), true, 'featuredorderdown', 'K2_MOVE_DOWN', $this->ordering); ?></span>
                            <input type="text" name="order[]" size="5" value="<?php echo $row->featured_ordering; ?>"<?php echo ($this->ordering) ? '' : ' disabled="disabled"'; ?> class="text_area" />
                            <?php else: ?>
                            <span><?php echo $this->page->orderUpIcon($key, ($row->catid == @$this->rows[$key-1]->catid), 'orderup', 'K2_MOVE_UP', $this->ordering); ?></span>
                            <span><?php echo $this->page->orderDownIcon($key, count($this->rows), ($row->catid == @$this->rows[$key+1]->catid), 'orderdown', 'K2_MOVE_DOWN', $this->ordering); ?></span>
                            <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"<?php echo ($this->ordering) ? '' : ' disabled="disabled"'; ?> class="text_area" />
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="k2ui-hide-on-mobile">
                            <?php if ($context == "modalselector"): ?>
                            <?php echo $row->category; ?>
                            <?php else: ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_k2&view=category&cid='.$row->catid); ?>"><?php echo $row->category; ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="k2ui-hide-on-mobile">
                            <?php if ($this->user->gid > 23 && $context != "modalselector"): ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->created_by); ?>"><?php echo $row->author; ?></a>
                            <?php else: ?>
                            <?php echo $row->author; ?>
                            <?php endif; ?>
                        </td>
                        <td class="k2ui-hide-on-mobile">
                            <?php if ($this->user->gid > 23 && $context != "modalselector"): ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->modified_by); ?>"><?php echo $row->moderator; ?></a>
                            <?php else: ?>
                            <?php echo $row->moderator; ?>
                            <?php endif; ?>
                        </td>
                        <td class="k2ui-center k2ui-hide-on-mobile"><?php echo ($this->filter_trash || K2_JVERSION != '15') ? $row->groupname : JHTML::_('grid.access', $row, $key); ?></td>
                        <td class="k2Date k2ui-hide-on-mobile"><?php echo JHTML::_('date', $row->created, $this->dateFormat); ?></td>
                        <td class="k2Date k2ui-hide-on-mobile"><?php echo ($row->modified == $this->nullDate) ? JText::_('K2_NEVER') : JHTML::_('date', $row->modified, $this->dateFormat); ?></td>
                        <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->hits ?></td>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$row->id).'_XL.jpg')): ?>
                            <a href="<?php echo JURI::root(true).'/media/k2/items/cache/'.md5("Image".$row->id).'_XL.jpg'; ?>" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" data-fancybox="gallery" data-caption="&lt;b&gt;<?php echo $row->title; ?>&lt;/b&gt; - <?php echo JText::_('K2_PUBLISHED_IN'); ?> &lt;b&gt;<?php echo $row->category; ?>&lt;/b&gt; <?php echo JText::_('K2_BY'); ?> &lt;b&gt;<?php echo $row->author; ?>&lt;/b&gt;">
                                <i class="fa fa-picture-o" aria-hidden="true" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($this->lists['language'])): ?>
                        <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->language; ?></td>
                        <?php endif; ?>
                        <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->id; ?></td>
                        <?php foreach ($this->columns as $column): ?>
                        <td<?php echo ($column->class) ? ' class="'.$column->class.'"' : ''; ?>>
                            <?php $property = $column->property; echo $row->$property; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $tfootColspan; ?>" class="k2ui-nocontent">
                            <div class="k2ui-nocontent-message">
                                <i class="fa fa-list" aria-hidden="true"></i><?php echo JText::_('K2_BE_NO_ITEMS_FOUND'); ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Batch Operations Modal -->
        <div id="k2BatchOperations" class="k2ui-modal">
            <div class="k2ui-modal-content-wrapper">
                <div class="k2ui-modal-content">
                    <div class="k2ui-modal-header">
                        <h3 class="k2ui-float-left"><?php echo JText::_('K2_BATCH_OPERATIONS'); ?></h3>
                        <div class="k2ui-float-right">
                            <span id="k2BatchOperationsCounter">0</span>
                            <?php echo JText::_('K2_SELECTED_ITEMS'); ?>
                        </div>
                        <div class="clr"></div>
                    </div>
                    <div class="k2ui-batch-actions">
                        <div class="k2ui-row">
                            <input type="radio" name="batchMode" value="apply" id="assign" checked="checked" />
                            <label for="assign"><?php echo JText::_('K2_ASSIGN'); ?></label>

                            <input type="radio" name="batchMode" value="clone" id="clone" />
                            <label for="clone"><?php echo JText::_('K2_CREATE_DUPLICATE'); ?></label>
                        </div>
                        <div class="clr"></div>
                    </div>
                    <div class="k2ui-batch-options">
                        <div class="k2ui-row">
                            <label><i class="fa fa-folder-open"></i> <?php echo JText::_('K2_CATEGORY'); ?></label>
                            <?php echo $this->lists['batchCategories']; ?>
                        </div>
                        <div class="k2ui-row">
                            <label><i class="fa fa-unlock-alt"></i> <?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
                            <?php echo $this->lists['batchAccess']; ?>
                        </div>
                        <div class="k2ui-row">
                            <label><i class="fa fa-user"></i> <?php echo JText::_('K2_AUTHOR'); ?></label>
                            <?php echo $this->lists['batchAuthor']; ?>
                        </div>
                        <div class="k2ui-row">
                            <?php if (isset($this->lists['language'])): ?>
                            <label><i class="fa fa-globe"></i> <?php echo JText::_('K2_LANGUAGE'); ?></label>
                            <?php echo $this->lists['batchLanguage']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="clr"></div>
                    </div>
                    <div class="k2ui-modal-footer">
                        <?php if (K2_JVERSION == '15'): ?>
                        <button class="k2ui-btn k2ui-btn-save" onclick="javascript:submitbutton('saveBatch')"><?php echo JText::_('K2_APPLY'); ?></button>
                        <?php else: ?>
                        <button class="k2ui-btn k2ui-btn-save" onclick="Joomla.submitbutton('saveBatch')" class="btn btn-small"><?php echo JText::_('K2_APPLY'); ?></button>
                        <?php endif; ?>
                        <button class="k2ui-btn k2ui-btn-close" onclick="$K2('.k2ui-modal-open').removeClass('k2ui-modal-open'); return false;"><?php echo JText::_('K2_CANCEL'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="option" value="com_k2" />
        <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
        <input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php if ($context == "modalselector"): ?>
        <input type="hidden" name="context" value="modalselector" />
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="fid" value="<?php echo JRequest::getCmd('fid'); ?>" />
        <input type="hidden" name="fname" value="<?php echo JRequest::getVar('fname'); ?>" />
        <input type="hidden" name="output" value="<?php echo JRequest::getCmd('output'); ?>" />
        <?php endif; ?>
        <?php echo JHTML::_('form.token'); ?>
    </form>

<?php if($app->isSite() || $context == "modalselector"): ?>
</div>
<?php endif; ?>
