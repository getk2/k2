<?php
/**
 * @version     2.6.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge'))
{
    class K2View extends JViewLegacy
    {
    }

}
else
{
    class K2View extends JView
    {
    }

}
