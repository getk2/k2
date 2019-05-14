<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class K2View extends JViewLegacy
    {
    }
} else {
    class K2View extends JView
    {
    }
}
