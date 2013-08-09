<?php
/**
 * @version		$Id: element.php 1971 2013-05-01 16:04:17Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?php echo JText::_('K2_SELECT_ITEMS'); ?></h1>
	<table class="k2AdminTableFilters table">
    	<tr>
			<td class="k2AdminTableFiltersSearch">
				<?php echo JText::_('K2_FILTER'); ?>
				<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area" title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>"/>
				<button id="k2SubmitButton"><?php echo JText::_('K2_GO'); ?></button>
				<button id="k2ResetButton"><?php echo JText::_('K2_RESET'); ?></button>
        	</td>
			<td class="k2AdminTableFiltersSelects hidden-phone">
				<?php echo $this->lists['trash']; ?>
				<?php echo $this->lists['featured']; ?>&nbsp;|
				<?php echo $this->lists['categories']; ?>
				<?php if(isset($this->lists['tag'])): ?>
					<?php echo $this->lists['tag']; ?>
				<?php endif; ?>
				<?php echo $this->lists['authors']; ?>
				<?php echo $this->lists['state']; ?>
				<?php if(isset($this->lists['language'])): ?>
					<?php echo $this->lists['language']; ?>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('K2_NUM'); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_TITLE', 'i.title', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_CATEGORY', 'category', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_AUTHOR', 'author', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_ACCESS_LEVEL', 'i.access', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_CREATED', 'i.created', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_ID', 'i.id', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->rows as $key => $row): ?>
			<tr class="row<?php echo ($key%2); ?>">
				<td><?php echo $key+1; ?></td>
				<td><a class="k2ListItemDisabled" title="<?php echo JText::_('K2_CLICK_TO_ADD_THIS_ITEM'); ?>" onclick="window.parent.jSelectItem('<?php echo $row->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$row->title); ?>', '<?php echo JRequest::getCmd('object', 'id'); ?>');"><?php echo $row->title; ?></a></td>
				<td><?php echo $row->category; ?></td>
				<td><?php echo $row->author; ?></td>
				<td class="k2Center"><?php echo $row->groupname; ?></td>
				<td class="k2Date"><?php echo $row->created; ?></td>
				<td><?php echo $row->id; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
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
	</table>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="items" />
	<input type="hidden" name="task" value="element" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>