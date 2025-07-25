<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
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
