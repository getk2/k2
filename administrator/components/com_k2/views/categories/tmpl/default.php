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
        <h2 id="k2ModalLogo"><span><?php echo JText::_('K2_CATEGORIES'); ?></span></h2>
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
                    <?php echo $this->lists['state']; ?>
                    <?php echo $this->lists['categories']; ?>
                    <?php if (isset($this->lists['language'])): ?>
                    <?php echo $this->lists['language']; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <div class="k2AdminTableData">
            <table class="adminlist table table-striped<?php if (isset($this->rows) && count($this->rows) == 0): ?> nocontent<?php endif; ?>" id="k2CategoriesList">
                <thead>
                    <tr>
                        <?php if (K2_JVERSION == '30'): ?>
                        <th width="1%" class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'c.ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
                        </th>
                        <?php else: ?>
                        <th>#</th>
                        <?php endif; ?>
                        <th<?php echo ($context == "modalselector") ? ' class="k2ui-not-visible"' : ' class="k2ui-center"'; ?>>
                            <input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" />
                        </th>
                        <th>
                            <?php echo JHTML::_('grid.sort', 'K2_TITLE', 'c.name', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php if (K2_JVERSION != '30'): ?>
                        <th class="k2ui-right">
                            <?php echo JHTML::_('grid.sort', 'K2_ORDER', 'c.ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?> <?php echo $this->ordering ? JHTML::_('grid.order', $this->rows, 'filesave.png') : ''; ?>
                        </th>
                        <?php endif ;?>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JText::_('K2_PARAMETER_INHERITANCE'); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_ASSOCIATED_EXTRA_FIELD_GROUPS', 'extra_fields_group', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JText::_('K2_TEMPLATE'); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_ACCESS_LEVEL', 'c.access', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center">
                            <?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'c.published', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JText::_('K2_IMAGE'); ?>
                        </th>
                        <?php if (isset($this->lists['language'])): ?>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_LANGUAGE', 'c.language', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                        <?php endif; ?>
                        <th class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo JHTML::_('grid.sort', 'K2_ID', 'c.id', @$this->lists['order_Dir'], @$this->lists['order']); ?>
                        </th>
                    </tr>
                </thead>
                <?php
                    $tfootColspan = 10;
                    if (K2_JVERSION != '30') {
                        $tfootColspan++;
                    }
                    if (isset($this->lists['language'])) {
                        $tfootColspan++;
                    }
                    if ($context == "modalselector") {
                        $tfootColspan--;
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
                    <?php foreach ($this->rows as $key => $row) :   ?>
                    <tr class="row<?php echo($key%2); ?>" sortable-group-id="<?php echo $row->parent; ?>">
                        <?php if (K2_JVERSION == '30'): ?>
                        <td class="k2ui-order k2ui-center k2ui-hide-on-mobile">
                            <?php if ($row->canChange): ?>
                            <span class="sortable-handler<?php echo ($this->ordering) ? '' : ' inactive tip-top' ;?>" title="<?php echo ($this->ordering) ? '' : JText::_('JORDERINGDISABLED'); ?>" rel="tooltip"><i class="icon-menu"></i></span>
                            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="width-20 text-area-order" />
                            <?php else: ?>
                            <span class="sortable-handler inactive"><i class="icon-menu"></i></span>
                            <?php endif; ?>
                        </td>
                        <?php else: ?>
                        <td><?php echo $key+1; ?></td>
                        <?php endif; ?>
                        <td class="k2ui-center<?php echo ($context == "modalselector") ? ' k2ui-not-visible' : ''; ?>">
                            <?php if (!$this->filter_trash || $row->trash) {
                                $row->checked_out = 0;
                                echo @JHTML::_('grid.checkedout', $row, $key);
                            } ?>
                        </td>
                        <td>
                            <div class="k2ui-list-title">
                                <?php if ($this->filter_trash): ?>
                                <?php if ($row->trash): ?>
                                <strong><?php echo $row->treename; ?> (<?php echo $row->numOfTrashedItems; ?>)</strong>
                                <?php else: ?>
                                <?php echo $row->treename; ?> (<?php echo $row->numOfItems.' '.JText::_('K2_ACTIVE'); ?> / <?php echo $row->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)
                                <?php endif; ?>
                                <?php else: ?>
                                <?php if ($context == "modalselector"): ?>
                                <?php
                                if (JRequest::getCmd('output') == 'list') {
                                    $onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->treename).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\', \''.JRequest::getCmd('output').'\'); return false;';
                                } else {
                                    $onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->treename).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\'); return false;';
                                }
                                ?>
                                <a class="k2ListItemDisabled" title="<?php echo JText::_('K2_CLICK_TO_ADD_THIS_ENTRY'); ?>" href="#" onclick="<?php echo $onClick; ?>">
                                    <?php echo $row->treename; ?>
                                    <?php if ($this->params->get('showItemsCounterAdmin')): ?>
                                    <span class="small">(<?php echo $row->numOfItems.' '.JText::_('K2_ACTIVE'); ?> / <?php echo $row->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)</span>
                                    <?php endif; ?>
                                </a>
                                <?php else: ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_k2&view=category&cid='.$row->id); ?>">
                                    <?php echo $row->treename; ?>
                                    <?php if ($this->params->get('showItemsCounterAdmin')): ?>
                                    <span class="small">(<?php echo $row->numOfItems.' '.JText::_('K2_ACTIVE'); ?> / <?php echo $row->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)</span>
                                    <?php endif; ?>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php if (K2_JVERSION != '30'): ?>
                        <td class="k2ui-order">
                            <span><?php echo $this->page->orderUpIcon($key, $row->parent == 0 || $row->parent == @$this->rows[$key-1]->parent, 'orderup', 'K2_MOVE_UP', $this->ordering); ?></span>
                            <span><?php echo $this->page->orderDownIcon($key, count($this->rows), $row->parent == 0 || $row->parent == @$this->rows[$key+1]->parent, 'orderdown', 'K2_MOVE_DOWN', $this->ordering); ?></span>
                            <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"<?php echo ($this->ordering) ? '' : ' disabled="disabled"'; ?> class="text_area" />
                        </td>
                        <?php endif; ?>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo $row->inheritFrom; ?>
                        </td>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo $row->extra_fields_group; ?>
                        </td>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo $row->template; ?>
                        </td>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo ($this->filter_trash || K2_JVERSION != '15') ? $row->groupname : JHTML::_('grid.access', $row, $key); ?>
                        </td>
                        <td class="k2ui-center">
                            <?php echo $row->status; ?>
                        </td>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php if ($row->image): ?>
                            <a href="<?php echo JURI::root(true).'/media/k2/categories/'.$row->image; ?>" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" data-fancybox="gallery" data-caption="<?php echo $row->title; ?>">
                                <i class="fa fa-picture-o" aria-hidden="true" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($this->lists['language'])): ?>
                        <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->language; ?></td>
                        <?php endif; ?>
                        <td class="k2ui-center k2ui-hide-on-mobile">
                            <?php echo $row->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $tfootColspan; ?>" class="k2ui-nocontent">
                            <div class="k2ui-nocontent-message">
                                <i class="fa fa-list" aria-hidden="true"></i><?php echo JText::_('K2_BE_NO_CATEGORIES_FOUND'); ?>
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
                            <label><i class="fa fa-folder-open"></i> <?php echo JText::_('K2_PARENT_CATEGORY'); ?></label>
                            <?php echo $this->lists['batchCategories']; ?>
                        </div>
                        <div class="k2ui-row">
                            <label><i class="fa fa-unlock-alt"></i> <?php echo JText::_('K2_ACCESS_LEVEL'); ?></label>
                            <?php echo $this->lists['batchAccess']; ?>
                        </div>
                        <div class="k2ui-row">
                            <label><i class="fa fa-th-list"></i> <?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?></label>
                            <?php echo $this->lists['batchExtraFieldsGroups']; ?>
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
        <input type="hidden" name="task" value="" />
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
