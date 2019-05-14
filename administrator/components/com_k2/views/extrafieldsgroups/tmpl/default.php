<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScriptDeclaration("
    Joomla.submitbutton = function(pressbutton) {
        if (pressbutton == 'remove') {
            if (confirm('".JText::_('K2_WARNING_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_EXTRA_FIELDS_GROUPS_DELETING_THE_GROUPS_WILL_ALSO_DELETE_THE_ASSIGNED_EXTRA_FIELDS', true)."')){
                submitform( pressbutton );
            }
        } else {
            submitform( pressbutton );
        }
    };
");

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="table-responsive-wrap">
        <div class="table-responsive">
            <table class="adminlist table table-striped<?php if(isset($this->rows) && count($this->rows) == 0): ?> nocontent<?php endif; ?>" id="k2ExtraFieldGroupsList">
                <thead>
                    <tr>
                        <th class="k2ui-center hidden-phone">#</th>
                        <th class="k2ui-center"><input id="k2<?php echo $this->params->get('backendListToggler', 'TogglerStandard'); ?>" type="checkbox" name="toggle" value="" /></th>
                        <th><?php echo JText::_('K2_GROUP_NAME'); ?></th>
                        <th class="hidden-phone"><?php echo JText::_('K2_ASSIGNED_CATEGORIES'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="4">
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
                    <?php if(isset($this->rows) && count($this->rows) > 0): ?>
                    <?php foreach ($this->rows as $key=>$row): ?>
                    <tr class="row<?php echo ($key%2); ?>">
                        <td class="k2ui-center hidden-phone"><?php echo $key+1; ?></td>
                        <td class="k2ui-center"><?php $row->checked_out = 0; echo @JHTML::_('grid.checkedout', $row, $key ); ?></td>
                        <td><a href="<?php echo JRoute::_('index.php?option=com_k2&view=extrafieldsgroup&cid='.$row->id); ?>"><?php echo $row->name; ?></a></td>
                        <td class="hidden-phone"><?php echo $row->categories; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="k2ui-nocontent">
                            <div class="k2ui-nocontent-message">
                                <i class="fa fa-list" aria-hidden="true"></i><?php echo JText::_('K2_BE_NO_EXTRA_FIELD_GROUPS_FOUND'); ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <input type="hidden" name="option" value="com_k2" />
    <input type="hidden" name="view" value="<?php echo JRequest::getVar('view'); ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHTML::_('form.token'); ?>
</form>
