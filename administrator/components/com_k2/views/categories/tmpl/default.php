<?php
/**
 * @version		$Id: default.php 1971 2013-05-01 16:04:17Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'trash') {
			var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_TRASH_THE_SELECTED_CATEGORIES_THEIR_CHILDREN_CATEGORIES_AND_ALL_THEIR_INCLUDED_ITEMS', true)."')
			if (answer){
				submitform( pressbutton );
			} else {
				return;
			}
		} else {
			submitform( pressbutton );
		}
	}
");

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="k2AdminTableFilters table">
		<tr>
			<td class="k2AdminTableFiltersSearch">
				<?php echo JText::_('K2_FILTER'); ?>
				<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area" title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>"/>
				<button id="k2SubmitButton"><?php echo JText::_('K2_GO'); ?></button>
				<button id="k2ResetButton"><?php echo JText::_('K2_RESET'); ?></button>
			</td>
			<td class="k2AdminTableFiltersSelects hidden-phone">
				<?php echo $this->lists['categories']; ?>
				<?php echo $this->lists['trash']; ?> <?php echo $this->lists['state']; ?>
				<?php if(isset($this->lists['language'])): ?>
				<?php echo $this->lists['language']; ?>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped" id="k2CategoriesList">
		<thead>
			<tr>
                <?php if(K2_JVERSION == '30'): ?>
                <th width="1%" class="center hidden-phone">
                    <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'c.ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
                </th>
                <?php else: ?>
                <th>
                    #
                </th>
                <?php endif; ?>
				<th>
					<input id="jToggler" type="checkbox" name="toggle" value="" />
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_TITLE', 'c.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<?php if(K2_JVERSION != '30'): ?>
				<th>
					<?php echo JHTML::_('grid.sort', 'K2_ORDER', 'c.ordering', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> <?php echo $this->ordering ?JHTML::_('grid.order',  $this->rows ,'filesave.png' ):''; ?>
				</th>
				<?php endif ;?>
				<th class="center hidden-phone">
					<?php echo JText::_('K2_PARAMETER_INHERITANCE'); ?>
				</th>
				<th class="center hidden-phone">
					<?php echo JHTML::_('grid.sort', 'K2_ASSOCIATED_EXTRA_FIELD_GROUPS', 'extra_fields_group', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th class="center hidden-phone">
					<?php echo JText::_('K2_TEMPLATE'); ?>
				</th>
				<th class="hidden-phone center">
					<?php echo JHTML::_('grid.sort', 'K2_ACCESS_LEVEL', 'c.access', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th class="center">
					<?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'c.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th class="center hidden-phone">
					<?php echo JText::_('K2_IMAGE'); ?>
				</th>
				<?php if(isset($this->lists['language'])): ?>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_LANGUAGE', 'c.language', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<?php endif; ?>
				<th class="hidden-phone center">
					<?php echo JHTML::_('grid.sort', 'K2_ID', 'c.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="12">
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
			<?php foreach ($this->rows as $key => $row) :	?>
			<tr class="row<?php echo ($key%2); ?>" sortable-group-id="<?php echo $row->parent; ?>">
               
                <?php if(K2_JVERSION == '30'): ?>
                <td class="order center hidden-phone">
                <?php if($row->canChange): ?>
                    <span class="sortable-handler <?php echo ($this->ordering) ? '' : 'inactive tip-top' ;?>" title="<?php echo ($this->ordering) ? '' :JText::_('JORDERINGDISABLED');?>" rel="tooltip"><i class="icon-menu"></i></span>
                    <input type="text" style="display:none"  name="order[]" size="5" value="<?php echo $row->ordering;?>" class="width-20 text-area-order " />
                <?php else: ?>
                     <span class="sortable-handler inactive" ><i class="icon-menu"></i></span>
                <?php endif; ?>
                </td>
                <?php else: ?>
                <td><?php echo $key+1; ?></td>
                <?php endif; ?>
				<td class="k2Center center">
					<?php if(!$this->filter_trash || $row->trash) { $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key );}?>
				</td>
				<td>
					<?php if ($this->filter_trash): ?>
					<?php if ($row->trash): ?>
					<strong><?php echo $row->treename; ?> (<?php echo $row->numOfTrashedItems; ?>)</strong>
					<?php else: ?>
					<?php echo $row->treename; ?> (<?php echo $row->numOfItems.' '.JText::_('K2_ACTIVE'); ?> / <?php echo $row->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)
					<?php endif; ?>
					<?php else: ?>
					<a href="<?php echo JRoute::_('index.php?option=com_k2&view=category&cid='.$row->id); ?>"><?php echo $row->treename; ?>
					<?php if($this->params->get('showItemsCounterAdmin')): ?>
					<span class="small">
					(<?php echo $row->numOfItems.' '.JText::_('K2_ACTIVE'); ?> / <?php echo $row->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)
					</span>
					<?php endif; ?>
					</a>
					<?php endif; ?>
				</td>
				<?php if(K2_JVERSION != '30'): ?>
				<td class="order k2Order">
					<span><?php echo $this->page->orderUpIcon( $key, $row->parent == 0 || $row->parent == @$this->rows[$key-1]->parent, 'orderup', 'K2_MOVE_UP', $this->ordering); ?></span> <span><?php echo $this->page->orderDownIcon( $key, count($this->rows), $row->parent == 0 || $row->parent == @$this->rows[$key+1]->parent, 'orderdown', 'K2_MOVE_DOWN', $this->ordering ); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo ($this->ordering)?'':'disabled="disabled"'; ?> class="text_area k2OrderBox" />
				</td>
				<?php endif; ?>
				<td class="k2Center center hidden-phone">
					<?php echo $row->inheritFrom; ?>
				</td>
				<td class="k2Center center hidden-phone">
					<?php echo $row->extra_fields_group; ?>
				</td>
				<td class="k2Center center hidden-phone">
					<?php echo $row->template; ?>
				</td>
				<td class="k2Center hidden-phone center">
					<?php echo ($this->filter_trash || K2_JVERSION != '15')? $row->groupname:JHTML::_('grid.access', $row, $key ); ?>
				</td>
				<td class="k2Center center">
					<?php echo $row->status; ?>
				</td>
				<td class="k2Center center hidden-phone">
					<?php if($row->image): ?>
					<a href="<?php echo JURI::root().'media/k2/categories/'.$row->image; ?>" class="modal">
					    <?php if (K2_JVERSION == '30') : ?>
					    <?php echo JText::_('K2_PREVIEW_IMAGE'); ?>
					    <?php else: ?>
						<img src="templates/<?php echo $this->template; ?>/images/menu/icon-16-media.png" alt="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" />
						<?php endif; ?>
					</a>
					<?php endif; ?>
				</td>
				<?php if(isset($this->lists['language'])): ?>
				<td class="center hidden-phone"><?php echo $row->language; ?></td>
				<?php endif; ?>
				<td class="k2Center center hidden-phone">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
