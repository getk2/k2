<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="k2AdminTableData">
        <table class="adminlist table table-striped<?php if (isset($this->rows) && count($this->rows) == 0): ?> nocontent<?php endif; ?>" id="k2UserGroupsList">
            <thead>
                <tr>
                    <th class="k2ui-center k2ui-hide-on-mobile">#</th>
                    <th class="k2ui-center"><input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" /></th>
                    <th class="title"><?php echo JHTML::_('grid.sort', 'K2_NAME', 'name', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
                    <th class="k2ui-center k2ui-hide-on-mobile"><?php echo JHTML::_('grid.sort', 'K2_USER_COUNT', 'numOfUsers', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
                    <th class="k2ui-center k2ui-hide-on-mobile"><?php echo JHTML::_('grid.sort', 'K2_ID', 'id', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <?php if (K2_JVERSION == '30'): ?>
                        <div class="k2LimitBox">
                            <?php echo $this->page->getLimitBox(); ?>
                        </div>
                        <?php endif; ?>
                        <?php echo $this->page->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (isset($this->rows) && count($this->rows) > 0): ?>
                <?php foreach ($this->rows as $key => $row): ?>
                <tr class="row<?php echo($key%2); ?>">
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $key+1; ?></td>
                    <td class="k2ui-center"><?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key); ?></td>
                    <td><a href="<?php echo JRoute::_('index.php?option=com_k2&view=usergroup&cid='.$row->id); ?>"><?php echo $row->name; ?></a></td>
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->numOfUsers; ?></td>
                    <td class="k2ui-center k2ui-hide-on-mobile"><?php echo $row->id; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="k2ui-nocontent">
                        <div class="k2ui-nocontent-message">
                            <i class="fa fa-list" aria-hidden="true"></i><?php echo JText::_('K2_BE_NO_USER_GROUPS_FOUND'); ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHTML::_('form.token'); ?>
</form>
