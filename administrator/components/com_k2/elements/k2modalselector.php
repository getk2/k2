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

require_once(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementK2modalselector extends K2Element
{
    function fetchElement($name, $value, &$node, $control_name)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

        // Attributes
        $fieldID = 'fieldID_'.md5($name);
        if (version_compare(JVERSION, '1.5.0', 'gt'))
        {
            if($node->attributes()->scope)
            {
                $scope = $node->attributes()->scope;
            }
            else
            {
                $scope = 'items';
            }
            if($scope == 'items' || $scope == 'categories' || $scope == 'users' || $scope == 'tags')
            {
                $fieldName = $name.'[]';
            }
            else
            {
                $fieldName = $name;
            }
        }
        else
        {
            if($node->attributes('scope')){
                $scope = $node->attributes('scope');
            }
            else
            {
                $scope = 'items';
            }
            if($scope == 'items' || $scope == 'categories' || $scope == 'users' || $scope == 'tags')
            {
                $fieldName = $control_name.'['.$name.'][]';
            }
            else
            {
                $fieldName = $control_name.'['.$name.']';
            }
        }
        if(!$value)
        {
          $value = '';
        }
        $saved = array();
        if (is_string($value) && !empty($value))
        {
            $saved[] = $value;
        }
        if (is_array($value))
        {
            $saved = $value;
        }

        // Output
        $output = '';

        // Output for lists
        if($scope == 'items' || $scope == 'categories' || $scope == 'users' || $scope == 'tags')
        {
            $output = '
            <div class="k2SelectorButton">
                <a data-k2-modal="iframe" class="btn" title="'.JText::_('K2_SELECT').'" href="index.php?option=com_k2&view='.$scope.'&tmpl=component&context=modalselector&output=list&fid='.$fieldID.'&fname='.$fieldName.'">
                    <i class="fa fa-file-text-o"></i> '.JText::_('K2_SELECT').'
                </a>
            </div>
            <ul id="'.$fieldID.'" class="k2SortableListContainer">
            ';

            foreach ($saved as $value)
            {
                if($scope == 'items')
                {
                    $row = JTable::getInstance('K2Item', 'Table');
                    $row->load($value);
                    $entryName = $row->title;
                    $entryValue = $row->id;
                }
                if($scope == 'categories')
                {
                    $row = JTable::getInstance('K2Category', 'Table');
                    $row->load($value);
                    $entryName = $row->name;
                    $entryValue = $row->id;
                }
                if($scope == 'users')
                {
                    $row = JFactory::getUser($value);
                    $entryName = $row->name;
                    $entryValue = $row->id;
                }
                if($scope == 'tags')
                {
                    $db = JFactory::getDbo();
                    $query = 'SELECT * FROM #__k2_tags WHERE name='.$db->Quote($value);
                    $db->setQuery($query);
                    $row = $db->loadObject();
                    $entryName = $row->name;
                    $entryValue = htmlspecialchars($row->name, ENT_QUOTES, 'utf-8');
                }

                $output .= '<li class="handle"><a class="k2EntryRemove" href="#" title="'.JText::_('K2_REMOVE_THIS_ENTRY').'"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">'.$entryName.'</span><input type="hidden" name="'.$fieldName.'" value="'.$entryValue.'" /></li>';
            }
            $output .= '
            </ul>
            ';
        }

        // Output for single entities
        if($scope == 'item' || $scope == 'category' || $scope == 'user' || $scope == 'tag')
        {
            if(count($saved)) $value = $saved[0]; else $value = '';

            if($scope == 'item')
            {
                if($value)
                {
                    $row = JTable::getInstance('K2Item', 'Table');
                    $row->load($value);
                    $entryName = $row->title;
                    $entryValue = $row->id;
                }
                $view = "items";
            }
            if($scope == 'category')
            {
                if($value)
                {
                    $row = JTable::getInstance('K2Category', 'Table');
                    $row->load($value);
                    $entryName = $row->name;
                    $entryValue = $row->id;
                }
                $view = "categories";
            }
            if($scope == 'user')
            {
                if($value)
                {
                    $row = JFactory::getUser($value);
                    $entryName = $row->name;
                    $entryValue = $row->id;
                }
                $view = "users";
            }
            if($scope == 'tag')
            {
                if($value)
                {
                    $db = JFactory::getDbo();
                    $query = 'SELECT * FROM #__k2_tags WHERE name='.$db->Quote($value);
                    $db->setQuery($query);
                    $row = $db->loadObject();
                    $entryName = $row->name;
                    $entryValue = htmlspecialchars($row->name, ENT_QUOTES, 'utf-8');
                }
                $view = "tags";
            }

            $output = '
            <div class="k2SelectorButton k2SingleSelect">
                <a data-k2-modal="iframe" class="btn" title="'.JText::_('K2_SELECT').'" href="index.php?option=com_k2&view='.$view.'&tmpl=component&context=modalselector&fid='.$fieldID.'&fname='.$fieldName.'">
                    <i class="fa fa-file-text-o"></i> '.JText::_('K2_SELECT').'
                </a>
            </div>
            <div id="'.$fieldID.'" class="k2SingleSelect">
            ';

            if($value)
            {
                $output .= '<div class="handle"><a class="k2EntryRemove" href="#" title="'.JText::_('K2_REMOVE_THIS_ENTRY').'"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">'.$entryName.'</span><input type="hidden" name="'.$fieldName.'" value="'.$entryValue.'" /></div>';
            }
            $output .= '
            </div>
            ';

        }

        return $output;
    }
}

class JFormFieldK2modalselector extends K2ElementK2modalselector
{
    var $type = 'k2modalselector';
}

class JElementK2modalselector extends K2ElementK2modalselector
{
    var $_name = 'k2modalselector';
}
