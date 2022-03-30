<?php
/**
 * @version    2.11.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class K2View extends JViewLegacy
    {
        public function display($tpl = null)
        {
            // Allow for YOOtheme PRO Integration
            $app = \Joomla\CMS\Factory::getApplication();
            if ($app->isClient('site') && stripos($app->getTemplate(), 'yootheme') === 0) {
                $app->triggerEvent('onLoadTemplate', [$this, $tpl]);
            }

            return parent::display($tpl);
        }
    }
} else {
    class K2View extends JView
    {
    }
}
