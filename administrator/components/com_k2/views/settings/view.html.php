<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewSettings extends K2View
{
    public function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');

        jimport('joomla.html.pane');

        $model = $this->getModel();

        $params = $model->getParams();
        $this->assignRef('params', $params);

        $pane = JPane::getInstance('Tabs');
        $this->assignRef('pane', $pane);

        parent::display($tpl);
    }
}
