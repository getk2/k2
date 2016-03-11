<?php
/**
 * @version     2.7.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '2.5', 'ge'))
{
    class K2Model extends JModelLegacy
    {
        public static function addIncludePath($path = '', $prefix = 'K2Model')
        {
            return parent::addIncludePath($path, $prefix);
        }

    }

}
else
{
    class K2Model extends JModel
    {
        public function addIncludePath($path = '', $prefix = 'K2Model')
        {
            return parent::addIncludePath($path);
        }

    }

}
