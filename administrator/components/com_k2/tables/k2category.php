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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2Category extends K2Table
{

	var $id = null;
	var $name = null;
	var $alias = null;
	var $description = null;
	var $parent = null;
	var $extraFieldsGroup = null;
	var $published = null;
	var $image = null;
	var $access = null;
	var $ordering = null;
	var $params = null;
	var $trash = null;
	var $plugins = null;
	var $language = null;

	function __construct(&$db)
	{

		parent::__construct('#__k2_categories', 'id', $db);
	}

	function load($oid = null, $reset = false)
	{

		static $K2CategoriesInstances = array();
		if (isset($K2CategoriesInstances[$oid]))
		{
			return $this->bind($K2CategoriesInstances[$oid]);
		}

		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

		$oid = $this->$k;

		if ($oid === null)
		{
			return false;
		}
		$this->reset();

		$db = $this->getDBO();

		$query = 'SELECT *'.' FROM '.$this->_tbl.' WHERE '.$this->_tbl_key.' = '.$db->Quote($oid);
		$db->setQuery($query);
		$result = $db->loadAssoc();
		if ($result)
		{
			$K2CategoriesInstances[$oid] = $result;
			return $this->bind($K2CategoriesInstances[$oid]);
		}
		else
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
	}

	function check()
	{

		jimport('joomla.filter.output');
		$params = JComponentHelper::getParams('com_k2');
		$this->name = JString::trim($this->name);
		if ($this->name == '')
		{
			$this->setError(JText::_('K2_CATEGORY_MUST_HAVE_A_NAME'));
			return false;
		}
		if (empty($this->alias))
		{
			$this->alias = $this->name;
		}

		if (K2_JVERSION == '15')
		{
			if (JPluginHelper::isEnabled('system', 'unicodeslug') || JPluginHelper::isEnabled('system', 'jw_unicodeSlugsExtended'))
			{
				$this->alias = JFilterOutput::stringURLSafe($this->alias);
			}
			else
			{
				mb_internal_encoding("UTF-8");
				mb_regex_encoding("UTF-8");
				$this->alias = trim(mb_strtolower($this->alias));
				$this->alias = str_replace('-', ' ', $this->alias);
				$this->alias = str_replace('/', '-', $this->alias);
				$this->alias = mb_ereg_replace('[[:space:]]+', ' ', $this->alias);
				$this->alias = trim(str_replace(' ', '-', $this->alias));
				$this->alias = str_replace('.', '', $this->alias);
				$this->alias = str_replace('"', '', $this->alias);
				$this->alias = str_replace("'", '', $this->alias);
				$stripthese = ',|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°|«|»|…';
				$strips = explode('|', $stripthese);
				foreach ($strips as $strip)
				{
					$this->alias = str_replace($strip, '', $this->alias);
				}
				if (trim(str_replace('-', '', $this->alias)) == '')
				{
					$datenow = JFactory::getDate();
					$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
				}
				$this->alias = trim($this->alias, '-.');
			}
		}
		else
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
			}
			// Transliterate properly...
			else
			{
				// Detect the site language we will transliterate
				if ($this->language == '*')
				{
					$langParams = JComponentHelper::getParams('com_languages');
					$languageTag = $langParams->get('site');
				}
				else
				{
					$languageTag = $this->language;
				}
				$language = JLanguage::getInstance($languageTag);
				$this->alias = $language->transliterate($this->alias);
				$this->alias = JFilterOutput::stringURLSafe($this->alias);
				if (trim(str_replace('-', '', $this->alias)) == '')
				{
					$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
				}
			}
		}

		if (K2_JVERSION == '15' || $params->get('enforceSEFReplacements'))
		{

			$SEFReplacements = array();
			$items = explode(',', $params->get('SEFReplacements'));
			foreach ($items as $item)
			{
				if (!empty($item))
				{
					@list($src, $dst) = explode('|', trim($item));
					$SEFReplacements[trim($src)] = trim($dst);
				}
			}

			foreach ($SEFReplacements as $key => $value)
			{
				$this->alias = str_replace($key, $value, $this->alias);
			}

			$this->alias = trim($this->alias, '-.');
		}

		if (K2_JVERSION == '15')
		{
			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$datenow = JFactory::getDate();
				$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
			}
		}

		// Check if alias already exists. If so warn the user
		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('k2Sef') && !$params->get('k2SefInsertCatId'))
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT id FROM #__k2_categories WHERE alias = ".$db->quote($this->alias)." AND id != ".(int)$this->id);
			$result = count($db->loadObjectList());
			if ($result > 0)
			{
				$this->alias .= '-'.((int)$result + 1);
				$application = JFactory::getApplication();
				$application->enqueueMessage(JText::_('K2_WARNING_DUPLICATE_TITLE_ALIAS_DETECTED'), 'notice');
			}
		}

		return true;

	}

	function bind($array, $ignore = '')
	{

		if (key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		if (key_exists('plugins', $array) && is_array($array['plugins']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['plugins']);
			$array['plugins'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

}
