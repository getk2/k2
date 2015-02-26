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

jimport('joomla.application.component.view');

class K2ViewUserGroup extends K2View
{

    function display($tpl = null)
    {

        JHTML::_('behavior.tooltip');
        JRequest::setVar('hidemainmenu', 1);
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
            $form = new JParameter('', JPATH_COMPONENT.DS.'models'.DS.'usergroup.xml');
            $form->loadINI($userGroup->permissions);
            $appliedCategories = $form->get('categories');
            $inheritance = $form->get('inheritance');
        }
        else
        {
            jimport('joomla.form.form');
            $form = JForm::getInstance('permissions', JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'usergroup.xml');
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
		$lists['categories'] = JHtml::_('select.genericlist', $categories, 'params[categories][]', 'multiple="multiple" size="15"', 'value', 'text', $appliedCategories);
		$lists['inheritance'] = JHtml::_('select.booleanlist', 'params[inheritance]', NULL, $inheritance);

		$extraFieldsModel = K2Model::getInstance('ExtraFields', 'K2Model');
		$groups = $extraFieldsModel->getGroups();
		$group [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'K2_NONE_ONSELECTLISTS' ), 'id', 'name' );
		$extraFieldsGroups = array_merge ( $group, $groups );
		//JAW modified - made multiple select
		$lists['extraFieldsGroups'] = JHtml::_ ( 'select.genericlist', $extraFieldsGroups, 'extraFieldsGroups[]', 'class="inputbox" size="5" multiple="multiple"', 'id', 'name', $userGroup->extraFieldsGroups );

        $this->assignRef('lists', $lists);
        (JRequest::getInt('cid')) ? $title = JText::_('K2_EDIT_USER_GROUP') : $title = JText::_('K2_ADD_USER_GROUP');
        JToolBarHelper::title($title, 'k2.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();

        parent::display($tpl);
    }

}
