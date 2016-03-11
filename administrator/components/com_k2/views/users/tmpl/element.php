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

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?php echo JText::_('K2_SELECT_USERS'); ?></h1>
	<table class="k2AdminTableFilters">
		<tr>
			<td class="k2AdminTableFiltersSearch">
				<!--<label><?php echo JText::_('K2_FILTER'); ?></label>-->
				<div class="btn-wrapper input-append">
					<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area"	title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>" placeholder="<?php echo JText::_('K2_FILTER'); ?>" />
					<button id="k2SubmitButton" class="btn"><?php echo JText::_('K2_GO'); ?></button>
					<button id="k2ResetButton" class="btn"><?php echo JText::_('K2_RESET'); ?></button>
				</div>
			</td>
			<td class="k2AdminTableFiltersSelects hidden-phone">
				<?php echo $this->lists['filter_group_k2']; ?> <?php echo $this->lists['filter_group']; ?> <?php echo $this->lists['status']; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('K2_NUM'); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_NAME', 'juser.name', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_USER_NAME', 'juser.username', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_ENABLED', 'juser.block', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_GROUP', 'juser.usertype', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_K2_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_ID', 'juser.id', @$this->lists['order_Dir'], @$this->lists['order'], 'element' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
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
			<?php foreach($this->rows as $key => $row): ?>
			<tr class="row<?php echo ($key%2); ?>">
				<td class="k2Center">
					<?php echo $key+1; ?>
				</td>
				<td>
					<a class="k2ListItemDisabled" title="<?php echo JText::_('K2_CLICK_TO_ADD_THIS_ITEM'); ?>"	onclick="window.parent.jSelectUser('<?php echo $row->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$row->name); ?>', 'id');"><?php echo $row->name; ?></a>
				</td>
				<td class="k2Center">
					<?php echo $row->username; ?>
				</td>
				<td class="k2Center">
					<?php echo $row->blockStatus; ?>
				</td>
				<td class="k2Center">
					<?php echo $row->usertype; ?>
				</td>
				<td class="k2Center">
					<?php echo $row->groupname; ?>
				</td>
				<td class="k2Center">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_k2" />
	<?php if ($this->isAdmin): ?>
	<input type="hidden" name="view" value="users" />
	<input type="hidden" name="task" value="element" />
	<?php else: ?>
	<input type="hidden" name="view" value="item" />
	<input type="hidden" name="task" value="users" />
	<?php endif; ?>
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
