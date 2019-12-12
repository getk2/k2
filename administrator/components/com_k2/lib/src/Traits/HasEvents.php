<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

namespace K2\Traits;

defined('_JEXEC') || die;

/**
 * Methods for entities that have events.
 *
 * @since  __DEPLOY_VERSION__
 */
trait HasEvents
{
	/**
	 * Plugin types already imported.
	 *
	 * @var  array
	 */
	protected $pluginsImported = [];

	/**
	 * Event plugins that will be imported by default.
	 *
	 * @return  array
	 */
	public function getDefaultEventsPlugins()
	{
		return ['k2', 'content'];
	}

	/**
	 * Check if
	 *
	 * @param   string   $type  Plugin type
	 *
	 * @return  boolean
	 */
	public function hasImportedPlugin($type)
	{
		return in_array($type, $this->pluginsImported, true);
	}

	/**
	 * Import an events plugin type.
	 *
	 * @param   string  $name  Plugin type.
	 *
	 * @return  void
	 */
	public function importPlugin($name)
	{
		return $this->importPlugins((array) $name);
	}

	/**
	 * Import plugin types.
	 *
	 * @param   string  $types  Plugin types.
	 *
	 * @return  void
	 */
	public function importPlugins(array $types)
	{
		$types = array_filter(
			$types,
			function ($type)
			{
				return '' !== trim($type);
			}
		);

		foreach ($types as $type)
		{
			\JPluginHelper::importPlugin($type);

			$this->eventsPluginsImported[] = $type;
		}
	}

	/**
	 * Trigger an event.
	 *
	 * @param   string  $event        Name of the event to trigger
	 * @param   array   $params       Event parameters
	 * @param   array   $pluginTypes  Plugin types
	 *
	 * @return  array
	 */
	public function triggerEvent($event, array $params = [], array $pluginTypes = [])
	{
		$pluginTypes = $pluginTypes ?: $this->getDefaultEventsPlugins();

		$this->importPlugins($pluginTypes);

		$dispatcher = \JDispatcher::getInstance();

		return $dispatcher->trigger($event, $params);
	}
}
