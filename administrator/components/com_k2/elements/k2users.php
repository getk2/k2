<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2Users extends K2Element
{

	function fetchElement($name, $value, &$node, $control_name)
	{

		$fieldName = (K2_JVERSION != '15') ? $name.'[]' : $control_name.'['.$name.'][]';

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/select2.min.css?v=2.7.0');
		$document->addScript(JURI::root(true).'/media/k2/assets/js/select2.min.js?v=2.7.0');
		$document->addScriptDeclaration('
		$K2(document).ready(function() {
			if(typeof($K2(".k2UsersElement").chosen) == "function") {
				$K2(".k2UsersElement").chosen("destroy");
			}
			$K2(".k2UsersElement").select2({
				width : "300px",
				minimumInputLength : 2,
				ajax: {
					dataType : "json",
					url: "'.JURI::root(true).'/administrator/index.php?option=com_k2&view=users&task=search&format=raw",
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
			$db = JFactory::getDBO();
			$query = "SELECT id AS value, name AS text FROM #__users WHERE id IN(".implode(',', $value).")";
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return JHTML::_('select.genericlist', $options, $fieldName, 'class="k2UsersElement" multiple="multiple" size="15"', 'value', 'text', $value);

	}

}

class JFormFieldK2Users extends K2ElementK2Users
{
	var $type = 'k2users';
}

class JElementK2Users extends K2ElementK2Users
{
	var $_name = 'k2users';
}
