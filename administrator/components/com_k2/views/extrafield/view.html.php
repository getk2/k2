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

jimport('joomla.application.component.view');

class K2ViewExtraField extends K2View
{
	function display($tpl = null)
	{
		JHTML::_('behavior.modal');
		JHTML::_('behavior.keepalive');

		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
			var K2BasePath = "'.JURI::base(true).'/";
			var K2Language = [
				"'.JText::_('K2_REMOVE', true).'",
				"'.JText::_('K2_OPTIONAL', true).'",
				"'.JText::_('K2_COMMA_SEPARATED_VALUES', true).'",
				"'.JText::_('K2_USE_EDITOR', true).'",
				"'.JText::_('K2_ALL_SETTINGS_ABOVE_ARE_OPTIONAL', true).'",
				"'.JText::_('K2_ADD_AN_OPTION', true).'",
				"'.JText::_('K2_LINK_TEXT', true).'",
				"'.JText::_('K2_URL', true).'",
				"'.JText::_('K2_OPEN_IN', true).'",
				"'.JText::_('K2_SAME_WINDOW', true).'",
				"'.JText::_('K2_NEW_WINDOW', true).'",
				"'.JText::_('K2_CLASSIC_JAVASCRIPT_POPUP', true).'",
				"'.JText::_('K2_LIGHTBOX_POPUP', true).'",
				"'.JText::_('K2_RESET_VALUE', true).'",
				"'.JText::_('K2_CALENDAR', true).'",
				"'.JText::_('K2_PLEASE_SELECT_A_FIELD_TYPE_FROM_THE_LIST_ABOVE', true).'",
				"'.JText::_('K2_COLUMNS', true).'",
				"'.JText::_('K2_ROWS', true).'",
			];
		');

		$model = $this->getModel();
		$extraField = $model->getData();
		if (!$extraField->id)
		{
			$extraField->published = 1;
			$extraField->alias = '';
			$extraField->required = 1;
			$extraField->showNull = 0;
			$extraField->displayInFrontEnd = 1;
		}
		else
		{
			$values = json_decode($extraField->value);
			if (isset($values[0]->alias) && !empty($values[0]->alias))
			{
				$extraField->alias = $values[0]->alias;
			}
			else
			{
				$extraField->alias = $extraField->name;
			}
			$searches = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ç', 'ç', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ð', 'ð', 'Ď', 'ď', 'Đ', 'đ', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ñ', 'ñ', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'ſ', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ý', 'ý', 'ÿ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'ά', 'έ', 'ή', 'ί', 'ό', 'ύ', 'ώ', 'Ά', 'Έ', 'Ή', 'Ί', 'Ό', 'Ύ', 'Ώ', 'ϊ', 'ΐ', 'ϋ', 'ς', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'А', 'Ӑ', 'Ӓ', 'Ә', 'Ӛ', 'Ӕ', 'Б', 'В', 'Г', 'Ґ', 'Ѓ', 'Ғ', 'Ӷ', 'Д', 'Е', 'Ѐ', 'Ё', 'Ӗ', 'Ҽ', 'Ҿ', 'Є', 'Ж', 'Ӂ', 'Җ', 'Ӝ', 'З', 'Ҙ', 'Ӟ', 'Ӡ', 'Ѕ', 'И', 'Ѝ', 'Ӥ', 'Ӣ', 'І', 'Ї', 'Ӏ', 'Й', 'Ҋ', 'Ј', 'К', 'Қ', 'Ҟ', 'Ҡ', 'Ӄ', 'Ҝ', 'Л', 'Ӆ', 'Љ', 'М', 'Ӎ', 'Н', 'Ӊ', 'Ң', 'Ӈ', 'Ҥ', 'Њ', 'О', 'Ӧ', 'Ө', 'Ӫ', 'Ҩ', 'П', 'Ҧ', 'Р', 'Ҏ', 'С', 'Ҫ', 'Т', 'Ҭ', 'Ћ', 'Ќ', 'У', 'Ў', 'Ӳ', 'Ӱ', 'Ӯ', 'Ү', 'Ұ', 'Ф', 'Х', 'Ҳ', 'Һ', 'Ц', 'Ҵ', 'Ч', 'Ӵ', 'Ҷ', 'Ӌ', 'Ҹ', 'Џ', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ӹ', 'Ь', 'Ҍ', 'Э', 'Ӭ', 'Ю', 'Я', 'а', 'ӑ', 'ӓ', 'ә', 'ӛ', 'ӕ', 'б', 'в', 'г', 'ґ', 'ѓ', 'ғ', 'ӷ', 'y', 'д', 'е', 'ѐ', 'ё', 'ӗ', 'ҽ', 'ҿ', 'є', 'ж', 'ӂ', 'җ', 'ӝ', 'з', 'ҙ', 'ӟ', 'ӡ', 'ѕ', 'и', 'ѝ', 'ӥ', 'ӣ', 'і', 'ї', 'Ӏ', 'й', 'ҋ', 'ј', 'к', 'қ', 'ҟ', 'ҡ', 'ӄ', 'ҝ', 'л', 'ӆ', 'љ', 'м', 'ӎ', 'н', 'ӊ', 'ң', 'ӈ', 'ҥ', 'њ', 'о', 'ӧ', 'ө', 'ӫ', 'ҩ', 'п', 'ҧ', 'р', 'ҏ', 'с', 'ҫ', 'т', 'ҭ', 'ћ', 'ќ', 'у', 'ў', 'ӳ', 'ӱ', 'ӯ', 'ү', 'ұ', 'ф', 'х', 'ҳ', 'һ', 'ц', 'ҵ', 'ч', 'ӵ', 'ҷ', 'ӌ', 'ҹ', 'џ', 'ш', 'щ', 'ъ', 'ы', 'ӹ', 'ь', 'ҍ', 'э', 'ӭ', 'ю', 'я');
			$replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'D', 'd', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'J', 'j', 'K', 'k', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'N', 'n', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'y', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 'a', 'b', 'g', 'd', 'e', 'z', 'h', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p', 'r', 's', 't', 'y', 'f', 'ch', 'ps', 'w', 'A', 'B', 'G', 'D', 'E', 'Z', 'H', 'Th', 'I', 'K', 'L', 'M', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'Ch', 'Ps', 'W', 'a', 'e', 'h', 'i', 'o', 'y', 'w', 'A', 'E', 'H', 'I', 'O', 'Y', 'W', 'i', 'i', 'y', 's', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Zero', 'A', 'A', 'A', 'E', 'E', 'E', 'B', 'V', 'G', 'G', 'G', 'G', 'G', 'D', 'E', 'E', 'YO', 'E', 'E', 'E', 'YE', 'ZH', 'DZH', 'ZH', 'DZH', 'Z', 'Z', 'DZ', 'DZ', 'DZ', 'I', 'I', 'I', 'I', 'I', 'JI', 'I', 'Y', 'Y', 'J', 'K', 'Q', 'Q', 'K', 'Q', 'K', 'L', 'L', 'L', 'M', 'M', 'N', 'N', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'P', 'PF', 'P', 'P', 'S', 'S', 'T', 'TH', 'T', 'K', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'F', 'H', 'H', 'H', 'TS', 'TS', 'CH', 'CH', 'CH', 'CH', 'CH', 'DZ', 'SH', 'SHT', 'A', 'Y', 'Y', 'Y', 'Y', 'E', 'E', 'YU', 'YA', 'a', 'a', 'a', 'e', 'e', 'e', 'b', 'v', 'g', 'g', 'g', 'g', 'g', 'y', 'd', 'e', 'e', 'yo', 'e', 'e', 'e', 'ye', 'zh', 'dzh', 'zh', 'dzh', 'z', 'z', 'dz', 'dz', 'dz', 'i', 'i', 'i', 'i', 'i', 'ji', 'i', 'y', 'y', 'j', 'k', 'q', 'q', 'k', 'q', 'k', 'l', 'l', 'l', 'm', 'm', 'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'p', 'pf', 'p', 'p', 's', 's', 't', 'th', 't', 'k', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'f', 'h', 'h', 'h', 'ts', 'ts', 'ch', 'ch', 'ch', 'ch', 'ch', 'dz', 'sh', 'sht', 'a', 'y', 'y', 'y', 'y', 'e', 'e', 'yu', 'ya');
			$extraField->alias = str_replace($searches, $replacements, $extraField->alias);
			$filter = JFilterInput::getInstance();
			$extraField->alias = $filter->clean($extraField->alias, 'WORD');

			if (isset($values[0]->required))
			{
				$extraField->required = $values[0]->required;
			}
			else
			{
				$extraField->required = 0;
			}
			if (isset($values[0]->showNull))
			{
				$extraField->showNull = $values[0]->showNull;
			}
			else
			{
				$extraField->showNull = 0;
			}
			if (isset($values[0]->displayInFrontEnd))
			{
				$extraField->displayInFrontEnd = $values[0]->displayInFrontEnd;
			}
			else
			{
				$extraField->displayInFrontEnd = 0;
			}
		}
		$extraField->name = htmlspecialchars($extraField->name, ENT_QUOTES, 'UTF-8');
		$this->assignRef('row', $extraField);

		$lists = array();
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $extraField->published);

		$groups[] = JHTML::_('select.option', 0, JText::_('K2_CREATE_NEW_GROUP'));

		$extraFieldModel = K2Model::getInstance('ExtraFields', 'K2Model');
		$uniqueGroups = $extraFieldModel->getGroups(true);
		foreach ($uniqueGroups as $group)
		{
			$groups[] = JHTML::_('select.option', $group->id, $group->name);
		}

		$lists['group'] = JHTML::_('select.genericlist', $groups, 'groups', '', 'value', 'text', $extraField->group);

		$typeOptions[] = JHTML::_('select.option', 0, JText::_('K2_SELECT_TYPE'));
		$typeOptions[] = JHTML::_('select.option', 'textfield', JText::_('K2_TEXT_FIELD'));
		$typeOptions[] = JHTML::_('select.option', 'textarea', JText::_('K2_TEXTAREA'));
		$typeOptions[] = JHTML::_('select.option', 'select', JText::_('K2_DROPDOWN_SELECTION'));
		$typeOptions[] = JHTML::_('select.option', 'multipleSelect', JText::_('K2_MULTISELECT_LIST'));
		$typeOptions[] = JHTML::_('select.option', 'radio', JText::_('K2_RADIO_BUTTONS'));
		$typeOptions[] = JHTML::_('select.option', 'link', JText::_('K2_LINK'));
		$typeOptions[] = JHTML::_('select.option', 'csv', JText::_('K2_CSV_DATA'));
		$typeOptions[] = JHTML::_('select.option', 'labels', JText::_('K2_SEARCHABLE_LABELS'));
		$typeOptions[] = JHTML::_('select.option', 'date', JText::_('K2_DATE'));
		$typeOptions[] = JHTML::_('select.option', 'image', JText::_('K2_IMAGE'));
		$typeOptions[] = JHTML::_('select.option', 'header', JText::_('K2_HEADER'));
		$lists['type'] = JHTML::_('select.genericlist', $typeOptions, 'type', '', 'value', 'text', $extraField->type);

		$this->assignRef('lists', $lists);

		// Disable Joomla menu
		JRequest::setVar('hidemainmenu', 1);

		// Toolbar
		$title = (JRequest::getInt('cid')) ? JText::_('K2_EDIT_EXTRA_FIELD') : JText::_('K2_ADD_EXTRA_FIELD');
		JToolBarHelper::title($title, 'k2.png');

		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::cancel();

		parent::display($tpl);
	}
}
