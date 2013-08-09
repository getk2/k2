<?php
/**
 * @version		$Id: view.html.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewExtraFieldsGroup extends K2View
{
    function display($tpl = null)
    {
        JRequest::setVar('hidemainmenu', 1);
        $model = $this->getModel();
        $extraFieldsGroup = $model->getExtraFieldsGroup();
        JFilterOutput::objectHTMLSafe($extraFieldsGroup);
        $this->assignRef('row', $extraFieldsGroup);
        (JRequest::getInt('cid')) ? $title = JText::_('K2_EDIT_EXTRA_FIELD_GROUP') : $title = JText::_('K2_ADD_EXTRA_FIELD_GROUP');
        JToolBarHelper::title($title, 'k2.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }

}
