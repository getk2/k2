<?php
/**
 * @version		$Id: translationK2_categoryFilter.php 1618 2012-09-21 11:23:08Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// Don't allow direct linking
defined( 'JPATH_BASE' ) or die( 'Direct Access to this location is not allowed.' );

class translationK2_categoryFilter extends translationFilter {
    function translationK2_categoryFilter($contentElement) {
        $this->filterNullValue = -1;
        $this->filterType = "catid";
        $this->filterField = $contentElement->getFilter("K2_category");
        parent::translationFilter($contentElement);
    }
    
    function _createFilter() {
        $database = JFactory::getDBO();
        if (!$this->filterField)
            return "";
        $filter = "";
        if ($this->filter_value != $this->filterNullValue) {
            $sql = "SELECT tab.id FROM #__k2_items as tab WHERE tab.catid=$this->filter_value";
            $database->setQuery($sql);
            $ids = $database->loadObjectList();
            $idstring = "";
            foreach ($ids as $pid) {
                if (strlen($idstring) > 0)
                    $idstring .= ",";
                $idstring .= $pid->id;
            }
            $filter = "c.id IN($idstring)";
        }
        return $filter;
    }
    
    function _createfilterHTML() {
        if (!$this->filterField)
            return "";
        $db = JFactory::getDBO();
        $categoryOptions = array();
        $categoryOptions[] = JHTML::_('select.option', '-1', JText::_('K2_SELECT_CATEGORY'));
        
        $sql = "SELECT DISTINCT p.id, p.name FROM #__k2_categories as p, #__".$this->tableName." as c WHERE c.".$this->filterField."=p.id ORDER BY p.name";
        $db->setQuery($sql);
        $cats = $db->loadObjectList();
        $catcount = 0;
        foreach ($cats as $cat) {
            $categoryOptions[] = JHTML::_('select.option', $cat->id, $cat->name);
            $catcount++;
        }
        $catnameList = array();
        $catnameList["title"] = JText::_('K2_CATEGORIES');
        $catnameList["html"] = JHTML::_('select.genericlist', $categoryOptions, 'catid_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
        return $catnameList;
    }
}
?>
