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

// Installer for Joomla 1.5
if (version_compare(JVERSION, '1.6.0', '<')) {
    jimport('joomla.installer.installer');

    // Load K2 language file
    $db = JFactory::getDbo();

    $language = JFactory::getLanguage();
    $language->load('com_k2');

    $status = new stdClass;
    $status->modules = array();
    $status->plugins = array();

    $src = $this->parent->getPath('source');

    $k2AlreadyInstalled = JFile::exists(JPATH_SITE.'/modules/mod_k2_content/mod_k2_content.php');

    // Retrieve modules from the installation XML file and install one by one
    $modules = $this->manifest->getElementByPath('modules');
    if (is_a($modules, 'JSimpleXMLElement') && count($modules->children())) {
        foreach ($modules->children() as $module) {
            $mname = $module->attributes('module');
            $client = $module->attributes('client');
            if (is_null($client)) {
                $client = 'site';
            }
            $path = $client == 'administrator' ? $src.'/administrator/modules/'.$mname : $src.'/modules/'.$mname;
            $installer = new JInstaller;
            $result = $installer->install($path);
            $status->modules[] = array('name' => $mname, 'client' => $client, 'result' => $result);
        }

        if (!$k2AlreadyInstalled) {
            $query = "UPDATE #__modules SET position='icon', ordering=99, published=1 WHERE module='mod_k2_quickicons'";
            $db->setQuery($query);
            $db->query();

            $query = "UPDATE #__modules SET position='cpanel', ordering=0, published=1 WHERE module='mod_k2_stats'";
            $db->setQuery($query);
            $db->query();
        }
    }

    // Retrieve plugins from the installation XML file and install one by one
    $plugins = $this->manifest->getElementByPath('plugins');
    if (is_a($plugins, 'JSimpleXMLElement') && count($plugins->children())) {
        foreach ($plugins->children() as $plugin) {
            $pname = $plugin->attributes('plugin');
            $pgroup = $plugin->attributes('group');
            if ($pgroup == 'finder') {
                continue;
            }
            $path = $src.'/plugins/'.$pgroup;
            $installer = new JInstaller;
            $result = $installer->install($path);
            $query = "UPDATE #__plugins SET published=1 WHERE element=".$db->Quote($pname)." AND folder=".$db->Quote($pgroup);
            $db->setQuery($query);
            $db->query();
            $status->plugins[] = array('name' => $pname, 'group' => $pgroup, 'result' => $result);
        }
    }

    // Install JoomFish elements
    if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements')) {
        $elements = $this->manifest->getElementByPath('joomfish');
        if (is_a($elements, 'JSimpleXMLElement') && count($elements->children())) {
            foreach ($elements->children() as $element) {
                JFile::copy($src.'/administrator/components/com_joomfish/contentelements/'.$element->data(), JPATH_ADMINISTRATOR.'/components/com_joomfish/contentelements/'.$element->data());
            }
        }
    }

    // File Cleanups
    if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/admin.k2.php')) {
        JFile::delete(JPATH_ADMINISTRATOR.'/components/com_k2/admin.k2.php');
    }
    if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/models/cpanel.php')) {
        JFile::delete(JPATH_ADMINISTRATOR.'/components/com_k2/models/cpanel.php');
    }

    // --- DB updates ---
    $db = JFactory::getDbo();

    // Items
    $fields = $db->getTableFields('#__k2_items');
    if (!array_key_exists('featured_ordering', $fields['#__k2_items'])) {
        $query = "ALTER TABLE #__k2_items ADD `featured_ordering` INT(11) NOT NULL default '0' AFTER `featured`";
        $db->setQuery($query);
        $db->query();
    }
    if (!array_key_exists('language', $fields['#__k2_items'])) {
        $query = "ALTER TABLE #__k2_items ADD `language` CHAR(7) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $query = "ALTER TABLE #__k2_items ADD INDEX (`language`)";
        $db->setQuery($query);
        $db->query();
    }
    if ($fields['#__k2_items']['introtext'] == 'text') {
        $query = "ALTER TABLE #__k2_items MODIFY `introtext` MEDIUMTEXT";
        $db->setQuery($query);
        $db->query();
    }
    if ($fields['#__k2_items']['fulltext'] == 'text') {
        $query = "ALTER TABLE #__k2_items MODIFY `fulltext` MEDIUMTEXT";
        $db->setQuery($query);
        $db->query();
    }
    if ($fields['#__k2_items']['video'] != 'text') {
        $query = "ALTER TABLE #__k2_items MODIFY `video` TEXT";
        $db->setQuery($query);
        $db->query();
    }

    $query = "SHOW INDEX FROM #__k2_items";
    $db->setQuery($query);
    $itemIndices = $db->loadObjectList();
    $itemKeys_item = false;
    $itemKeys_idx_item = false;
    foreach ($itemIndices as $index) {
        if ($index->Key_name == 'item') {
            $itemKeys_item = true;
        }
        if ($index->Key_name == 'idx_item') {
            $itemKeys_idx_item = true;
        }
    }
    if ($itemKeys_item) {
        $query = "ALTER TABLE #__k2_items DROP INDEX `item`";
        $db->setQuery($query);
        $db->query();
    }
    if (!$itemKeys_idx_item) {
        $query = "ALTER TABLE #__k2_items ADD INDEX `idx_item` (`published`,`publish_up`,`publish_down`,`trash`,`access`)";
        $db->setQuery($query);
        $db->query();
    }

    // Categories
    $fields = $db->getTableFields('#__k2_categories');
    if (!array_key_exists('language', $fields['#__k2_categories'])) {
        $query = "ALTER TABLE #__k2_categories ADD `language` CHAR(7) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $query = "ALTER TABLE #__k2_categories ADD INDEX `idx_language` (`language`)";
        $db->setQuery($query);
        $db->query();
    }

    // Comments (add index for comments count)
    $query = "SHOW INDEX FROM #__k2_comments";
    $db->setQuery($query);
    $indexes = $db->loadObjectList();
    $indexExists = false;
    foreach ($indexes as $index) {
        if ($index->Key_name == 'countComments' || $index->Key_name == 'idx_countComments') {
            $indexExists = true;
        }
    }
    if (!$indexExists) {
        $query = "ALTER TABLE #__k2_comments ADD INDEX `idx_countComments` (`itemID`, `published`)";
        $db->setQuery($query);
        $db->query();
    }

    // Users
    $fields = $db->getTableFields('#__k2_users');
    if (!array_key_exists('ip', $fields['#__k2_users'])) {
        $query = "ALTER TABLE `#__k2_users`
            ADD `ip` VARCHAR(45) NOT NULL ,
            ADD `hostname` VARCHAR(255) NOT NULL ,
            ADD `notes` TEXT NOT NULL";
        $db->setQuery($query);
        $db->query();
    }

    // Users - add new ENUM option for "gender"
    $query = "SELECT DISTINCT gender FROM #__k2_users";
    $db->setQuery($query);
    $enumOptions = $db->loadResultArray();
    if (count($enumOptions) < 3) {
        $query = "ALTER TABLE #__k2_users MODIFY COLUMN `gender` enum('m','f','n') NOT NULL DEFAULT 'n'";
        $db->setQuery($query);
        $db->query();
    }

    // User groups (set first 2 user groups)
    $query = "SELECT COUNT(*) FROM #__k2_user_groups";
    $db->setQuery($query);
    $userGroupCount = $db->loadResult();
    if ($userGroupCount == 0) {
        $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Registered', 'comment=1\nfrontEdit=0\nadd=0\neditOwn=0\neditAll=0\npublish=0\neditPublished=0\ninheritance=0\ncategories=all\n\n')";
        $db->setQuery($query);
        $db->Query();

        $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Site Owner', 'comment=1\nfrontEdit=1\nadd=1\neditOwn=1\neditAll=1\npublish=1\neditPublished=1\ninheritance=1\ncategories=all\n\n')";
        $db->setQuery($query);
        $db->Query();
    }

    // Log for updates
    $query = "CREATE TABLE IF NOT EXISTS `#__k2_log` (
            `status` int(11) NOT NULL,
            `response` text NOT NULL,
            `timestamp` datetime NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;";
    $db->setQuery($query);
    $db->query();

    // Clean up empty entries in #__k2_users table caused by an issue in the K2 user plugin
    $query = "DELETE FROM #__k2_users WHERE userID = 0";
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

    $rows = 0; ?>
<img src="https://cdn.joomlaworks.org/joomla/extensions/k2/app/k2_logo.png" alt="K2" align="right" />
<h2><?php echo JText::_('K2_INSTALLATION_STATUS'); ?></h2>
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
