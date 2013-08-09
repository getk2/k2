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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'remove') {
			if (document.adminForm.boxchecked.value==0){
				alert('<?php echo JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_DELETE', true); ?>');
				return false;
			}
			if (confirm('<?php echo JText::_('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_COMMENTS', true); ?>')){
				submitform( pressbutton );
			} 
		} else if (pressbutton == 'deleteUnpublished') {
			if (confirm('<?php echo JText::_('K2_THIS_WILL_PERMANENTLY_DELETE_ALL_UNPUBLISHED_COMMENTS_ARE_YOU_SURE', true); ?>')){
				submitform( pressbutton );
			} 
		} else if (pressbutton == 'publish') {
			if (document.adminForm.boxchecked.value==0){
				alert('<?php echo JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_PUBLISH', true); ?>');
				return false;
			}
			submitform( pressbutton );
		} else if (pressbutton == 'unpublish') {
			if (document.adminForm.boxchecked.value==0){
				alert('<?php echo JText::_('K2_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO_UNPUBLISH', true); ?>');
				return false;
			}
			submitform( pressbutton );
		}  else { 
			submitform( pressbutton );
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if($this->mainframe->isSite()): ?>
	<div id="k2FrontendContainer">
		<div id="k2Frontend">
			<table class="k2FrontendToolbar" cellpadding="2" cellspacing="4">
				<tr>
					<td id="toolbar-publish" class="button">
						<a class="toolbar" onclick="Joomla.submitbutton('publish'); return false;" href="#"><?php echo JText::_('K2_PUBLISH'); ?></a>
					</td>
					<td id="toolbar-unpublish" class="button">
						<a class="toolbar" onclick="Joomla.submitbutton('unpublish'); return false;" href="#"><?php echo JText::_('K2_UNPUBLISH'); ?></a>
					</td>
					<td id="toolbar-delete" class="button">
						<a class="toolbar" onclick="Joomla.submitbutton('remove'); return false;" href="#"><?php echo JText::_('K2_DELETE'); ?></a>
					</td>
					<td id="toolbar-Link" class="button">
						<a onclick="Joomla.submitbutton('deleteUnpublished'); return false;" href="#"><?php echo JText::_('K2_DELETE_ALL_UNPUBLISHED'); ?></a>
					</td>
				</tr>
			</table>
			<div id="k2FrontendEditToolbar">
				<h2 class="header icon-48-k2"><?php echo JText::_('K2_MODERATE_COMMENTS_TO_MY_ITEMS'); ?></h2>
			</div>
			<div class="clr"></div>
			<hr class="sep" />
			<?php endif; ?>
			<table class="k2AdminTableFilters table">
				<tr>
					<td class="k2AdminTableFiltersSearch">
						<?php echo JText::_('K2_FILTER'); ?>
						<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area" title="<?php echo JText::_('K2_FILTER_BY_COMMENT'); ?>"/>
						<button id="k2SubmitButton"><?php echo JText::_('K2_GO'); ?></button>
						<button id="k2ResetButton"><?php echo JText::_('K2_RESET'); ?></button>
					</td>
					<td class="k2AdminTableFiltersSelects hidden-phone">
						<?php echo $this->lists['categories']; ?>
						<?php if($this->mainframe->isAdmin()): ?>
						<?php echo $this->lists['authors']; ?>
						<?php endif; ?>
						<?php echo $this->lists['state']; ?>
					</td>
				</tr>
			</table>
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th class="center hidden-phone">
							#
						</th>
						<th class="center">
							<input id="jToggler" type="checkbox" name="toggle" value="" />
						</th>
						<th>
							<?php echo JHTML::_('grid.sort', 'K2_COMMENT', 'c.commentText', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="center">
							<?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'c.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_NAME', 'c.userName', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th>
							<?php echo JHTML::_('grid.sort', 'K2_EMAIL', 'c.commentEmail', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_URL', 'c.commentURL', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="center hidden-phone">
							<?php echo JText::_('K2_LAST_RECORDED_IP'); ?>
						</th>
						<th class="center">
							<?php echo JText::_('K2_FLAG_AS_SPAMMER'); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_ITEM', 'i.title', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_CATEGORY', 'cat.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JText::_('K2_AUTHOR'); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_DATE', 'c.commentDate', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHTML::_('grid.sort', 'K2_ID', 'c.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="15">
							<div class="k2CommentsPagination">
								<?php if(K2_JVERSION == '30'): ?>
								<div class="k2LimitBox">
									<?php echo $this->page->getLimitBox(); ?>
								</div>
								<?php endif; ?>
								<?php echo $this->page->getListFooter(); ?>
							</div>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->rows as $key=>$row): ?>
					<tr class="row<?php echo ($key%2); ?>">
						<td class="center hidden-phone">
							<?php echo $key+1; ?>
						</td>
						<td class="center">
							<?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key ); ?>
						</td>
						<td id="k2Comment<?php echo $row->id; ?>">
							<div class="commentText"><?php echo $row->commentText; ?></div>
							<div class="commentToolbar"><span class="k2CommentsLog"></span> <a href="#" rel="<?php echo $row->id; ?>" class="editComment"><?php echo JText::_('K2_EDIT'); ?></a> <a href="#" rel="<?php echo $row->id; ?>" class="saveComment"><?php echo JText::_('K2_SAVE'); ?></a> <a href="#" rel="<?php echo $row->id; ?>" class="closeComment"><?php echo JText::_('K2_CANCEL'); ?></a>
								<div class="clr"></div>
							</div>
							<input type="hidden" name="currentValue[]" value="<?php echo $row->commentText; ?>" />
						</td>
						<td class="k2Center center">
							<?php echo $row->status; ?>
						</td>
						<td class="hidden-phone">
							<?php if($this->mainframe->isAdmin() && $row->userID): ?>
							<a href="<?php echo $this->userEditLink.$row->userID;?>"><?php echo $row->userName; ?></a>
							<?php else :?>
							<?php echo $row->userName; ?>
							<?php endif; ?>
						</td>
						<td class="k2ForceWrap">
							<?php echo $row->commentEmail; ?>
						</td>
						<td class="k2ForceWrap hidden-phone">
							<a target="_blank" href="<?php echo JFilterOutput::cleanText($row->commentURL); ?>"><?php echo str_replace(array('http://www.','https://www.','http://','https://'),array('','','',''),$row->commentURL); ?></a>
						</td>
						<td class="k2Center center hidden-phone">
							<?php if($row->commenterLastVisitIP): ?>
							<a target="_blank" href="http://www.ipchecking.com/?ip=<?php echo $row->commenterLastVisitIP; ?>&check=Lookup">
								<?php echo $row->commenterLastVisitIP; ?>
							</a>
							<?php endif; ?>
						</td>
		        <td class="k2Center center">
		        	<?php if($row->reportUserLink): ?>
		        	<a class="k2ReportUserButton k2IsIcon" href="<?php echo $row->reportUserLink; ?>">&times;</a>
		        	<?php endif; ?>
		        </td>
						<td class="hidden-phone">
							<a class="modal" rel="{handler: 'iframe', size: {x: 1000, y: 600}}"	href="<?php echo JURI::root().K2HelperRoute::getItemRoute($row->itemID.':'.urlencode($row->itemAlias),$row->catid.':'.urlencode($row->catAlias)); ?>"><?php echo $row->title; ?></a>
						</td>
						<td class="hidden-phone">
							<?php echo $row->catName; ?>
						</td>
						<td class="hidden-phone">
							<?php $user = JFactory::getUser($row->created_by); echo $user->name; ?> 
						</td>
						<td class="k2Date hidden-phone">
							<?php echo JHTML::_('date', $row->commentDate , $this->dateFormat); ?>
						</td>
						<td class="hidden-phone">
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<input type="hidden" name="isSite" value="<?php echo (int)$this->mainframe->isSite(); ?>" />
			<input type="hidden" name="option" value="com_k2" />
			<input type="hidden" name="view" value="<?php echo JRequest::getCmd('view'); ?>" />
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" id="commentID" name="commentID" value="" />
			<input type="hidden" id="commentText" name="commentText" value="" />
			<?php echo JHTML::_( 'form.token' ); ?>
			<?php if($this->mainframe->isSite()): ?>
		</div>
	</div>
	<?php endif; ?>
</form>
