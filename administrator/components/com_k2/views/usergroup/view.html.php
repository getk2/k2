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

jimport('joomla.application.component.view');

class K2ViewUserGroup extends K2View
{
    function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');

        $model = $this->getModel();
        $userGroup = $model->getData();
        if (K2_JVERSION == '15')
        {
            JFilterOutput::objectHTMLSafe($userGroup);
        }
        else
        {
            JFilterOutput::objectHTMLSafe($userGroup, ENT_QUOTES, 'permissions');
        }
        $this->assignRef('row', $userGroup);

        if (K2_JVERSION == '15')
        {
            $form = new JParameter('', JPATH_COMPONENT.'/models/usergroup.xml');
            $form->loadINI($userGroup->permissions);
            $appliedCategories = $form->get('categories');
            $inheritance = $form->get('inheritance');
        }
        else
        {
            jimport('joomla.form.form');
            $form = JForm::getInstance('permissions', JPATH_COMPONENT_ADMINISTRATOR.'/models/usergroup.xml');
            $values = array('params' => json_decode($userGroup->permissions));
            $form->bind($values);
            $inheritance = isset($values['params']->inheritance) ? $values['params']->inheritance : 0;
            $appliedCategories = isset($values['params']->categories) ? $values['params']->categories : '';
        }
        $this->assignRef('form', $form);
        $this->assignRef('categories', $appliedCategories);

        $lists = array();
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
        $categoriesModel = K2Model::getInstance('Categories', 'K2Model');
        $categories = $categoriesModel->categoriesTree(NULL, true);
        $categories_options = @array_merge($categories_option, $categories);
        $lists['categories'] = JHTML::_('select.genericlist', $categories, 'params[categories][]', 'multiple="multiple" size="15"', 'value', 'text', $appliedCategories);
        $lists['inheritance'] = JHTML::_('select.booleanlist', 'params[inheritance]', NULL, $inheritance);
        $this->assignRef('lists', $lists);

        // Disable Joomla menu
        JRequest::setVar('hidemainmenu', 1);

        // Toolbar
        $title = (JRequest::getInt('cid')) ? JText::_('K2_EDIT_USER_GROUP') : JText::_('K2_ADD_USER_GROUP');
        JToolBarHelper::title($title, 'k2.png');
        JToolBarHelper::apply();
        JToolBarHelper::save();
        JToolBarHelper::cancel();

        parent::display($tpl);
    }
}
