<?php
/**
 * @version     $Id: model.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '3.0', 'ge'))
{
    class K2Model extends JModelLegacy
    {
        public static function addIncludePath($path = '', $prefix = '')
        {
            return parent::addIncludePath($path, $prefix);
        }

    }

}
else if (version_compare(JVERSION, '2.5', 'ge'))
{
    class K2Model extends JModel
    {
        public static function addIncludePath($path = '', $prefix = '')
        {
            return parent::addIncludePath($path, $prefix);
        }

    }

}
else
{
    class K2Model extends JModel
    {
        public function addIncludePath($path = '', $prefix = '')
        {
            return parent::addIncludePath($path);
        }

    }

}
