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

class K2ViewExtraFieldsGroup extends K2View
{
    public function display($tpl = null)
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
        $saveNewIcon = version_compare(JVERSION, '2.5.0', 'ge') ? 'save-new.png' : 'save.png';
        JToolBarHelper::custom('saveAndNew', $saveNewIcon, 'save_f2.png', 'K2_SAVE_AND_NEW', false);
        JToolBarHelper::cancel();

        // JS
        $document = JFactory::getDocument();
        $document->addScriptDeclaration("
            Joomla.submitbutton = function(pressbutton) {
                if (pressbutton == 'cancel') {
                    submitform(pressbutton);
                    return;
                }
                if (\$K2.trim(\$K2('#name').val()) == '') {
                    alert('".JText::_('K2_GROUP_NAME_CANNOT_BE_EMPTY', true)."');
                } else {
                    submitform(pressbutton);
                }
            };
        ");

        parent::display($tpl);
    }
}
