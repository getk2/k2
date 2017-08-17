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

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2Tags extends K2Element
{
	function fetchElementValue($name, $value, &$node, $control_name)
	{
		$fieldName = (K2_JVERSION != '15') ? $name.'[]' : $control_name.'['.$name.'][]';

		$document = JFactory::getDocument();
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
		$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js');
		$document->addScriptDeclaration('
			$K2(document).ready(function() {
				if(typeof($K2(".k2TagsElement").chosen) == "function") {
					$K2(".k2TagsElement").chosen("destroy");
				}
				$K2(".k2TagsElement").select2({
					width : "300px",
					minimumInputLength : 2,
					ajax: {
						dataType : "json",
						url: "'.JURI::root(true).'/administrator/index.php?option=com_k2&view=item&task=tags&id=1",
						cache: "true",
						 data: function (params) {
						 	var queryParameters = {q: params.term};
						 	return queryParameters;
						 },
						 processResults: function (data) {
						 	var results = [];
						 	jQuery.each(data, function(index, value) {
						 		var row = {
						 			id : value.id,
						 			text : value.name
						 		};
								results.push(row);
						 	});
						 	return {results: results};
						 }

					}
				});
			});
		');

		$options = array();
		if(is_array($value) && count($value))
		{
			$db = JFactory::getDbo();
			$query = "SELECT id AS value, name AS text FROM #__k2_tags WHERE id IN(".implode(',', $value).")";
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return JHTML::_('select.genericlist', $options, $fieldName, 'class="k2TagsElement" multiple="multiple" size="15"', 'value', 'text', $value);
	}
}

class JFormFieldK2Tags extends K2ElementK2Tags
{
	var $type = 'k2tags';
}

class JElementK2Tags extends K2ElementK2Tags
{
	var $_name = 'k2tags';
}
