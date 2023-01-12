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

jimport('joomla.application.component.view');

class K2ViewUser extends K2View
{
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $user = $model->getData();
        if (K2_JVERSION == '15') {
            JFilterOutput::objectHTMLSafe($user);
        } else {
            JFilterOutput::objectHTMLSafe($user, ENT_QUOTES, array('params', 'plugins'));
        }
        $joomlaUser = JUser::getInstance(JRequest::getInt('cid'));

        $user->name = $joomlaUser->name;
        $user->userID = $joomlaUser->id;
        $this->assignRef('row', $user);

        $wysiwyg = JFactory::getEditor();
        $editor = $wysiwyg->display('description', $user->description, '480px', '250px', '', '', false);
        $this->assignRef('editor', $editor);

        $lists = array();
        $genderOptions[] = JHTML::_('select.option', 'n', JText::_('K2_NOT_SPECIFIED'));
        $genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
        $genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
        $lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'gender', '', 'value', 'text', $user->gender);

        $userGroupOptions = $model->getUserGroups();
        $lists['userGroup'] = JHTML::_('select.genericlist', $userGroupOptions, 'group', 'class="inputbox"', 'id', 'name', $user->group);

        $this->assignRef('lists', $lists);

        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        // Plugins
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();
        $K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(&$user, 'user'));
        $this->assignRef('K2Plugins', $K2Plugins);

        // Disable Joomla menu
        JRequest::setVar('hidemainmenu', 1);

        // Toolbar
        $toolbar = JToolBar::getInstance('toolbar');
        JToolBarHelper::title(JText::_('K2_USER'), 'k2.png');

        JToolBarHelper::apply();
        JToolBarHelper::save();
        JToolBarHelper::cancel();

        if (K2_JVERSION != '15') {
            $editJoomlaUserButtonUrl = JURI::base().'index.php?option=com_users&view=user&task=user.edit&id='.$user->userID;
        } else {
            $editJoomlaUserButtonUrl = JURI::base().'index.php?option=com_users&view=user&task=edit&cid[]='.$user->userID;
        }
        if (K2_JVERSION == '30') {
            $editJoomlaUserButton = '<a data-k2-modal="iframe" href="'.$editJoomlaUserButtonUrl.'" class="btn btn-small"><i class="icon-edit"></i>'.JText::_('K2_EDIT_JOOMLA_USER').'</a>';
        } else {
            $editJoomlaUserButton = '<a data-k2-modal="iframe" href="'.$editJoomlaUserButtonUrl.'"><span class="icon-32-edit" title="'.JText::_('K2_EDIT_JOOMLA_USER').'"></span>'.JText::_('K2_EDIT_JOOMLA_USER').'</a>';
        }
        $toolbar->prependButton('Custom', $editJoomlaUserButton);

        parent::display($tpl);
    }
}
