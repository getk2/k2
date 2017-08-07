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

class K2ViewExtraFieldsGroup extends K2View
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $extraFieldsGroup = $model->getExtraFieldsGroup();
        JFilterOutput::objectHTMLSafe($extraFieldsGroup);
        $this->assignRef('row', $extraFieldsGroup);

		// Disable Joomla menu
		JRequest::setVar('hidemainmenu', 1);

        // Toolbar
        $title = (JRequest::getInt('cid')) ? JText::_('K2_EDIT_EXTRA_FIELD_GROUP') : JText::_('K2_ADD_EXTRA_FIELD_GROUP');
        JToolBarHelper::title($title, 'k2.png');

        JToolBarHelper::apply();
        JToolBarHelper::save();
        JToolBarHelper::cancel();

        parent::display($tpl);
    }
}
