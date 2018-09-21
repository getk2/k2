<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class TableK2User extends K2Table
{
    public $id = null;
    public $userID = null;
    public $userName = null;
    public $gender = null;
    public $description = null;
    public $image = null;
    public $url = null;
    public $group = null;
    public $plugins = null;
    public $ip = null;
    public $hostname = null;
    public $notes = null;

    public function __construct(&$db)
    {
        parent::__construct('#__k2_users', 'id', $db);
    }

    public function check()
    {
        if (trim($this->url) != '' && substr($this->url, 0, 4) != 'http') {
            $this->url = 'http://'.$this->url;
        }
        return true;
    }

    public function bind($array, $ignore = '')
    {
        if (key_exists('plugins', $array) && is_array($array['plugins'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['plugins']);
            $array['plugins'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }
}
