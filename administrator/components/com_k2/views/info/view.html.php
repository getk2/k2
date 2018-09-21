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

jimport('joomla.application.component.view');

class K2ViewInfo extends K2View
{

    function display($tpl = null)
    {
        jimport('joomla.filesystem.file');
        $user = JFactory::getUser();
        $db = JFactory::getDbo();
        $db_version = $db->getVersion();
        $php_version = phpversion();
        $server = $this->get_server_software();
        $gd_check = extension_loaded('gd');
        $mb_check = extension_loaded('mbstring');

        $media_folder_check = is_writable(JPATH_ROOT.'/media/k2');
        $attachments_folder_check = is_writable(JPATH_ROOT.'/media/k2/attachments');
        $categories_folder_check = is_writable(JPATH_ROOT.'/media/k2/categories');
        $galleries_folder_check = is_writable(JPATH_ROOT.'/media/k2/galleries');
        $items_folder_check = is_writable(JPATH_ROOT.'/media/k2/items');
        $users_folder_check = is_writable(JPATH_ROOT.'/media/k2/users');
        $videos_folder_check = is_writable(JPATH_ROOT.'/media/k2/videos');
        $cache_folder_check = is_writable(JPATH_ROOT.'/cache');

        $this->assignRef('server', $server);
        $this->assignRef('php_version', $php_version);
        $this->assignRef('db_version', $db_version);
        $this->assignRef('gd_check', $gd_check);
        $this->assignRef('mb_check', $mb_check);

        $this->assignRef('media_folder_check', $media_folder_check);
        $this->assignRef('attachments_folder_check', $attachments_folder_check);
        $this->assignRef('categories_folder_check', $categories_folder_check);
        $this->assignRef('galleries_folder_check', $galleries_folder_check);
        $this->assignRef('items_folder_check', $items_folder_check);
        $this->assignRef('users_folder_check', $users_folder_check);
        $this->assignRef('videos_folder_check', $videos_folder_check);
        $this->assignRef('cache_folder_check', $cache_folder_check);

        JToolBarHelper::title(JText::_('K2_INFORMATION'), 'k2.png');

        if (K2_JVERSION != '15')
        {
            JToolBarHelper::preferences('com_k2', 580, 800, 'K2_PARAMETERS');
        }
        else
        {
            $toolbar = JToolBar::getInstance('toolbar');
            $toolbar->appendButton('Popup', 'config', 'K2_PARAMETERS', 'index.php?option=com_k2&view=settings', 800, 580);
        }

        $this->loadHelper('html');
        K2HelperHTML::subMenu();

        parent::display($tpl);
    }

    function get_server_software()
    {
        if (isset($_SERVER['SERVER_SOFTWARE']))
        {
            return $_SERVER['SERVER_SOFTWARE'];
        }
        else if (($sf = getenv('SERVER_SOFTWARE')))
        {
            return $sf;
        }
        else
        {
            return JText::_('K2_NA');
        }
    }

}
