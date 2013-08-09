<?php
/**
 * @version     $Id: base.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

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
    class K2Element extends JFormField
    {

        function getInput()
        {
            return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
        }

        function getLabel()
        {
            if (method_exists($this, 'fetchTooltip'))
            {
                return $this->fetchTooltip($this->element['label'], $this->description, $this->element, $this->options['control'], $this->element['name'] = '');
            }
            else
            {
                return parent::getLabel();
            }

        }

        function render()
        {
            return $this->getInput();
        }

    }

}
