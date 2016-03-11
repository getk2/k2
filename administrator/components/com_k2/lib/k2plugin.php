<?php
/**
 * @version     2.7.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');

JLoader::register('K2Parameter', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'k2parameter.php');

class K2Plugin extends JPlugin
{

	/**
	 * Below we list all available BACKEND events, to trigger K2 plugins and generate additional fields in the item, category and user forms.
	 */

	/* ------------ Functions to render plugin parameters in the backend - no need to change anything ------------ */
	function onRenderAdminForm(&$item, $type, $tab = '')
	{

		$mainframe = JFactory::getApplication();
		$manifest = (K2_JVERSION == '15') ? JPATH_SITE.DS.'plugins'.DS.'k2'.DS.$this->pluginName.'.xml' : JPATH_SITE.DS.'plugins'.DS.'k2'.DS.$this->pluginName.DS.$this->pluginName.'.xml';
		if (!empty($tab))
		{
			$path = $type.'-'.$tab;
		}
		else
		{
			$path = $type;
		}
		if (!isset($item->plugins))
		{
			$item->plugins = NULL;
		}

		if (K2_JVERSION == '15')
		{

			$form = new K2Parameter($item->plugins, $manifest, $this->pluginName);
			$fields = $form->render('plugins', $path);
		}
		else
		{
			jimport('joomla.form.form');
			$form = JForm::getInstance('plg_k2_'.$this->pluginName.'_'.$path, $manifest, array(), true, 'fields[@group="'.$path.'"]');
			$values = array();
			if ($item->plugins)
			{
				foreach (json_decode($item->plugins) as $name => $value)
				{
					$count = 1;
					$values[str_replace($this->pluginName, '', $name, $count)] = $value;
				}
				$form->bind($values);
			}
			$fields = '';
			foreach ($form->getFieldset() as $field)
			{

				if (strpos($field->name, '[]') !== false)
				{
					$search = 'name="'.$field->name.'"';
					$replace = 'name="plugins['.$this->pluginName.str_replace('[]', '', $field->name).'][]"';
				}
				else
				{
					$search = 'name="'.$field->name.'"';
					$replace = 'name="plugins['.$this->pluginName.$field->name.']"';
				}
				$input = JString::str_ireplace($search, $replace, $field->__get('input'));
				$fields .= $field->__get('label').' '.$input;
			}

			// Legacy code to maintain compatibillity with existing plugins that use params instead of JForm
			if (empty($fields) && K2_JVERSION == '25')
			{
				$form = new K2Parameter($item->plugins, $manifest, $this->pluginName);
				$fields = $form->render('plugins', $path);
			}

		}
		if ($fields)
		{
			$plugin = new stdClass;
			$plugin->name = $this->pluginNameHumanReadable;
			$plugin->fields = $fields;
			return $plugin;
		}
	}

}
