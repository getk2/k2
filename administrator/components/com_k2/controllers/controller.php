<?php
/**
 * @version     2.9.x
 * @package     K2
 * @author      JoomlaWorks https://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class K2Controller extends JControllerLegacy
    {
        public function display($cachable = false, $urlparams = array())
        {
            parent::display($cachable, $urlparams);
        }
    }
} elseif (version_compare(JVERSION, '2.5', 'ge')) {
    class K2Controller extends JController
    {
        public function display($cachable = false, $urlparams = false)
        {
            parent::display($cachable, $urlparams);
        }
    }
} else {
    class K2Controller extends JController
    {
        public function display($cachable = false)
        {
            parent::display($cachable);
        }
    }
}
