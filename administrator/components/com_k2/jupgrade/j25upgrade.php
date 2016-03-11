<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_BASE') or die();

/**
 * K2 migration class from Joomla 1.5 to Joomla 1.6+
 *
 * You can also put this class into your own extension, which makes jUpgrade to use your own copy instead of this adapter class.
 * In order to do that you should have j16upgrade.xml file somewhere in your extension path containing:
 * 	<jupgrade>
 * 		<!-- Adapter class location and name -->
 * 		<installer>
 * 			<file>administrator/components/com_k2/jupgrade/j16upgrade.php</file>
 * 			<class>jUpgradeComponentK2</class>
 * 		</installer>
 * 	</jupgrade>
 * For more information, see ./j16upgrade.xml
 */
class jUpgradeComponentK2 extends jUpgradeExtensions
{
	/**
	 * Check if K2 migration is supported.
	 */
	protected function detectExtension()
	{
		return true;
	}

	/**
	 * Migrate custom information.
	 *
	 * This function gets called after all folders and tables have been copied.
	 *
	 * If you want to split this task into smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.6.4
	 * @throws	Exception
	 */
	protected function migrateExtensionCustom()
	{
		return true;
	}

	protected function copyTable_k2_categories($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			$row['access'] = $row['access'] == 0 ? 1 : $row['access'] + 1;
			$row['params'] = $this->convertParams($row['params']);
		}
		$this->setDestinationData($rows);
		return true;
	}
	
	protected function copyTable_k2_items($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			$row['access'] = $row['access'] == 0 ? 1 : $row['access'] + 1;
			$row['params'] = $this->convertParams($row['params']);
			$row['plugins'] = $this->convertParams($row['plugins']);
		}
		$this->setDestinationData($rows);
		return true;
	}

}
