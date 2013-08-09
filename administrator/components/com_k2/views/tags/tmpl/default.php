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
		if (pressbutton == 'remove') {
			if (confirm('".JText::_('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_TAGS', true)."')){
				submitform( pressbutton );
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
		<input type="text" name="search" value="<?php echo $this->lists['search'] ?>" class="text_area" title="<?php echo JText::_('K2_FILTER_BY_NAME'); ?>"/>
		<button id="k2SubmitButton"><?php echo JText::_('K2_GO'); ?></button>
		<button id="k2ResetButton"><?php echo JText::_('K2_RESET'); ?></button>
      </td>
      <td class="k2AdminTableFiltersSelects hidden-phone">
      	<?php echo $this->lists['state']; ?>
      </td>
    </tr>
  </table>
  <table class="adminlist table table-striped">
    <thead>
      <tr>
        <th class="center hidden-phone">#</th>
        <th class="center"><input id="jToggler" type="checkbox" name="toggle" value="" /></th>
        <th><?php echo JHTML::_('grid.sort', 'K2_NAME', 'name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center"><?php echo JHTML::_('grid.sort', 'K2_PUBLISHED', 'published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ITEMS', 'numOfItems', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
        <th class="center hidden-phone"><?php echo JHTML::_('grid.sort', 'K2_ID', 'id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="6">
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
        <td><a href="<?php echo JRoute::_('index.php?option=com_k2&view=tag&cid='.$row->id); ?>"><?php echo $row->name; ?></a></td>
        <td class="k2Center center"><?php echo $row->status; ?></td>
        <td class="k2Center center hidden-phone"><?php echo $row->numOfItems; ?></td>
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
