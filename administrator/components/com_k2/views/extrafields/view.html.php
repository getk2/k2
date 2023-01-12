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

class K2ViewExtraFields extends K2View
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');

        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $filter_order = $app->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'groupname', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
        $filter_state = $app->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
        $search = $app->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search));
        $filter_type = $app->getUserStateFromRequest($option.$view.'filter_type', 'filter_type', '', 'string');
        $filter_group = $app->getUserStateFromRequest($option.$view.'filter_group', 'filter_group', '', 'string');

        $model = $this->getModel();
        $total = $model->getTotal();
        if ($limitstart > $total - $limit) {
            $limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
            JRequest::setVar('limitstart', $limitstart);
        }
        $extraFields = $model->getData();
        foreach ($extraFields as $key => $extraField) {
            $extraField->status = K2_JVERSION == '15' ? JHTML::_('grid.published', $extraField, $key) : JHtml::_('jgrid.published', $extraField->published, $key);
            $values = json_decode($extraField->value);
            if (isset($values[0]->alias) && !empty($values[0]->alias)) {
                $extraField->alias = $values[0]->alias;
            } else {
                $filter = JFilterInput::getInstance();
                $extraField->alias = $filter->clean($extraField->name, 'WORD');
            }
        }
        $this->assignRef('rows', $extraFields);

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $this->assignRef('page', $pageNav);

        $lists = array();
        $lists['search'] = $search;
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        $filter_state_options[] = JHTML::_('select.option', -1, JText::_('K2_SELECT_STATE'));
        $filter_state_options[] = JHTML::_('select.option', 1, JText::_('K2_PUBLISHED'));
        $filter_state_options[] = JHTML::_('select.option', 0, JText::_('K2_UNPUBLISHED'));
        $lists['state'] = JHTML::_('select.genericlist', $filter_state_options, 'filter_state', '', 'value', 'text', $filter_state);

        $extraFieldGroups = $model->getGroups(true);
        $groups[] = JHTML::_('select.option', '0', JText::_('K2_SELECT_GROUP'));

        foreach ($extraFieldGroups as $extraFieldGroup) {
            $groups[] = JHTML::_('select.option', $extraFieldGroup->id, $extraFieldGroup->name);
        }
        $lists['group'] = JHTML::_('select.genericlist', $groups, 'filter_group', '', 'value', 'text', $filter_group);

        $typeOptions[] = JHTML::_('select.option', 0, JText::_('K2_SELECT_TYPE'));

        $typeOptions[] = JHTML::_('select.option', 'textfield', JText::_('K2_TEXT_FIELD'));
        $typeOptions[] = JHTML::_('select.option', 'textarea', JText::_('K2_TEXTAREA'));
        $typeOptions[] = JHTML::_('select.option', 'select', JText::_('K2_DROPDOWN_SELECTION'));
        $typeOptions[] = JHTML::_('select.option', 'multipleSelect', JText::_('K2_MULTISELECT_LIST'));
        $typeOptions[] = JHTML::_('select.option', 'radio', JText::_('K2_RADIO_BUTTONS'));
        $typeOptions[] = JHTML::_('select.option', 'link', JText::_('K2_LINK'));
        $typeOptions[] = JHTML::_('select.option', 'csv', JText::_('K2_CSV_DATA'));
        $typeOptions[] = JHTML::_('select.option', 'labels', JText::_('K2_SEARCHABLE_LABELS'));
        $typeOptions[] = JHTML::_('select.option', 'date', JText::_('K2_DATE'));
        $typeOptions[] = JHTML::_('select.option', 'image', JText::_('K2_IMAGE'));
        $typeOptions[] = JHTML::_('select.option', 'header', JText::_('K2_HEADER'));

        $lists['type'] = JHTML::_('select.genericlist', $typeOptions, 'filter_type', '', 'value', 'text', $filter_type);

        $this->assignRef('lists', $lists);

        // Toolbar
        JToolBarHelper::title(JText::_('K2_EXTRA_FIELDS'), 'k2.png');

        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList('K2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_EXTRA_FIELDS', 'remove', 'K2_DELETE');

        if (K2_JVERSION != '15') {
            JToolBarHelper::preferences('com_k2', '(window.innerHeight) * 0.9', '(window.innerWidth) * 0.7', 'K2_SETTINGS');
        } else {
            $toolbar = JToolBar::getInstance('toolbar');
            $toolbar->appendButton('Popup', 'config', 'K2_SETTINGS', 'index.php?option=com_k2&view=settings', '(window.innerWidth) * 0.7', '(window.innerHeight) * 0.9');
        }

        $this->loadHelper('html');
        K2HelperHTML::subMenu();

        $ordering = ($this->lists['order'] == 'ordering');
        $this->assignRef('ordering', $ordering);

        // Joomla 3.x drag-n-drop sorting variables
        if (K2_JVERSION == '30') {
            if ($ordering) {
                JHtml::_('sortablelist.sortable', 'k2ExtraFieldsList', 'adminForm', strtolower($this->lists['order_Dir']), 'index.php?option=com_k2&view=extrafields&task=saveorder&format=raw');
            }
            $document = JFactory::getDocument();
            $document->addScriptDeclaration('
            Joomla.orderTable = function() {
                table = document.getElementById("sortTable");
                direction = document.getElementById("directionTable");
                order = table.options[table.selectedIndex].value;
                if (order != "'.$this->lists['order'].'") {
                    dirn = "asc";
				} else {
                	dirn = direction.options[direction.selectedIndex].value;
				}
				Joomla.tableOrdering(order, dirn, "");
            }
            ');
        }

        parent::display($tpl);
    }
}
