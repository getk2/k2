<?php
/**
 * @version     $Id: table.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

class K2Table extends JTable
{
    public function load($keys = null, $reset = true)
    {
        if (K2_JVERSION == '15')
        {
            return parent::load($keys);
        }
        else
        {
            return parent::load($keys, $reset);
        }
    }

}
