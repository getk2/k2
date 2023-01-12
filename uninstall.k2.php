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

// Un-installer for Joomla 1.5
if (version_compare(JVERSION, '1.6.0', '<')) {
    jimport('joomla.installer.installer');

    // Load K2 language file
    $lang = JFactory::getLanguage();
    $lang->load('com_k2');

    $status = new stdClass;
    $status->modules = array();
    $status->plugins = array();

    // Remove K2 modules
    $modules = $this->manifest->getElementByPath('modules');
    if (is_a($modules, 'JSimpleXMLElement') && count($modules->children())) {
        foreach ($modules->children() as $module) {
            $mname = $module->attributes('module');
            $client = $module->attributes('client');
            $db = JFactory::getDbo();
            $query = "SELECT `id` FROM `#__modules` WHERE module = ".$db->Quote($mname)."";
            $db->setQuery($query);
            $modules = $db->loadResultArray();
            if (count($modules)) {
                foreach ($modules as $module) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('module', $module, 0);
                }
            }
            $status->modules[] = array('name' => $mname, 'client' => $client, 'result' => $result);
        }
    }

    // Remove K2 plugins
    $plugins = $this->manifest->getElementByPath('plugins');
    if (is_a($plugins, 'JSimpleXMLElement') && count($plugins->children())) {
        foreach ($plugins->children() as $plugin) {
            $pname = $plugin->attributes('plugin');
            $pgroup = $plugin->attributes('group');
            if ($pgroup == 'finder') {
                continue;
            }
            $db = JFactory::getDbo();
            $query = 'SELECT `id` FROM #__plugins WHERE element = '.$db->Quote($pname).' AND folder = '.$db->Quote($pgroup);
            $db->setQuery($query);
            $plugins = $db->loadResultArray();
            if (count($plugins)) {
                foreach ($plugins as $plugin) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('plugin', $plugin, 0);
                }
            }
            $status->plugins[] = array('name' => $pname, 'group' => $pgroup, 'result' => $result);
        }
    }

    // Remove JoomFish elements
    if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements')) {
        $elements = $this->manifest->getElementByPath('joomfish/file');
        if (is_array($elements)) {
            foreach ($elements as $element) {
                if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data())) {
                    JFile::delete(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data());
                }
            }
        }
    }

    $rows = 0; ?>
<img src="https://cdn.joomlaworks.org/joomla/extensions/k2/app/k2_logo.png" alt="K2" align="right" />
<h2><?php echo JText::_('K2_REMOVAL_STATUS'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('K2_EXTENSION'); ?></th>
			<th width="30%"><?php echo JText::_('K2_STATUS'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'K2 '.JText::_('K2_COMPONENT'); ?></td>
			<td><strong><?php echo JText::_('K2_REMOVED'); ?></strong></td>
		</tr>
		<?php if (count($status->modules)): ?>
		<tr>
			<th><?php echo JText::_('K2_MODULE'); ?></th>
			<th><?php echo JText::_('K2_CLIENT'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong><?php echo ($module['result'])?JText::_('K2_REMOVED'):JText::_('K2_NOT_REMOVED'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if (count($status->plugins)): ?>
		<tr>
			<th><?php echo JText::_('K2_PLUGIN'); ?></th>
			<th><?php echo JText::_('K2_GROUP'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong><?php echo ($plugin['result'])?JText::_('K2_REMOVED'):JText::_('K2_NOT_REMOVED'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<?php
}
