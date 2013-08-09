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
	\$K2(document).ready(function(){
		\$K2('#K2ImportUsersButton').click(function(event){
			var answer = confirm('".JText::_('K2_WARNING_YOU_ARE_ABOUT_TO_IMPORT_JOOMLA_USERS_TO_K2_GENERATING_CORRESPONDING_K2_USER_GROUPS_IF_YOU_HAVE_EXECUTED_THIS_OPERATION_BEFORE_DUPLICATE_CONTENT_MAY_BE_PRODUCED', true)."');
			if (!answer){
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
			<?php echo JText::_('K2_FILTER'); ?>
			<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area"	title="<?php echo JText::_('K2_FILTER_BY_NAME'); ?>" />
			<button id="k2SubmitButton"><?php echo JText::_('K2_GO'); ?></button>
			<button id="k2ResetButton"><?php echo JText::_('K2_RESET'); ?></button>
		</td>
		<td class="k2AdminTableFiltersSelects hidden-phone">
			<?php echo $this->lists['filter_group_k2']; ?>
			<?php echo $this->lists['filter_group']; ?>
			<?php echo $this->lists['status']; ?>
		</td>
	</tr>
</table>
<table class="adminlist table table-striped">
    <thead>
      <tr>
        <th class="hidden-phone">#</th>
        <th><input id="jToggler" type="checkbox" name="toggle" value="" /></th>
        <th><?php echo JHTML::_('grid.sort', 'K2_NAME', 'juser.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_USERNAME', 'juser.username', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center"><?php echo JText::_('K2_LOGGED_IN'); ?></th>
        <th class="center"><?php echo JHTML::_('grid.sort', 'K2_ENABLED', 'juser.block', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_JOOMLA_GROUP', 'juser.usertype', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th><?php echo JHTML::_('grid.sort', 'K2_EMAIL', 'juser.email', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_LAST_VISIT', 'juser.lastvisitDate', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center hidden-phone"><?php echo JText::_('K2_LAST_RECORDED_IP'); ?></th>
        <th class="center"><?php echo JText::_('K2_FLAG_AS_SPAMMER'); ?></th>
        <th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ID', 'juser.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="13">
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
      <?php	foreach ($this->rows as $key => $row): ?>
      <tr class="row<?php echo ($key%2); ?>">
        <td class="hidden-phone"><?php echo $key+1; ?></td>
        <td class="k2Center"><?php $row->checked_out = 0; echo JHTML::_('grid.id', $key, $row->id ); ?></td>
        <td><a href="<?php echo $row->link; ?>"><?php echo $row->name; ?></a></td>
        <td class="hidden-phone"><?php echo $row->username; ?></td>
        <td class="k2Center center"><?php echo $row->loggedInStatus; ?></td>
        <td class="k2Center center"><?php echo $row->blockStatus; ?></td>
        <td class="hidden-phone"><?php echo $row->usertype; ?></td>
        <td class="hidden-phone"><?php echo $row->groupname; ?></td>
        <td><?php echo $row->email; ?></td>
        <td class="k2Date hidden-phone"><?php echo ($row->lvisit) ? JHTML::_('date', $row->lvisit , $this->dateFormat):JText::_('K2_NEVER'); ?></td>
        <td class="k2Center center hidden-phone">
					<?php if($row->ip): ?>
					<a target="_blank" href="http://www.ipchecking.com/?ip=<?php echo $row->ip; ?>&check=Lookup">
						<?php echo $row->ip; ?>
					</a>
					<?php endif; ?>
        </td>
        <td class="k2Center center">
        	<?php if(!$row->block): ?>
        	<a class="k2ReportUserButton k2IsIcon" href="<?php echo JRoute::_('index.php?option=com_k2&view=user&task=report&id='.$row->id); ?>">&times;</a>
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
  <?php echo JHTML::_( 'form.token' ); ?>
</form>