<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2ExtraField extends K2Table
{

    var $id = null;
    var $name = null;
    var $value = null;
    var $type = null;
    var $group = null;
    var $published = null;
    var $ordering = null;

    function __construct(&$db)
    {
        parent::__construct('#__k2_extra_fields', 'id', $db);
    }
	
    function check()
    {
    	$this->name = JString::trim($this->name);
        if ($this->name == '')
        {
            $this->setError(JText::_('K2_NAME_CANNOT_BE_EMPTY'));
            return false;
        }
        return true;
    }
}
