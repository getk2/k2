<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.model');

class K2ModelSettings extends K2Model
{

    function save()
    {
        $mainframe = JFactory::getApplication();
        $component = JTable::getInstance('component');
        $component->loadByOption('com_k2');
        $post = JRequest::get('post');
        $component->bind($post);
        if (!$component->check())
        {
            $mainframe->enqueueMessage($component->getError(), 'error');
            return false;
        }
        if (!$component->store())
        {
            $mainframe->enqueueMessage($component->getError(), 'error');
            return false;
        }
        return true;
    }

    function & getParams()
    {
        static $instance;
        if ($instance == null)
        {
            $component = JTable::getInstance('component');
            $component->loadByOption('com_k2');
            $instance = new JParameter($component->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'config.xml');
        }
        return $instance;
    }

}
