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

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class K2View extends JViewLegacy
    {
        public function display($tpl = null)
        {
            // Allow for YOOtheme PRO Integration
            $app = \Joomla\CMS\Factory::getApplication();

            // Only in YOOtheme PRO
            if ($app->isClient('site') && stripos($app->getTemplate(), 'yootheme') === 0) {
                // Trigger the custom YOOtheme Pro event
                $app->triggerEvent('onLoadTemplate', [$this, $tpl]);

                // If the event overrode the output, print that output and don't display anything else
                if ($this->_output) {
                    echo $this->_output;
                    return;
                }
            }

            return parent::display($tpl);
        }
    }
} else {
    class K2View extends JView
    {
    }
}
