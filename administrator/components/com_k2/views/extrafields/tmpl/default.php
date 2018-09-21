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
<form action="index.php" method="post" name="adminForm" id="adminForm">
  <table class="k2AdminTableFilters table">
    <tr>
      <td class="k2AdminTableFiltersSearch">
				<label class="visually-hidden"><?php echo JText::_('K2_FILTER'); ?></label>
				<div class="btn-wrapper input-append">
					<input type="text" name="search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="text_area"	title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>" placeholder="<?php echo JText::_('K2_FILTER'); ?>" />
					<button id="k2SubmitButton" class="btn"><?php echo JText::_('K2_GO'); ?></button>
					<button id="k2ResetButton" class="btn"><?php echo JText::_('K2_RESET'); ?></button>
				</div>
      </td>
      <td class="k2AdminTableFiltersSelects hidden-phone">
		<?php echo $this->lists['type']; ?>
		<?php echo $this->lists['group']; ?>
		<?php echo $this->lists['state']; ?>
      </td>
    </tr>
  </table>
  <table class="adminlist table table-striped" id="k2ExtraFieldsList">
    <thead>
      <tr>
        <?php if(K2_JVERSION == '30'): ?>
        <th width="1%" class="center hidden-phone">
              <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
        </th>
        <?php else: ?>
        <th>#</th>
        <?php endif; ?>
        <th class="k2Center center"><input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" /></th>
        <th><?php echo JHTML::_('grid.sort', 'K2_NAME', 'name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="k2Center center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <?php if(K2_JVERSION != '30'): ?>
        <th><?php echo JHTML::_('grid.sort', 'K2_ORDER', 'ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?> <?php if ($this->ordering) echo JHTML::_('grid.order',  $this->rows ); ?></th>
        <?php endif; ?>
        <th class="k2Center center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_TYPE', 'type', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="k2Center center"><?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="k2Center center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ID', 'exf.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->rows as $key=>$row): ?>
      <tr class="row<?php echo ($key%2); ?>" sortable-group-id="<?php echo $row->group; ?>">
        <?php if(K2_JVERSION == '30'): ?>
        <td class="order hidden-phone">
        <span class="sortable-handler <?php echo ($this->ordering) ? '' : 'inactive tip-top' ;?>" title="<?php echo ($this->ordering) ? '' :JText::_('JORDERINGDISABLED');?>" rel="tooltip"><i class="icon-menu"></i></span>
        <input type="text" style="display:none"  name="order[]" size="5" value="<?php echo $row->ordering;?>" class="width-20 text-area-order " />
        </td>
        <?php else: ?>
        <td><?php echo $key+1; ?></td>
        <?php endif; ?>
        <td class="k2Center center"><?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key ); ?></td>
        <td><a href="<?php echo JRoute::_('index.php?option=com_k2&view=extrafield&cid='.$row->id); ?>"><?php echo $row->name; ?></a><br /><?php echo JText::_('K2_ALIAS'); ?>: <?php echo $row->alias; ?></td>
        <td class="k2Center center hidden-phone"><?php echo $row->groupname; ?></td>
        <?php if(K2_JVERSION != '30'): ?>
        <td class="order">
        	<span><?php echo $this->page->orderUpIcon($key, ($row->group == @$this->rows[$key-1]->group), 'orderup', 'Move Up', $this->ordering); ?></span>
        	<span><?php echo $this->page->orderDownIcon($key, count($this->rows), ($row->group == @$this->rows[$key+1]->group), 'orderdown', 'Move Down', $this->ordering); ?></span>
          	<?php $disabled = $this->ordering ?  '' : 'disabled="disabled"'; ?>
          	<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
        </td>
        <?php endif; ?>
        <td class="k2Center center hidden-phone"><?php echo JText::_('K2_EXTRA_FIELD_'.JString::strtoupper($row->type)); ?></td>
        <td class="k2Center center"><?php echo $row->status; ?></td>
        <td class="k2Center center hidden-phone"><?php echo $row->id; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="8">
        	<?php if(K2_JVERSION == '30'): ?>
			<div class="k2LimitBox">
				<?php echo $this->page->getLimitBox(); ?>
			</div>
			<?php endif; ?>
        	<?php echo $this->page->getListFooter(); ?>
        </td>
      </tr>
    </tfoot>
  </table>
  <input type="hidden" name="option" value="com_k2" />
  <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>