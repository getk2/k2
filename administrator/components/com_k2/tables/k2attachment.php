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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2Attachment extends K2Table
{

    var $id = null;
    var $itemID = null;
    var $filename = null;
    var $title = null;
    var $titleAttribute = null;
    var $hits = null;

    function __construct(&$db)
    {
        parent::__construct('#__k2_attachments', 'id', $db);
    }

}
