<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	\$K2(document).ready(function(){
		\$K2('#K2ImportContentButton').click(function(event){
			var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_ALL_SECTIONS_CATEGORIES_AND_ARTICLES_FROM_JOOMLAS_CORE_CONTENT_COMPONENT_COM_CONTENT_INTO_K2_IF_THIS_IS_THE_FIRST_TIME_YOU_IMPORT_CONTENT_TO_K2_AND_YOUR_SITE_HAS_MORE_THAN_A_FEW_THOUSAND_ARTICLES_THE_PROCESS_MAY_TAKE_A_FEW_MINUTES_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED', true)."');
			if(!answer){
				event.preventDefault();
			}
		});
	});
");

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="k2AdminTableFilters table">
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
				<?php echo $this->lists['trash']; ?>
				<?php echo $this->lists['featured']; ?>
				<?php echo $this->lists['categories']; ?>
				<?php if(isset($this->lists['tag'])): ?>
				<?php echo $this->lists['tag']; ?>
				<?php endif; ?>
				<?php echo $this->lists['authors']; ?> <?php echo $this->lists['state']; ?>
				<?php if(isset($this->lists['language'])): ?>
				<?php echo $this->lists['language']; ?>
				<?php endif; ?>
				<?php foreach($this->filters as $filter):?>
				<?php echo $filter; ?>
				<?php endforeach; ?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped" id="k2ItemsList">
		<thead>
			<tr>
				<?php if(K2_JVERSION == '30'): ?>
				<th width="1%" class="center hidden-phone">
					<?php if($this->filter_featured=='1'): ?>
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.featured_ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_FEATURED_ORDER'); ?>
					<?php else: ?>
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'K2_ORDER'); ?>
					<?php endif; ?>
				</th>
				<?php else: ?>
				<th>#</th>
				<?php endif; ?>
				<th class="center">
					<input id="jToggler" type="checkbox" name="toggle" value="" />
				</th>
				<th class="title"> <?php echo JHTML::_('grid.sort', 'K2_TITLE', 'i.title', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="center"> <?php echo JHTML::_('grid.sort', 'K2_FEATURED', 'i.featured', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="center"> <?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'i.published', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<?php if(K2_JVERSION != '30'): ?>
				<th>
					<?php if($this->filter_featured=='1'): ?>
					<?php echo JHTML::_('grid.sort', 'K2_FEATURED_ORDER', 'i.featured_ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?>
					<?php if($this->ordering) {echo JHTML::_('grid.order',  $this->rows, 'filesave.png','savefeaturedorder');} ?>
					<?php else: ?>
					<?php echo JHTML::_('grid.sort', 'K2_ORDER', 'i.ordering', @$this->lists['order_Dir'], @$this->lists['order']); ?>
					<?php if($this->ordering) {echo JHTML::_('grid.order',  $this->rows);} ?>
					<?php endif; ?>
				</th>
				<?php endif; ?>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_CATEGORY', 'category', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_AUTHOR', 'author', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_LAST_MODIFIED_BY', 'moderator', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone center"> <?php echo JHTML::_('grid.sort', 'K2_ACCESS_LEVEL', 'i.access', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_CREATED', 'i.created', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_MODIFIED', 'i.modified', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="center hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_HITS', 'i.hits', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<th class="hidden-phone center"> <?php echo JText::_('K2_IMAGE'); ?> </th>
				<?php if(isset($this->lists['language'])): ?>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_LANGUAGE', 'i.language', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<?php endif; ?>
				<th class="hidden-phone"> <?php echo JHTML::_('grid.sort', 'K2_ID', 'i.id', @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<?php foreach($this->columns as $column):?>
				<th> <?php echo JHTML::_('grid.sort', $column->label, $column->property, @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 16+sizeof($this->columns); ?>">
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
			<?php foreach ($this->rows as $key => $row): ?>
			<tr class="row<?php echo ($key%2); ?>"<?php if($this->filter_featured!='1') echo ' sortable-group-id="'.$row->catid.'"'; ?>>
				<?php if(K2_JVERSION == '30'): ?>
				<td class="order center hidden-phone">
					<?php if($row->canChange): ?>
					<span class="sortable-handler<?php echo ($this->ordering) ? '' : ' inactive tip-top' ; ?>" title="<?php echo ($this->ordering) ? '' : JText::_('JORDERINGDISABLED'); ?>" rel="tooltip"><i class="icon-menu"></i></span>
					<input type="text" style="display:none"  name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order " />
					<?php else: ?>
					<span class="sortable-handler inactive" ><i class="icon-menu"></i></span>
					<?php endif; ?>
				</td>
				<?php else: ?>
				<td><?php echo $key+1; ?></td>
				<?php endif; ?>
				<td class="center"><?php echo @JHTML::_('grid.checkedout', $row, $key); ?></td>
				<td>
					<?php if($this->table->isCheckedOut($this->user->get('id'), $row->checked_out)): ?>
					<?php echo $row->title; ?>
					<?php else: ?>
					<?php if(!$this->filter_trash): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_k2&view=item&cid='.$row->id); ?>"><?php echo $row->title; ?></a>
					<?php else: ?>
					<?php echo $row->title; ?>
					<?php endif; ?>
					<?php endif; ?>
				</td>
				<td class="k2Center center"><?php echo $row->featuredStatus; ?></td>
				<td class="k2Center center"><?php echo $row->status; ?></td>
				<?php if(K2_JVERSION != '30'): ?>
				<td class="order k2Order">
					<?php if($this->filter_featured=='1'): ?>
					<span><?php echo $this->page->orderUpIcon($key, true, 'featuredorderup', 'K2_MOVE_UP', $this->ordering); ?></span> <span><?php echo $this->page->orderDownIcon($key, count($this->rows), true, 'featuredorderdown', 'K2_MOVE_DOWN', $this->ordering); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->featured_ordering; ?>" <?php echo ($this->ordering) ?  '' : 'disabled="disabled"' ?> class="text_area k2OrderBox" />
					<?php else: ?>
					<span><?php echo $this->page->orderUpIcon($key, ($row->catid == @$this->rows[$key-1]->catid), 'orderup', 'K2_MOVE_UP', $this->ordering); ?></span> <span><?php echo $this->page->orderDownIcon($key, count($this->rows), ($row->catid == @$this->rows[$key+1]->catid), 'orderdown', 'K2_MOVE_DOWN', $this->ordering); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo ($this->ordering)?  '' : 'disabled="disabled"' ?> class="text_area k2OrderBox" />
					<?php endif; ?>
				</td>
				<?php endif; ?>
				<td class="hidden-phone"><a href="<?php echo JRoute::_('index.php?option=com_k2&view=category&cid='.$row->catid); ?>"><?php echo $row->category; ?></a></td>
				<td class="hidden-phone">
					<?php if($this->user->gid>23): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->created_by); ?>"><?php echo $row->author; ?></a>
					<?php else: ?>
					<?php echo $row->author; ?>
					<?php endif; ?>
				</td>
				<td class="hidden-phone">
					<?php if($this->user->gid>23): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_k2&view=user&cid='.$row->modified_by); ?>"><?php echo $row->moderator; ?></a>
					<?php else: ?>
					<?php echo $row->moderator; ?>
					<?php endif; ?>
				</td>
				<td class="k2Center hidden-phone center"><?php echo ($this->filter_trash || K2_JVERSION != '15')? $row->groupname:JHTML::_('grid.access', $row, $key); ?></td>
				<td class="k2Date hidden-phone"><?php echo JHTML::_('date', $row->created , $this->dateFormat); ?></td>
				<td class="k2Date hidden-phone"><?php echo ($row->modified == $this->nullDate) ? JText::_('K2_NEVER') : JHTML::_('date', $row->modified , $this->dateFormat); ?></td>
				<td class="center hidden-phone"><?php echo $row->hits ?></td>
				<td class="k2Center center hidden-phone">
					<?php if(JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$row->id).'_XL.jpg')): ?>
					<a href="<?php echo JURI::root(true).'/media/k2/items/cache/'.md5("Image".$row->id).'_XL.jpg'; ?>" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" class="modal">
					<?php if(K2_JVERSION == '30'): ?>
					<i class="icon-picture" title="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>"></i>
					<?php else: ?>
					<img src="templates/<?php echo $this->template; ?>/images/menu/icon-16-media.png" alt="<?php echo JText::_('K2_PREVIEW_IMAGE'); ?>" />
					<?php endif; ?>
					</a>
					<?php endif; ?>
				</td>
				<?php if(isset($this->lists['language'])): ?>
				<td class="center hidden-phone"><?php echo $row->language; ?></td>
				<?php endif; ?>
				<td class="center hidden-phone"><?php echo $row->id; ?></td>
				<?php foreach($this->columns as $column):?>
				<td <?php if($column->class){ echo 'class="'.$column->class.'"';}?>>
					<?php $property = $column->property; echo $row->$property; ?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
	<input type="hidden" name="task" value="<?php echo JRequest::getVar('task'); ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
