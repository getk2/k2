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
            <td class="k2AdminTableFiltersSelects k2ui-hide-on-mobile">
                <?php echo $this->lists['type']; ?>
                <?php echo $this->lists['group']; ?>
                <?php echo $this->lists['state']; ?>
            </td>
        </tr>
    </table>
    <div class="k2AdminTableData">
        <table class="adminlist table table-striped<?php if(isset($this->rows) && count($this->rows) == 0): ?> nocontent<?php endif; ?>" id="k2ExtraFieldsList">
            <thead>
                <tr>
                    <?php if(K2_JVERSION == '30'): ?>
                    <th width="1%" class="k2ui-center k2ui-hide-on-mobile">
                        <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
                    </th>
                    <?php else: ?>
                    <th width="1%">#</th>
                    <?php endif; ?>
                    <th class="k2ui-center"><input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" /></th>
                    <th class="k2ui-left"><?php echo JHTML::_('grid.sort', 'K2_NAME', 'name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                    <th class="k2ui-center k2ui-hide-on-mobile"><?php echo JHTML::_('grid.sort', 'K2_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                    <?php if(K2_JVERSION != '30'): ?>
                    <th><?php echo JHTML::_('grid.sort', 'K2_ORDER', 'ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?> <?php if ($this->ordering) echo JHTML::_('grid.order',  $this->rows ); ?></th>
                    <?php endif; ?>
                    <th class="k2ui-center k2ui-hide-on-mobile"><?php echo JHTML::_('grid.sort', 'K2_TYPE', 'type', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                    <th class="k2ui-center"><?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                    <th class="k2ui-center k2ui-hide-on-mobile"><?php echo JHTML::_('grid.sort', 'K2_ID', 'exf.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                </tr>
            </thead>
            <?php
                $tfootColspan = 7;
                if(K2_JVERSION != '30') $tfootColspan++;
            ?>
            <tfoot>
                <tr>
                    <td colspan="<?php echo $tfootColspan; ?>">
                        <?php if(K2_JVERSION == '30'): ?>
                        <div class="k2LimitBox">
                            <?php echo $this->page->getLimitBox(); ?>
                        </div>
                        <?php endif; ?>
                        <?php echo $this->page->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if(isset($this->rows) && count($this->rows) > 0): ?>
                <?php foreach ($this->rows as $key=>$row): ?>
                <tr class="row<?php echo ($key%2); ?>" sortable-group-id="<?php echo $row->group; ?>">
                    <?php if(K2_JVERSION == '30'): ?>
                    <td class="k2ui-order k2ui-center k2ui-hide-on-mobile">
                        <span class="sortable-handler<?php echo ($this->ordering) ? '' : ' inactive tip-top' ;?>" title="<?php echo ($this->ordering) ? '' :JText::_('JORDERINGDISABLED'); ?>" rel="tooltip"><i class="icon-menu"></i></span>
                        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="width-20 text-area-order" />
                    </td>
                    <?php else: ?>
                    <td><?php echo $key+1; ?></td>
                    <?php endif; ?>
                    <td class="k2ui-center"><?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key ); ?></td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_k2&view=extrafield&cid='.$row->id); ?>"><?php echo $row->name; ?></a>
                        <span class="k2AliasValue"><?php echo JText::_('K2_ALIAS'); ?>: <?php echo $row->alias; ?></span>
                    </td>
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->groupname; ?></td>
                    <?php if(K2_JVERSION != '30'): ?>
                    <td class="k2ui-order">
                        <span><?php echo $this->page->orderUpIcon($key, ($row->group == @$this->rows[$key-1]->group), 'orderup', 'Move Up', $this->ordering); ?></span>
                        <span><?php echo $this->page->orderDownIcon($key, count($this->rows), ($row->group == @$this->rows[$key+1]->group), 'orderdown', 'Move Down', $this->ordering); ?></span>
                        <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"<?php echo ($this->ordering) ?  '' : ' disabled="disabled"'; ?> class="text_area" />
                    </td>
                    <?php endif; ?>
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo JText::_('K2_EXTRA_FIELD_'.JString::strtoupper($row->type)); ?></td>
                    <td class="k2ui-center"><?php echo $row->status; ?></td>
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->id; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="<?php echo $tfootColspan; ?>" class="k2ui-nocontent">
                        <div class="k2ui-nocontent-message">
                            <i class="fa fa-list" aria-hidden="true"></i><?php echo JText::_('K2_BE_NO_EXTRA_FIELDS_FOUND'); ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHTML::_('form.token'); ?>
</form>
