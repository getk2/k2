<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2020 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

class K2ModelSettings extends K2Model
{
    public function save()
    {
        $app = JFactory::getApplication();
        $component = JTable::getInstance('component');
        $component->loadByOption('com_k2');
        $post = JRequest::get('post');
        $component->bind($post);
        if (!$component->check()) {
            $app->enqueueMessage($component->getError(), 'error');
            return false;
        }
        if (!$component->store()) {
            $app->enqueueMessage($component->getError(), 'error');
            return false;
        }
        return true;
    }

    public function &getParams()
    {
        static $instance;
        if ($instance == null) {
            $component = JTable::getInstance('component');
            $component->loadByOption('com_k2');
            $instance = new JParameter($component->params, JPATH_ADMINISTRATOR.'/components/com_k2/config.xml');
        }
        return $instance;
    }
}
