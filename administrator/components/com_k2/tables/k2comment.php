<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2Comment extends K2Table
{

    var $id = null;
    var $itemID = null;
    var $userID = null;
    var $userName = null;
    var $commentDate = null;
    var $commentText = null;
    var $commentEmail = null;
    var $commentURL = null;
    var $published = null;

    function __construct(&$db)
    {
        parent::__construct('#__k2_comments', 'id', $db);
    }
    function check()
    {
		$this->commentText = JString::trim($this->commentText);
    }

}
