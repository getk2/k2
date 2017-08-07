<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2Tag extends K2Table
{
	var $id = null;
	var $name = null;
	var $published = null;

	function __construct(&$db)
	{
		parent::__construct('#__k2_tags', 'id', $db);
	}

	function check()
	{
		$this->name = JString::trim($this->name);
		$this->name = JString::str_ireplace('-', '', $this->name);
		$this->name = JString::str_ireplace('.', '', $this->name);

		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('k2TagNorm'))
		{
			$searches = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ç', 'ç', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ð', 'ð', 'Ď', 'ď', 'Đ', 'đ', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ñ', 'ñ', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'ſ', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ý', 'ý', 'ÿ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'Ά', 'ά', 'Έ', 'έ', 'Ή', 'ή', 'Ί', 'ί', 'Ό', 'ό', 'Ύ', 'ύ', 'Ώ', 'ώ', 'ϋ', 'ϊ', 'ΐ');
			$replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'D', 'd', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'J', 'j', 'K', 'k', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'N', 'n', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'y', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 'Α', 'α', 'Ε', 'ε', 'Η', 'η', 'Ι', 'ι', 'Ο', 'ο', 'Υ', 'υ', 'Ω', 'ω', 'υ', 'ι', 'ι');
			$additionalReplacements = $params->get('k2TagNormAdditionalReplacements');
			$pairs = @explode(',', $additionalReplacements);
			if(is_array($pairs))
			{
				foreach ($pairs as $pair) {
					@list($search, $replace) = @explode('|', $pair);
					if(isset($search) && $search && isset($replace) && $replace)
					{
						$searches[] = $search;
						$replacements[] = $replace;
					}
				}
			}

			//$this->name = JString::str_ireplace($searches, $replacements, $this->name); // This causes character stripping in J!1.5!!
			$this->name = str_ireplace($searches, $replacements, $this->name);

			// Switch case
			if ($params->get('k2TagNormCase') == 'upper')
			{
				$this->name = JString::strtoupper($this->name);
			}
			else
			{
				$this->name = JString::strtolower($this->name);

				// Special case for Greek letter s final
				$this->name = JString::str_ireplace('σ ', 'ς ', $this->name);
				if(JString::substr($this->name, -1) == 'σ')
				{
					$this->name = JString::substr($this->name, 0, -1);
					$this->name .= 'ς';
				}
			}
		}

		$this->name = JString::trim($this->name);
		if ($this->name == '')
		{
			$this->setError(JText::_('K2_TAG_CANNOT_BE_EMPTY'));
			return false;
		}

		if (strlen(utf8_decode($this->name)) < 2)
		{
			$this->setError(JText::_('K2_TAG_CANNOT_BE_A_SINGLE_CHARACTER'));
			return false;
		}

		// Check if a tag exists already before adding a new one
		if (!$this->id)
		{
			$this->_db->setQuery("SELECT id FROM #__k2_tags WHERE name = ".$this->_db->Quote($this->name));
			if ($this->_db->loadResult())
			{
				$this->setError(JText::_('K2_THIS_TAG_EXISTS_ALREADY'));
				return false;
			}
		}

		return true;
	}
}
