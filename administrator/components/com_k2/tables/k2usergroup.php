<?php
/**
 * @version		2.7.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2UserGroup extends K2Table
{

    var $id = null;
    var $name = null;
    var $permissions = null;

    function __construct(&$db)
    {

        parent::__construct('#__k2_user_groups', 'id', $db);
    }

  function load($oid = null, $reset = false)
	{
		static $K2UserGroupsInstances = array();
		if (isset($K2UserGroupsInstances[$oid]))
		{
			return $this->bind($K2UserGroupsInstances[$oid]);
		}
		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

		$oid = $this->$k;

		if ($oid === null)
		{
			return false;
		}
		$this->reset();

		$db = $this->getDBO();

		$query = 'SELECT *' . ' FROM ' . $this->_tbl . ' WHERE ' . $this->_tbl_key . ' = ' . $db->Quote($oid);
		$db->setQuery($query);
		$result = $db->loadAssoc();
		if ($result)
		{
			$K2UserGroupsInstances[$oid] = $result;
			return $this->bind($K2UserGroupsInstances[$oid]);
		}
		else
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
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
