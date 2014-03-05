<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;


if (version_compare(JVERSION, '1.6.0', '<'))
{
    jimport('joomla.installer.installer');

    // Load K2 language file
    $language = JFactory::getLanguage();
    $language->load('com_k2');
    $db = JFactory::getDBO();
    $status = new stdClass;
    $status->modules = array();
    $status->plugins = array();
    $src = $this->parent->getPath('source');
    $isUpdate = JFile::exists(JPATH_SITE.DS.'modules'.DS.'mod_k2_content'.DS.'mod_k2_content.php');

    $modules = $this->manifest->getElementByPath('modules');
    if (is_a($modules, 'JSimpleXMLElement') && count($modules->children()))
    {
        foreach ($modules->children() as $module)
        {
            $mname = $module->attributes('module');
            $client = $module->attributes('client');
            if (is_null($client))
            {
                $client = 'site';
            }
            $path = $client == 'administrator' ? $src.DS.'administrator'.DS.'modules'.DS.$mname : $src.DS.'modules'.DS.$mname;
            $installer = new JInstaller;
            $result = $installer->install($path);
            $status->modules[] = array('name' => $mname, 'client' => $client, 'result' => $result);
        }

        if (!$isUpdate)
        {
            $query = "UPDATE #__modules SET position='icon', ordering=99, published=1 WHERE module='mod_k2_quickicons'";
            $db->setQuery($query);
            $db->query();

            $query = "UPDATE #__modules SET position='cpanel', ordering=0, published=1 WHERE module='mod_k2_stats'";
            $db->setQuery($query);
            $db->query();
        }
    }

    $plugins = $this->manifest->getElementByPath('plugins');
    if (is_a($plugins, 'JSimpleXMLElement') && count($plugins->children()))
    {

        foreach ($plugins->children() as $plugin)
        {
            $pname = $plugin->attributes('plugin');
            $pgroup = $plugin->attributes('group');
            if ($pgroup == 'finder' || $pgroup == 'josetta_ext')
            {
                continue;
            }
            $path = $src.DS.'plugins'.DS.$pgroup;
            $installer = new JInstaller;
            $result = $installer->install($path);
            $query = "UPDATE #__plugins SET published=1 WHERE element=".$db->Quote($pname)." AND folder=".$db->Quote($pgroup);
            $db->setQuery($query);
            $db->query();
            $status->plugins[] = array('name' => $pname, 'group' => $pgroup, 'result' => $result);
        }
    }

    if (JFolder::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements'))
    {

        $elements = $this->manifest->getElementByPath('joomfish');
        if (is_a($elements, 'JSimpleXMLElement') && count($elements->children()))
        {
            foreach ($elements->children() as $element)
            {
                JFile::copy($src.DS.'administrator'.DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS.$element->data(), JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS.$element->data());
            }
        }

    }
    else
    {
        $mainframe = JFactory::getApplication();
        $mainframe->enqueueMessage(JText::_('K2_NOTICE_K2_CONTENT_ELEMENTS_FOR_JOOMFISH_WERE_NOT_COPIED_TO_THE_RELATED_FOLDER_BECAUSE_JOOMFISH_WAS_NOT_FOUND_ON_YOUR_SYSTEM'));
    }

    if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'admin.k2.php'))
    {
        JFile::delete(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'admin.k2.php');
    }

    if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'models'.DS.'cpanel.php'))
    {
        JFile::delete(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'models'.DS.'cpanel.php');
    }

    $db = JFactory::getDBO();
    $fields = $db->getTableFields('#__k2_categories');
    if (!array_key_exists('language', $fields['#__k2_categories']))
    {
        $query = "ALTER TABLE #__k2_categories ADD `language` CHAR(7) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $query = "ALTER TABLE #__k2_categories ADD INDEX (`language`)";
        $db->setQuery($query);
        $db->query();
    }

    $fields = $db->getTableFields('#__k2_items');
    if (!array_key_exists('featured_ordering', $fields['#__k2_items']))
    {
        $query = "ALTER TABLE #__k2_items ADD `featured_ordering` INT(11) NOT NULL default '0' AFTER `featured`";
        $db->setQuery($query);
        $db->query();
    }
    if (!array_key_exists('language', $fields['#__k2_items']))
    {
        $query = "ALTER TABLE #__k2_items ADD `language` CHAR(7) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $query = "ALTER TABLE #__k2_items ADD INDEX (`language`)";
        $db->setQuery($query);
        $db->query();
    }

    $query = "SELECT COUNT(*) FROM #__k2_user_groups";
    $db->setQuery($query);
    $num = $db->loadResult();

    if ($num == 0)
    {
        $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Registered', 'frontEdit=0\nadd=0\neditOwn=0\neditAll=0\npublish=0\ncomment=1\ninheritance=0\ncategories=all\n\n')";
        $db->setQuery($query);
        $db->Query();

        $query = "INSERT INTO #__k2_user_groups (`id`, `name`, `permissions`) VALUES('', 'Site Owner', 'frontEdit=1\nadd=1\neditOwn=1\neditAll=1\npublish=1\ncomment=1\ninheritance=1\ncategories=all\n\n')";
        $db->setQuery($query);
        $db->Query();

    }

    if ($fields['#__k2_items']['video'] != 'text')
    {
        $query = "ALTER TABLE #__k2_items MODIFY `video` TEXT";
        $db->setQuery($query);
        $db->query();
    }

    if ($fields['#__k2_items']['introtext'] == 'text')
    {
        $query = "ALTER TABLE #__k2_items MODIFY `introtext` MEDIUMTEXT";
        $db->setQuery($query);
        $db->query();
    }

    if ($fields['#__k2_items']['fulltext'] == 'text')
    {
        $query = "ALTER TABLE #__k2_items MODIFY `fulltext` MEDIUMTEXT";
        $db->setQuery($query);
        $db->query();
    }

   /* $query = "SHOW INDEX FROM #__k2_items";
    $db->setQuery($query);
    $indexes = $db->loadObjectList();
    $indexExists = false;
    foreach ($indexes as $index)
    {
        if ($index->Key_name == 'search')
            $indexExists = true;
    }

    if (!$indexExists)
    {
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
    foreach ($indexes as $index)
    {
        if ($index->Key_name == 'name')
            $indexExists = true;
    }

    if (!$indexExists)
    {
        $query = "ALTER TABLE #__k2_tags ADD FULLTEXT (`name`)";
        $db->setQuery($query);
        $db->query();
    }*/

    $fields = $db->getTableFields('#__k2_users');
    if (!array_key_exists('ip', $fields['#__k2_users']))
    {
        $query = "ALTER TABLE `#__k2_users` 
        ADD `ip` VARCHAR( 15 ) NOT NULL , 
        ADD `hostname` VARCHAR( 255 ) NOT NULL , 
        ADD `notes` TEXT NOT NULL";
        $db->setQuery($query);
        $db->query();
    }
    
    // Clean up empty entries in #__k2_users table caused by an issue in the K2 user plugin. Fix details: http://code.google.com/p/getk2/source/detail?r=1966
	$query = "DELETE FROM #__k2_users WHERE userID = 0";
	$db->setQuery($query);
	$db->query();
	
	// Fix media manager folder permissions
	set_time_limit(0);
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.path');
	$params = JComponentHelper::getParams('com_media');
    $root = $params->get('file_path', 'media');
	$mediaPath = JPATH_SITE.DS.JPath::clean($root);
	$folders = JFolder::folders($mediaPath, '.', true, true, array());
	foreach($folders as $folder)
	{
		@chmod($folder, 0755);
	}
	if(JFolder::exists($mediaPath.DS.'.tmb'))
	{
		@chmod($mediaPath.DS.'.tmb', 0755);
	}
	if(JFolder::exists($mediaPath.DS.'.quarantine'))
	{
		@chmod($mediaPath.DS.'.quarantine', 0755);
	}
}
?>
<?php if (version_compare(JVERSION, '1.6.0', '<')): ?>
<?php $rows = 0; ?>
<img src="<?php echo JURI::root(true); ?>/media/k2/assets/images/system/K2_Logo_126x48_24.png" alt="K2" align="right" />
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
<?php endif; ?>