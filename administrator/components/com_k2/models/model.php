<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use K2\Traits\HasEvents;

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '2.5', 'ge')) {
    class K2Model extends JModelLegacy
    {
        use HasEvents;

        public static function addIncludePath($path = '', $prefix = 'K2Model')
        {
            return parent::addIncludePath($path, $prefix);
        }
    }
} else {
    class K2Model extends JModel
    {
        use HasEvents;

        public function addIncludePath($path = '', $prefix = 'K2Model')
        {
            return parent::addIncludePath($path);
        }
    }
}
