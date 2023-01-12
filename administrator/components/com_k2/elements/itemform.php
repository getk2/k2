<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementItemForm extends K2Element
{
    public function fetchElement($name, $value, &$node, $control_name)
    {
        if (version_compare(JVERSION, '3.5', 'ge')) {
            JHtml::_('behavior.framework');
        }
        $document = JFactory::getDocument();
        $document->addScriptDeclaration("
        	/* Mootools Snippet */
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

    public function fetchTooltip($label, $description, &$node, $control_name, $name)
    {
        return '';
    }
}

class JFormFielditemform extends K2ElementItemForm
{
    public $type = 'itemform';
}

class JElementitemform extends K2ElementItemForm
{
    public $_name = 'itemform';
}
