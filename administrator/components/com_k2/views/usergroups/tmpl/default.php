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

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'remove') {
			if (confirm('".JText::_('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_GROUPS', true)."')){
				submitform( pressbutton );
			}
		} else {
			submitform( pressbutton );
		}
	};
");

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
  <table class="adminlist table table-striped">
    <thead>
      <tr>
        <th class="hidden-phone center">#</th>
        <th class="center"><input id="jToggler" type="checkbox" name="toggle" value="" /></th>
        <th class="title"><?php echo JHTML::_('grid.sort', 'K2_NAME', 'name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_USER_COUNT', 'numOfUsers', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ID', 'id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="5">
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
      <tr class="row<?php echo ($key%2); ?>">
        <td class="k2Center center hidden-phone"><?php echo $key+1; ?></td>
        <td class="k2Center center"><?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key ); ?></td>
        <td><a href="<?php echo JRoute::_('index.php?option=com_k2&view=usergroup&cid='.$row->id); ?>"><?php echo $row->name; ?></a></td>
        <td class="k2Center center hidden-phone"><?php echo $row->numOfUsers; ?></td>
        <td class="k2Center center hidden-phone"><?php echo $row->id; ?></td>
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
