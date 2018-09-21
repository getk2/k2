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

$app = JFactory::getApplication();
$context = JRequest::getCmd('context');

?>

<?php if($app->isSite() || $context == "modalselector"): ?>
<!-- Frontend Comments Moderation (Modal View) -->
<div id="k2ModalContainer">
	<div id="k2ModalHeader">
		<h2 id="k2ModalLogo"><?php echo JText::_('K2_MODERATE_COMMENTS_TO_MY_ITEMS'); ?></h2>
		<table id="k2ModalToolbar" cellpadding="2" cellspacing="4">
			<tr>
				<td class="button">
					<a class="toolbar" onclick="Joomla.submitbutton('publish');return false;" href="#">
						<i class="fa fa-check" aria-hidden="true"></i> <?php echo JText::_('K2_PUBLISH'); ?>
					</a>
				</td>
				<td class="button">
					<a class="toolbar" onclick="Joomla.submitbutton('unpublish');return false;" href="#">
						<i class="fa fa-times-circle" aria-hidden="true"></i> <?php echo JText::_('K2_UNPUBLISH'); ?>
					</a>
				</td>
				<td class="button">
					<a class="toolbar" onclick="Joomla.submitbutton('remove');return false;" href="#">
						<i class="fa fa-trash" aria-hidden="true"></i> <?php echo JText::_('K2_DELETE'); ?>
					</a>
				</td>
				<td class="button">
					<a onclick="Joomla.submitbutton('deleteUnpublished');return false;" href="#">
						<i class="fa fa-trash-o" aria-hidden="true"></i> <?php echo JText::_('K2_DELETE_ALL_UNPUBLISHED'); ?>
					</a>
				</td>
				<td id="toolbar-cancel" class="button">
					<a href="#">
						<i class="fa fa-times-circle" aria-hidden="true"></i> <?php echo JText::_('K2_CLOSE'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
<?php endif; ?>

<form action="<?php echo ($app->isSite()) ? JRoute::_('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector') : JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
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
				<?php echo $this->lists['state']; ?>
				<?php echo $this->lists['categories']; ?>
				<?php if($this->mainframe->isAdmin()): ?>
				<?php echo $this->lists['authors']; ?>
				<?php endif; ?>
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
					<input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" />
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
				<th class="center k2NoWrap">
					<?php echo JHTML::_('grid.sort', 'K2_EMAIL', 'c.commentEmail', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th class="hidden-phone">
					<?php echo JHTML::_('grid.sort', 'K2_URL', 'c.commentURL', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th class="center hidden-phone">
					IP
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
				<td colspan="14">
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
					<div class="commentToolbar">
						<span class="k2CommentsLog"></span>
						<a href="#" rel="<?php echo $row->id; ?>" class="editComment"><?php echo JText::_('K2_EDIT'); ?></a>
						<div class="k2CommentControls">
							<a href="#" rel="<?php echo $row->id; ?>" class="saveComment"><?php echo JText::_('K2_SAVE'); ?></a>
							<span class="k2OptionSep"><?php echo JText::_('K2_OR'); ?></span>
							<a href="#" rel="<?php echo $row->id; ?>" class="closeComment"><?php echo JText::_('K2_CANCEL'); ?></a>
						</div>
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
				<td class="center">
					<a href="mailto:<?php echo JFilterOutput::cleanText($row->commentEmail); ?>" title="<?php echo JFilterOutput::cleanText($row->commentEmail); ?>"><i class="fa fa-envelope-o" aria-hidden="true"></i></a> <a target="_blank" href="https://hunter.io/email-verifier/<?php echo JFilterOutput::cleanText($row->commentEmail); ?>" title="<?php echo JText::_('K2_TEST_EMAIL_ADRESS_VALID'); ?>: <?php echo JFilterOutput::cleanText($row->commentEmail); ?>"><i class="fa fa-question-circle-o" aria-hidden="true"></i></a>
				</td>
				<td class="k2ForceWrap hidden-phone">
					<?php if($row->commentURL): ?>
					<a target="_blank" href="<?php echo JFilterOutput::cleanText($row->commentURL); ?>" title="<?php echo JFilterOutput::cleanText($row->commentURL); ?>">
						<i class="fa fa-globe" aria-hidden="true"></i>
					</a>
					<?php endif; ?>
				</td>
				<td class="k2Center center hidden-phone">
					<?php if($row->commenterLastVisitIP): ?>
					<a target="_blank" href="https://ipalyzer.com/<?php echo $row->commenterLastVisitIP; ?>">
						<?php echo $row->commenterLastVisitIP; ?>
					</a>
					<?php endif; ?>
				</td>
				<td class="k2Center center">
					<?php if($row->reportUserLink): ?>
					<a class="k2ReportUserButton k2IsIcon" href="<?php echo $row->reportUserLink; ?>"><i class="fa fa-ban" aria-hidden="true"></i></a>
					<?php endif; ?>
				</td>
				<td class="hidden-phone">
					<?php $itemURL = K2HelperRoute::getItemRoute($row->itemID.':'.urlencode($row->itemAlias),$row->catid.':'.urlencode($row->catAlias)); ?>
					<a target="_blank" href="<?php echo ($app->isSite()) ? JRoute::_($itemURL) : JURI::root().$itemURL; ?>"><?php echo $row->title; ?></a>
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

	<input type="hidden" id="commentID" name="commentID" value="" />
	<input type="hidden" id="commentText" name="commentText" value="" />
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" name="isSite" value="<?php echo (int) $this->mainframe->isSite(); ?>" />
	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="view" value="<?php echo JRequest::getCmd('view'); ?>" />
	<?php if($context == "modalselector"): ?>
	<input type="hidden" name="context" value="modalselector" />
	<input type="hidden" name="tmpl" value="component" />
	<?php if($app->isSite()): ?>
	<input type="hidden" name="template" value="system" />
	<?php endif; ?>
	<?php endif; ?>
	<?php echo JHTML::_('form.token'); ?>
</form>

<?php if($app->isSite() || $context == "modalselector"): ?>
</div>
<?php endif; ?>
