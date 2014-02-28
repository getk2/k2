<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewTag extends K2View
{

    function display($tpl = null)
    {

        JRequest::setVar('hidemainmenu', 1);
        $model = $this->getModel();
        $tag = $model->getData();
        JFilterOutput::objectHTMLSafe($tag);
        if (!$tag->id)
            $tag->published = 1;
        $this->assignRef('row', $tag);

        $lists = array();
        $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $tag->published);
        $this->assignRef('lists', $lists);
        (JRequest::getInt('cid')) ? $title = JText::_('K2_EDIT_TAG') : $title = JText::_('K2_ADD_TAG');
        JToolBarHelper::title($title, 'k2.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();

        parent::display($tpl);
    }

}
