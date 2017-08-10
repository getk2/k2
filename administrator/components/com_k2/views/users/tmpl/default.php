<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
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
		<h2 id="k2ModalLogo"><?php echo JText::_('K2_USERS'); ?></h2>
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
				<label class="visually-hidden"><?php echo JText::_('K2_FILTER'); ?></label>
				<div class="btn-wrapper input-append">
					<input type="text" name="search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="text_area"	title="<?php echo JText::_('K2_FILTER_BY_TITLE'); ?>" placeholder="<?php echo JText::_('K2_FILTER'); ?>" />
					<button id="k2SubmitButton" class="btn"><?php echo JText::_('K2_GO'); ?></button>
					<button id="k2ResetButton" class="btn"><?php echo JText::_('K2_RESET'); ?></button>
				</div>
			</td>
			<td class="k2AdminTableFiltersSelects hidden-phone">
				<?php echo $this->lists['status']; ?>
				<?php echo $this->lists['filter_group_k2']; ?>
				<?php echo $this->lists['filter_group']; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped">
	    <thead>
			<tr>
				<th class="hidden-phone">#</th>
				<th<?php if($context == "modalselector") echo ' class="k2VisuallyHidden"'; ?>><input id="jToggler" type="checkbox" name="toggle" value="" /></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_NAME', 'juser.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_USERNAME', 'juser.username', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th class="center"><?php echo JText::_('K2_LOGGED_IN'); ?></th>
				<th class="center"><?php echo JHTML::_('grid.sort', 'K2_ENABLED', 'juser.block', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<?php if(K2_JVERSION == '30'): ?>
				<th class="hidden-phone"><?php echo JText::_('K2_JOOMLA_GROUP'); ?></th>
				<?php else: ?>
				<th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_JOOMLA_GROUP', 'juser.usertype', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<?php endif; ?>
				<th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2_EMAIL', 'juser.email', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_LAST_VISIT', 'juser.lastvisitDate', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th class="center hidden-phone">IP</th>
				<th class="center"><?php echo JText::_('K2_FLAG_AS_SPAMMER'); ?></th>
				<th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ID', 'juser.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
			</tr>
	    </thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo ($context == "modalselector") ? '12' : '13'; ?>">
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
			<?php foreach ($this->rows as $key => $row): ?>
			<tr class="row<?php echo ($key%2); ?>">
				<td class="hidden-phone"><?php echo $key+1; ?></td>
				<td class="k2Center<?php if($context == "modalselector") echo ' k2VisuallyHidden'; ?>"><?php $row->checked_out = 0; echo JHTML::_('grid.id', $key, $row->id ); ?></td>
				<td>
					<?php if($context == "modalselector"): ?>
					<?php
					if(JRequest::getCmd('output') == 'list'){
						$onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->name).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\', \''.JRequest::getCmd('output').'\'); return false;';
					} else {
						$onClick = 'window.parent.k2ModalSelector(\''.$row->id.'\', \''.str_replace(array("'", "\""), array("\\'", ""), $row->name).'\', \''.JRequest::getCmd('fid').'\', \''.JRequest::getVar('fname').'\'); return false;';
					}
					?>
					<a class="k2ListItemDisabled" title="<?php echo JText::_('K2_CLICK_TO_ADD_THIS_ENTRY'); ?>" href="#" onclick="<?php echo $onClick; ?>"><?php echo $row->name; ?></a>
					<?php else: ?>
					<a href="<?php echo $row->link; ?>"><?php echo $row->name; ?></a>
					<?php endif; ?>
				</td>
				<td class="hidden-phone"><?php echo $row->username; ?></td>
				<td class="k2Center center"><?php echo $row->loggedInStatus; ?></td>
				<td class="k2Center center"><?php echo $row->blockStatus; ?></td>
				<td class="hidden-phone"><?php echo $row->usertype; ?></td>
				<td class="hidden-phone"><?php echo $row->groupname; ?></td>
				<td><?php echo $row->email; ?></td>
				<td class="k2Date hidden-phone"><?php echo ($row->lvisit) ? JHTML::_('date', $row->lvisit , $this->dateFormat):JText::_('K2_NEVER'); ?></td>
				<td class="k2Center center hidden-phone">
					<?php if($row->ip): ?>
					<a target="_blank" href="https://ipalyzer.com/<?php echo $row->ip; ?>"><?php echo $row->ip; ?></a>
					<?php endif; ?>
				</td>
				<td class="k2Center center">
					<?php if(!$row->block): ?>
					<?php if($context == "modalselector"): ?>
					<a class="k2ReportUserButton k2IsIcon" href="<?php echo JRoute::_('index.php?option=com_k2&view=user&task=report&tmpl=component&context=modalselector&id='.$row->id); ?>">
						<i class="fa fa-ban" aria-hidden="true"></i>
					</a>
					<?php else: ?>
					<a class="k2ReportUserButton k2IsIcon" href="<?php echo JRoute::_('index.php?option=com_k2&view=user&task=report&id='.$row->id); ?>">
						<i class="fa fa-ban" aria-hidden="true"></i>
					</a>
					<?php endif; ?>
					<?php endif; ?>
				</td>
				<td class="center hidden-phone"><?php echo $row->id; ?></td>
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
	<?php if($context == "modalselector"): ?>
	<input type="hidden" name="context" value="modalselector" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="fid" value="<?php echo JRequest::getCmd('fid'); ?>" />
	<input type="hidden" name="fname" value="<?php echo JRequest::getVar('fname'); ?>" />
	<input type="hidden" name="output" value="<?php echo JRequest::getCmd('output'); ?>" />
	<?php endif; ?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php if($app->isSite() || $context == "modalselector"): ?>
</div>
<?php endif; ?>
