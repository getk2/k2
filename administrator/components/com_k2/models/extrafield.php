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

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelExtraField extends K2Model
{
    public function getData()
    {
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($cid);
        return $row;
    }

    public function save()
    {
        $application = JFactory::getApplication();
        $row = JTable::getInstance('K2ExtraField', 'Table');
        if (!$row->bind(JRequest::get('post'))) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=extrafields');
        }

        $isNewGroup = JRequest::getInt('isNew');

        if ($isNewGroup) {
            $group = JTable::getInstance('K2ExtraFieldsGroup', 'Table');
            $group->set('name', JRequest::getVar('group'));
            $group->store();
            $row->group = $group->id;
        }

        if (!$row->id) {
            $row->ordering = $row->getNextOrder("`group` = ".(int)$row->group);
        }

        $objects = array();
        $values = JRequest::getVar('option_value', null, 'default', 'none', 4);
        $names = JRequest::getVar('option_name');
        $target = JRequest::getVar('option_target');
        $editor = JRequest::getVar('option_editor');
        $rows = JRequest::getVar('option_rows');
        $cols = JRequest::getVar('option_cols');
        $alias = JRequest::getWord('alias');
        $required = JRequest::getInt('required');
        $showNull = JRequest::getInt('showNull');
        $displayInFrontEnd = JRequest::getInt('displayInFrontEnd');

        if (JString::strtolower($alias) == 'this') {
            $alias = '';
        }
        $lastOptionId = 1;
        for ($i = 0; $i < sizeof($values); $i++) {
            $object = new JObject;
            $object->set('name', $names[$i]);

            if ($row->type == 'select' || $row->type == 'multipleSelect' || $row->type == 'radio') {
                if (!empty($values[$i])) {
                    $object->set('value', $values[$i]);
                    $lastOptionId = intval($values[$i]);
                } else {
                    $lastOptionId ++;
                    $object->set('value', $lastOptionId);
                }
            } elseif ($row->type == 'link') {
                if (trim($values[$i]) != '') {
                    if (substr($values[$i], 0, 7) == 'http://' || substr($values[$i], 0, 8) == 'https://' || substr($values[$i], 0, 2) == '//' || substr($values[$i], 0, 1) == '/' || substr($values[$i], 0, 7) == 'mailto:' || substr($values[$i], 0, 4) == 'tel:') {
                        $values[$i] = $values[$i];
                    } else {
                        $values[$i] = 'http://'.$values[$i];
                    }
                }
                $object->set('value', trim($values[$i]));
            } elseif ($row->type == 'csv') {
                $file = JRequest::getVar('csv_file', null, 'FILES');
                $csvFile = $file['tmp_name'];
                if (!empty($csvFile) && JFile::getExt($file['name']) == 'csv') {
                    $handle = @fopen($csvFile, 'r');
                    $csvData = array();
                    while (($data = fgetcsv($handle, 1000)) !== false) {
                        $csvData[] = $data;
                    }
                    fclose($handle);
                    $object->set('value', $csvData);
                } else {
                    $object->set('value', json_decode($values[$i]));
                    if (JRequest::getBool('K2ResetCSV')) {
                        $object->set('value', null);
                    }
                }
            } elseif ($row->type == 'textarea') {
                $object->set('value', $values[$i]);
                $object->set('editor', $editor[$i]);
                $object->set('rows', $rows[$i]);
                $object->set('cols', $cols[$i]);
            } elseif ($row->type == 'image') {
                $object->set('value', $values[$i]);
            } elseif ($row->type == 'header') {
                $object->set('value', JRequest::getString('name'));
                $object->set('displayInFrontEnd', $displayInFrontEnd);
            } else {
                $object->set('value', $values[$i]);
            }

            $object->set('target', $target[$i]);
            $object->set('alias', $alias);
            $object->set('required', $required);
            $object->set('showNull', $showNull);
            unset($object->_errors);
            $objects[] = $object;
        }

        $row->value = json_encode($objects);

        if (!$row->check()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=extrafield&cid='.$row->id);
        }

        if (!$row->store()) {
            $application->enqueueMessage($row->getError(), 'error');
            $application->redirect('index.php?option=com_k2&view=extrafields');
        }

        $params = JComponentHelper::getParams('com_k2');
        if (!$params->get('disableCompactOrdering')) {
            $row->reorder("`group` = ".(int)$row->group);
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        switch (JRequest::getCmd('task')) {
            case 'apply':
                $msg = JText::_('K2_CHANGES_TO_EXTRA_FIELD_SAVED');
                $link = 'index.php?option=com_k2&view=extrafield&cid='.$row->id;
                break;
            case 'saveAndNew':
                $msg = JText::_('K2_EXTRA_FIELD_SAVED');
                $link = 'index.php?option=com_k2&view=extrafield';
                break;
            case 'save':
            default:
                $msg = JText::_('K2_EXTRA_FIELD_SAVED');
                $link = 'index.php?option=com_k2&view=extrafields';
                break;
        }
        $application->enqueueMessage($msg);
        $application->redirect($link);
    }

    public function getExtraFieldsByGroup($group)
    {
        $db = JFactory::getDbo();
        $group = (int)$group;
        $query = "SELECT * FROM #__k2_extra_fields WHERE `group`={$group} AND published=1 ORDER BY ordering";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function renderExtraField($extraField, $itemID = null)
    {
        $application = JFactory::getApplication();

        if (!is_null($itemID)) {
            $item = JTable::getInstance('K2Item', 'Table');
            $item->load($itemID);
        }

        $defaultValues = json_decode($extraField->value);

        foreach ($defaultValues as $value) {
            $required = isset($value->required) ? $value->required : 0;
            $showNull = isset($value->showNull) ? $value->showNull : 0;

            if ($extraField->type == 'textfield' || $extraField->type == 'csv' || $extraField->type == 'labels' || $extraField->type == 'date' || $extraField->type == 'image') {
                $active = $value->value;
            } elseif ($extraField->type == 'textarea') {
                $active[0] = $value->value;
                $active[1] = $value->editor;
                $active[2] = (int)$value->rows ? (int)$value->rows : 10;
                $active[3] = (int)$value->cols ? (int)$value->cols : 40;
            } elseif ($extraField->type == 'link') {
                $active[0] = $value->name;
                $active[1] = $value->value;
                $active[2] = $value->target;
            } else {
                $active = '';
            }
        }

        if (!isset($active)) {
            $active = '';
        }

        if (isset($item)) {
            $currentValues = json_decode($item->extra_fields);
            if (count($currentValues)) {
                foreach ($currentValues as $value) {
                    if ($value->id == $extraField->id) {
                        if ($extraField->type == 'textarea') {
                            $active[0] = $value->value;
                        } elseif ($extraField->type == 'date') {
                            $active = (is_array($value->value)) ? $value->value[0] : $value->value;
                        } elseif ($extraField->type == 'header') {
                            continue;
                        } else {
                            $active = $value->value;
                        }
                    }
                }
            }
        }
        $attributes = '';
        $arrayAttributes = array();
        if ($required) {
            $arrayAttributes['class'] = "k2Required";
            $attributes .= 'class="k2Required"';
        }

        if ($showNull && in_array($extraField->type, array(
            'select',
            'multipleSelect'
        ))) {
            $nullOption = new stdClass;
            $nullOption->name = JText::_('K2_PLEASE_SELECT');
            $nullOption->value = '';
            array_unshift($defaultValues, $nullOption);
        }

        if (in_array($extraField->type, array(
            'textfield',
            'labels',
            'date',
            'image'
        ))) {
            $active = htmlspecialchars($active, ENT_QUOTES, 'UTF-8');
        }

        switch ($extraField->type) {

            case 'textfield':
                $output = '<input type="text" name="K2ExtraField_'.$extraField->id.'" id="K2ExtraField_'.$extraField->id.'" value="'.$active.'" '.$attributes.' />';
                break;

            case 'labels':
                $output = '<input type="text" name="K2ExtraField_'.$extraField->id.'" id="K2ExtraField_'.$extraField->id.'" value="'.$active.'" '.$attributes.' /> '.JText::_('K2_COMMA_SEPARATED_VALUES');
                break;

            case 'textarea':
                if ($active[1]) {
                    if ($required) {
                        $attributes = 'class="k2ExtraFieldEditor k2Required"';
                    } else {
                        $attributes = 'class="k2ExtraFieldEditor"';
                    }
                }
                $output = '<textarea name="K2ExtraField_'.$extraField->id.'" id="K2ExtraField_'.$extraField->id.'" rows="'.$active[2].'" cols="'.$active[3].'" '.$attributes.'>'.htmlspecialchars($active[0], ENT_QUOTES, 'UTF-8').'</textarea>';
                break;

            case 'select':
                $attributes .= ' id="K2ExtraField_'.$extraField->id.'"';
                $arrayAttributes['id'] = 'K2ExtraField_'.$extraField->id;
                $attrs = version_compare(JVERSION, '3.2', 'ge') ? $arrayAttributes : $attributes;
                $output = JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id, $attrs, 'value', 'name', $active);
                break;

            case 'multipleSelect':

                $attributes .= ' id="K2ExtraField_'.$extraField->id.'" multiple="multiple"';
                $arrayAttributes['id'] = 'K2ExtraField_'.$extraField->id;
                $arrayAttributes['multiple'] = "multiple";
                $attrs = version_compare(JVERSION, '3.2', 'ge') ? $arrayAttributes : $attributes;
                $output = JHTML::_('select.genericlist', $defaultValues, 'K2ExtraField_'.$extraField->id.'[]', $attrs, 'value', 'name', $active);
                break;

            case 'radio':
                if (!$active && isset($defaultValues[0])) {
                    $active = $defaultValues[0]->value;
                }
                $attrs = version_compare(JVERSION, '3.2', 'ge') ? $arrayAttributes : $attributes;
                $output = JHTML::_('select.radiolist', $defaultValues, 'K2ExtraField_'.$extraField->id, $attrs, 'value', 'name', $active);
                break;

            case 'link':
                $output = '<label>'.JText::_('K2_TEXT').'</label>';
                $output .= '<input type="text" name="K2ExtraField_'.$extraField->id.'[]" value="'.htmlspecialchars($active[0], ENT_QUOTES, 'UTF-8').'" />';
                $output .= '<label>'.JText::_('K2_URL').'</label>';
                $output .= '<input type="text" name="K2ExtraField_'.$extraField->id.'[]" id="K2ExtraField_'.$extraField->id.'"  value="'.htmlspecialchars($active[1], ENT_QUOTES, 'UTF-8').'" '.$attributes.'/>';
                $output .= '<label>'.JText::_('K2_OPEN_IN').'</label>';
                $targetOptions[] = JHTML::_('select.option', 'same', JText::_('K2_SAME_WINDOW'));
                $targetOptions[] = JHTML::_('select.option', 'new', JText::_('K2_NEW_WINDOW'));
                $targetOptions[] = JHTML::_('select.option', 'popup', JText::_('K2_CLASSIC_JAVASCRIPT_POPUP'));
                $targetOptions[] = JHTML::_('select.option', 'lightbox', JText::_('K2_LIGHTBOX_POPUP'));
                $output .= JHTML::_('select.genericlist', $targetOptions, 'K2ExtraField_'.$extraField->id.'[]', '', 'value', 'text', $active[2]);
                break;

            case 'csv':
                if ($active) {
                    $attributes = '';
                }
                $output = '<input type="file" id="K2ExtraField_'.$extraField->id.'" name="K2ExtraField_'.$extraField->id.'[]" '.$attributes.' />';
                if (is_array($active) && count($active)) {
                    $output .= '<input type="hidden" name="K2CSV_'.$extraField->id.'" value="'.htmlspecialchars(json_encode($active)).'" />';
                    $output .= '<table class="csvTable">';
                    foreach ($active as $key => $row) {
                        $output .= '<tr>';
                        foreach ($row as $cell) {
                            $output .= ($key > 0) ? '<td>'.$cell.'</td>' : '<th>'.$cell.'</th>';
                        }
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                    $output .= '<label>'.JText::_('K2_DELETE_CSV_DATA').'</label>';
                    $output .= '<input type="checkbox" name="K2ResetCSV_'.$extraField->id.'" />';
                }
                break;

            case 'date':
                if ($required) {
                    $cssClass = 'k2Calendar k2Required';
                } else {
                    $cssClass = 'k2Calendar';
                }
                $output = '<input class="'.$cssClass.'" type="text" data-k2-datetimepicker="{allowInput:true}" name="K2ExtraField_'.$extraField->id.'" id="K2ExtraField_'.$extraField->id.'" value="'.$active.'" />';
                break;
            case 'image':
                $output = '<input type="text" name="K2ExtraField_'.$extraField->id.'" id="K2ExtraField_'.$extraField->id.'" value="'.$active.'" '.$attributes.' /><a class="k2ExtraFieldImageButton" href="'.JURI::base(true).'/index.php?option=com_k2&view=media&type=image&tmpl=component&fieldID=K2ExtraField_'.$extraField->id.'">'.JText::_('K2_SELECT').'</a>';
                break;
            case 'header':
                $output = '';
                break;
        }

        return $output;
    }

    public function getExtraFieldInfo($fieldID)
    {
        $db = JFactory::getDbo();
        $fieldID = (int)$fieldID;
        $query = "SELECT * FROM #__k2_extra_fields WHERE published=1 AND id = ".$fieldID;
        $db->setQuery($query, 0, 1);
        $row = $db->loadObject();
        return $row;
    }

    public function getSearchValue($id, $currentValue)
    {
        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($id);

        $jsonObject = json_decode($row->value);

        $value = '';
        if ($row->type == 'textfield' || $row->type == 'textarea') {
            $value = $currentValue;
        } elseif ($row->type == 'multipleSelect') {
            foreach ($jsonObject as $option) {
                if (in_array($option->value, $currentValue)) {
                    $value .= $option->name.' ';
                }
            }
        } elseif ($row->type == 'link') {
            $value .= $currentValue[0].' ';
            $value .= $currentValue[1].' ';
        } elseif ($row->type == 'labels') {
            $parts = explode(',', $currentValue);
            $value .= implode(' ', $parts);
        } else {
            foreach ($jsonObject as $option) {
                if ($option->value == $currentValue) {
                    $value .= $option->name;
                }
            }
        }
        return $value;
    }
}
