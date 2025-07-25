<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2Users extends K2Element
{
    public function fetchElement($name, $value, &$node, $control_name)
    {
        $fieldName = (K2_JVERSION != '15') ? $name.'[]' : $control_name.'['.$name.'][]';

        $document = JFactory::getDocument();
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js');
        $document->addScriptDeclaration('
			$K2(document).ready(function() {
				if (typeof($K2(".k2UsersElement").chosen) == "function") {
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
        if (is_array($value) && count($value)) {
            $db = JFactory::getDbo();
            $query = "SELECT id AS value, name AS text FROM #__users WHERE id IN(".implode(',', $value).")";
            $db->setQuery($query);
            $options = $db->loadObjectList();
        }

        return JHTML::_('select.genericlist', $options, $fieldName, 'class="k2UsersElement" multiple="multiple" size="15"', 'value', 'text', $value);
    }
}

class JFormFieldK2Users extends K2ElementK2Users
{
    public $type = 'k2users';
}

class JElementK2Users extends K2ElementK2Users
{
    public $_name = 'k2users';
}
