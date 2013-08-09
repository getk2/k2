<?php
/**
 * @version		$Id: itemform.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementItemForm extends K2Element
{
    function fetchElement($name, $value, &$node, $control_name)
    {
        $document = JFactory::getDocument();
        $document->addScriptDeclaration("
			window.addEvent('domready', function() {
				if($('request-options')) {
					$$('.panel')[0].setStyle('display', 'none');
				}
				if($('jform_browserNav')) {
					$('jform_browserNav').setProperty('value', 2);
					$('jform_browserNav').getElements('option')[0].destroy();
				}
				if($('browserNav')) {
					$('browserNav').setProperty('value', 2);
					options = $('browserNav').getElements('option');
					if(options.length == 3) {
						options[0].remove();
					}
				}				
			});
		");
        return '';
    }

    function fetchTooltip($label, $description, &$node, $control_name, $name)
    {
        return '';
    }

}

class JFormFielditemform extends K2ElementItemForm
{
    var $type = 'itemform';
}

class JElementitemform extends K2ElementItemForm
{
    var $_name = 'itemform';
}
