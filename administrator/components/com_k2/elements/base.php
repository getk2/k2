<?php
/**
 * @version     2.8.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

K2HelperHTML::loadHeadIncludes(true, true, false, true);

if (K2_JVERSION == '15')
{
    jimport('joomla.html.parameter.element');
    class K2Element extends JElement
    {
    }
}
else
{
	jimport('joomla.form.formfield');
	if(version_compare(JVERSION, '3.5.0', 'ge'))
	{
		class K2Element extends JFormField
		{
		    function getInput()
		    {
			    if (method_exists($this,'fetchElement')) // BC
			    {
				   return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
			    }
		        return $this->fetchElementValue($this->name, $this->value, $this->element, $this->options['control']);
		    }
		    function getLabel()
		    {
		        if (method_exists($this, 'fetchTooltip')) // BC
		        {
		        	return $this->fetchTooltip($this->element['label'], $this->description, $this->element, $this->options['control'], $this->element['name'] = '');
		        }
		        if (method_exists($this, 'fetchElementName'))
		        {
		        	return $this->fetchElementName($this->element['label'], $this->description, $this->element, $this->options['control'], $this->element['name'] = '');
		        }
		        return parent::getLabel();
		    }
		    function render($layoutId, $data = array())
		    {
		        return $this->getInput();
		    }
		}
	}
	else
	{
		class K2Element extends JFormField
		{
		    function getInput()
		    {
		        if (method_exists($this, 'fetchElement')) // BC
		        {
			        return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
			    }
		        return $this->fetchElementValue($this->name, $this->value, $this->element, $this->options['control']);
		    }
		    function getLabel()
		    {
		        if (method_exists($this, 'fetchTooltip')) // BC
		        {
			        return $this->fetchTooltip($this->element['label'], $this->description, $this->element, $this->options['control'], $this->element['name'] = '');
			    }
		        if (method_exists($this, 'fetchElementName'))
		        {
		            return $this->fetchElementName($this->element['label'], $this->description, $this->element, $this->options['control'], $this->element['name'] = '');
		        }
		        return parent::getLabel();
		    }
		    function render()
		    {
		        return $this->getInput();
		    }
		}
	}
}
