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

// Installer & un-installer for Joomla 2.5+
class Com_K2InstallerScript
{
    public function postflight($type, $parent)
    {
        $db = JFactory::getDbo();

        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();

        $src = $parent->getParent()->getPath('source');

        $k2AlreadyInstalled = JFile::exists(JPATH_SITE.'/modules/mod_k2_content/mod_k2_content.php');

        // Get extension manifest
        $manifest = $parent->getParent()->manifest;

        // Install K2 modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            if (is_null($client)) {
                $client = 'site';
            }
            $path = ($client == 'administrator') ? $src.'/administrator/modules/'.$name : $src.'/modules/'.$name;

            $installer = new JInstaller;
            $result = $installer->install($path);
            if ($result) {
                $root = ($client == 'administrator') ? JPATH_ADMINISTRATOR : JPATH_SITE;
                if (JFile::exists($root.'/modules/'.$name.'/'.$name.'.xml')) {
                    JFile::delete($root.'/modules/'.$name.'/'.$name.'.xml');
                }
                JFile::move($root.'/modules/'.$name.'/'.$name.'.j25.xml', $root.'/modules/'.$name.'/'.$name.'.xml');
            }

            $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);

            if ($client == 'administrator' && !$k2AlreadyInstalled) {
                $position = (version_compare(JVERSION, '3.0', '<') && $name == 'mod_k2_quickicons') ? 'icon' : 'cpanel';
                $db->setQuery("UPDATE #__modules SET `position`=".$db->quote($position).", `published`='1' WHERE `module`=".$db->quote($name));
                $db->query();

                $db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
                $id = (int)$db->loadResult();

                $db->setQuery("INSERT IGNORE INTO #__modules_menu (`moduleid`,`menuid`) VALUES (".$id.", 0)");
                $db->query();
            }
        }

        // Install K2 plugins
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/'.$group;
            if (JFolder::exists($src.'/plugins/'.$group.'/'.$name)) {
                $path = $src.'/plugins/'.$group.'/'.$name;
            }

            $installer = new JInstaller;
            $result = $installer->install($path);
            if ($result && $group != 'finder') {
                if (JFile::exists(JPATH_SITE.'/plugins/'.$group.'/'.$name.'/'.$name.'.xml')) {
                    JFile::delete(JPATH_SITE.'/plugins/'.$group.'/'.$name.'/'.$name.'.xml');
                }
                JFile::move(JPATH_SITE.'/plugins/'.$group.'/'.$name.'/'.$name.'.j25.xml', JPATH_SITE.'/plugins/'.$group.'/'.$name.'/'.$name.'.xml');
            }

            if ($group != 'finder') {
                $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
                $db->setQuery($query);
                $db->query();
            }

            $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
        }

        // Install JoomFish elements
        if (version_compare(JVERSION, '3.0', 'lt') && JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements')) {
            $elements = $manifest->xpath('joomfish/file');
            foreach ($elements as $element) {
                JFile::copy($src.'/administrator/components/com_joomfish/contentelements/'.$element->data(), JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data());
            }
        }

        // Cleanups
        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/admin.k2.php')) {
            JFile::delete(JPATH_ADMINISTRATOR.'/components/com_k2/admin.k2.php');
        }
        if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/models/cpanel.php')) {
            JFile::delete(JPATH_ADMINISTRATOR.'/components/com_k2/models/cpanel.php');
        }

        // Clean up empty entries in #__k2_users table caused by an issue in the K2 user plugin.
        $query = "DELETE FROM #__k2_users WHERE userID = 0";
        $db->setQuery($query);
        $db->query();

        /*
        // TO DO: Check main folders for 0755 first and then apply this fix
        // Fix media manager folder permissions
        set_time_limit(0);
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.path');
        $params = JComponentHelper::getParams('com_media');
        $root = $params->get('file_path', 'media');
        $mediaPath = JPATH_SITE.'/'.JPath::clean($root);
        $folders = JFolder::folders($mediaPath, '.', true, true, array());
        foreach ($folders as $folder) {
            @chmod($folder, 0755);
        }
        if (JFolder::exists($mediaPath.'/'.'.tmb')) {
            @chmod($mediaPath.'/'.'.tmb', 0755);
        }
        if (JFolder::exists($mediaPath.'/'.'.quarantine')) {
            @chmod($mediaPath.'/'.'.quarantine', 0755);
        }
        */

        // Display installation results
        $this->installationResults($status);
    }

    public function uninstall($parent)
    {
        $db = JFactory::getDbo();

        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();

        // Get extension manifest
        $manifest = $parent->getParent()->manifest;

        // Remove K2 modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            $db = JFactory::getDbo();
            $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions)) {
                foreach ($extensions as $id) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('module', $id);
                }
                $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
            }
        }

        // Remove K2 plugins
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($name)." AND folder = ".$db->Quote($group);
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions)) {
                foreach ($extensions as $id) {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('plugin', $id);
                }
                $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
            }
        }

        // Remove JoomFish elements
        if (version_compare(JVERSION, '3.0', 'lt') && JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements')) {
            $elements = $manifest('joomfish/file');
            if (is_array($elements)) {
                foreach ($elements as $element) {
                    if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data())) {
                        JFile::delete(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data());
                    }
                }
            }
        }

        // Display un-installation results
        $this->uninstallationResults($status);
    }

    public function update($type)
    {
        $db = JFactory::getDbo();

        // Items
        $fields = $db->getTableColumns('#__k2_items');
        if (!array_key_exists('featured_ordering', $fields)) {
            $query = "ALTER TABLE #__k2_items ADD `featured_ordering` INT(11) NOT NULL default '0' AFTER `featured`";
            $db->setQuery($query);
            $db->query();
        }
        if (!array_key_exists('language', $fields)) {
            $query = "ALTER TABLE #__k2_items ADD `language` CHAR(7) NOT NULL";
            $db->setQuery($query);
            $db->query();

            $query = "ALTER TABLE #__k2_items ADD INDEX (`language`)";
            $db->setQuery($query);
            $db->query();
        }
        if ($fields['introtext'] == 'text') {
            $query = "ALTER TABLE #__k2_items MODIFY `introtext` MEDIUMTEXT";
            $db->setQuery($query);
            $db->query();
        }
        if ($fields['fulltext'] == 'text') {
            $query = "ALTER TABLE #__k2_items MODIFY `fulltext` MEDIUMTEXT";
            $db->setQuery($query);
            $db->query();
        }
        if ($fields['video'] != 'text') {
            $query = "ALTER TABLE #__k2_items MODIFY `video` TEXT";
            $db->setQuery($query);
            $db->query();
        }

        // Categories
        $fields = $db->getTableColumns('#__k2_categories');
        if (!array_key_exists('language', $fields)) {
            $query = "ALTER TABLE #__k2_categories ADD `language` CHAR(7) NOT NULL";
            $db->setQuery($query);
            $db->query();

            $query = "ALTER TABLE #__k2_categories ADD INDEX (`language`)";
            $db->setQuery($query);
            $db->query();
        }

        // Comments (add index for comments count)
        $query = "SHOW INDEX FROM #__k2_comments";
        $db->setQuery($query);
        $indexes = $db->loadObjectList();
        $indexExists = false;
        foreach ($indexes as $index) {
            if ($index->Key_name == 'countComments') {
                $indexExists = true;
            }
        }
        if (!$indexExists) {
            $query = "ALTER TABLE #__k2_comments ADD INDEX `countComments` (`itemID`, `published`)";
            $db->setQuery($query);
            $db->query();
        }

        // Users
        $fields = $db->getTableColumns('#__k2_users');
        if (!array_key_exists('ip', $fields)) {
            $query = "ALTER TABLE `#__k2_users`
            ADD `ip` VARCHAR( 15 ) NOT NULL ,
            ADD `hostname` VARCHAR( 255 ) NOT NULL ,
            ADD `notes` TEXT NOT NULL";
            $db->setQuery($query);
            $db->query();
        }

        // User groups (set first 2 user groups)
        $query = "SELECT COUNT(*) FROM #__k2_user_groups";
        $db->setQuery($query);
        $num = $db->loadResult();

        if ($num == 0) {
            $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Registered', '{\"comment\":\"1\",\"frontEdit\":\"0\",\"add\":\"0\",\"editOwn\":\"0\",\"editAll\":\"0\",\"publish\":\"0\",\"inheritance\":0,\"categories\":\"all\"}')";
            $db->setQuery($query);
            $db->query();

            $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Site Owner', '{\"comment\":\"1\",\"frontEdit\":\"1\",\"add\":\"1\",\"editOwn\":\"1\",\"editAll\":\"1\",\"publish\":\"1\",\"inheritance\":1,\"categories\":\"all\"}')";
            $db->setQuery($query);
            $db->query();
        }

        // Log for updates
        $query = "CREATE TABLE IF NOT EXISTS `#__k2_log` (
	        `status` int(11) NOT NULL,
	        `response` text NOT NULL,
	        `timestamp` datetime NOT NULL
	        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->setQuery($query);
        $db->query();

        /*
        // TO DO: Use the following info to remove FULLTEXT attributes from the items & tags tables
        $query = "SHOW INDEX FROM #__k2_items";
        $db->setQuery($query);
        $indexes = $db->loadObjectList();
        $indexExists = false;
        foreach ($indexes as $index) {
            if ($index->Key_name == 'search') {
                $indexExists = true;
            }
        }

        if (!$indexExists) {
            $query = "ALTER TABLE #__k2_items ADD FULLTEXT `search` (`title`,`introtext`,`fulltext`,`extra_fields_search`,`image_caption`,`image_credits`,`video_caption`,`video_credits`,`metadesc`,`metakey`)";
            $db->setQuery($query);
            $db->query();

            $query = "ALTER TABLE #__k2_items ADD FULLTEXT (`title`)";
            $db->setQuery($query);
            $db->query();
        }

        $query = "SHOW INDEX FROM #__k2_tags";
        $db->setQuery($query);
        $indexes = $db->loadObjectList();
        $indexExists = false;
        foreach ($indexes as $index) {
            if ($index->Key_name == 'name') {
                $indexExists = true;
            }
        }

        if (!$indexExists) {
            $query = "ALTER TABLE #__k2_tags ADD FULLTEXT (`name`)";
            $db->setQuery($query);
            $db->query();
        }
        */
    }

    private function installationResults($status)
    {
        $language = JFactory::getLanguage();
        $language->load('com_k2');
        $rows = 0; ?>
        <img src="https://cdn.joomlaworks.org/joomla/extensions/k2/app/k2_logo.png" alt="K2" align="right" />
        <h2><?php echo JText::_('K2_INSTALLATION_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
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
                    <td><strong><?php echo JText::_('K2_INSTALLED'); ?></strong></td>
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
                    <td><strong><?php echo ($module['result'])?JText::_('K2_INSTALLED'):JText::_('K2_NOT_INSTALLED'); ?></strong></td>
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
                    <td><strong><?php echo ($plugin['result'])?JText::_('K2_INSTALLED'):JText::_('K2_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php
    }

    private function uninstallationResults($status)
    {
        $language = JFactory::getLanguage();
        $language->load('com_k2');
        $rows = 0; ?>
        <img src="https://cdn.joomlaworks.org/joomla/extensions/k2/app/k2_logo.png" alt="K2" align="right" />
        <h2><?php echo JText::_('K2_REMOVAL_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
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
}
