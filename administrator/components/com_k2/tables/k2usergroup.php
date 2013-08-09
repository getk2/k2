<?php
/**
 * @version		$Id: k2usergroup.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

class TableK2UserGroup extends K2Table
{

    var $id = null;
    var $name = null;
    var $permissions = null;

    function __construct(&$db)
    {

        parent::__construct('#__k2_user_groups', 'id', $db);
    }

    function check()
    {
		$this->name = JString::trim($this->name);
        if ($this->name == '')
        {
            $this->setError(JText::_('K2_GROUP_CANNOT_BE_EMPTY'));
            return false;
        }
        return true;
    }

    function bind($array, $ignore = '')
    {

        if (key_exists('params', $array) && is_array($array['params']))
        {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            if (JRequest::getVar('categories') == 'all' || JRequest::getVar('categories') == 'none')
                $registry->set('categories', JRequest::getVar('categories'));
            $array['permissions'] = $registry->toString();
        }
        return parent::bind($array, $ignore);
    }

}
