<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementCategoriesMultiple extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        $params = JComponentHelper::getParams('com_k2');
        $document = JFactory::getDocument();
        if (version_compare(JVERSION, '1.6.0', 'ge'))
        {
            JHtml::_('behavior.framework');
        }
        else
        {
            JHTML::_('behavior.mootools');
        }
        K2HelperHTML::loadjQuery();

        $db = JFactory::getDBO();
        $query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                if (K2_JVERSION != '15')
                {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();

        foreach ($list as $item)
        {
            $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
            $mitems[] = JHTML::_('select.option', $item->id, '   '.$item->treename);
        }

        $doc = JFactory::getDocument();
        if (K2_JVERSION != '15')
        {
            $js = "
			\$K2(document).ready(function(){
				
				\$K2('#jform_params_catfilter0').click(function(){
					\$K2('#jformparamscategory_id').attr('disabled', 'disabled');
					\$K2('#jformparamscategory_id option').each(function() {
						\$K2(this).attr('selected', 'selected');
					});
					\$K2('#jformparamscategory_id').trigger('liszt:updated');
				});
				
				\$K2('#jform_params_catfilter1').click(function(){
					\$K2('#jformparamscategory_id').removeAttr('disabled');
					\$K2('#jformparamscategory_id option').each(function() {
						\$K2(this).removeAttr('selected');
					});
					\$K2('#jformparamscategory_id').trigger('liszt:updated');
				});
				
				if (\$K2('#jform_params_catfilter0').attr('checked')) {
					\$K2('#jformparamscategory_id').attr('disabled', 'disabled');
					\$K2('#jformparamscategory_id option').each(function() {
						\$K2(this).attr('selected', 'selected');
					});
					\$K2('#jformparamscategory_id').trigger('liszt:updated');
				}
				
				if (\$K2('#jform_params_catfilter1').attr('checked')) {
					\$K2('#jformparamscategory_id').removeAttr('disabled');
					\$K2('#jformparamscategory_id').trigger('liszt:updated');
				}
				
			});
			";

        }
        else
        {
            $js = "
			\$K2(document).ready(function(){
				
				\$K2('#paramscatfilter0').click(function(){
					\$K2('#paramscategory_id').attr('disabled', 'disabled');
					\$K2('#paramscategory_id option').each(function() {
						\$K2(this).attr('selected', 'selected');
					});
				});
				
				\$K2('#paramscatfilter1').click(function(){
					\$K2('#paramscategory_id').removeAttr('disabled');
					\$K2('#paramscategory_id option').each(function() {
						\$K2(this).removeAttr('selected');
					});
	
				});
				
				if (\$K2('#paramscatfilter0').attr('checked')) {
					\$K2('#paramscategory_id').attr('disabled', 'disabled');
					\$K2('#paramscategory_id option').each(function() {
						\$K2(this).attr('selected', 'selected');
					});
				}
				
				if (\$K2('#paramscatfilter1').attr('checked')) {
					\$K2('#paramscategory_id').removeAttr('disabled');
				}
				
			});
			";

        }

        if (K2_JVERSION != '15')
        {
            $fieldName = $name.'[]';
        }
        else
        {
            $fieldName = $control_name.'['.$name.'][]';
        }

        $doc->addScriptDeclaration($js);
        $output = JHTML::_('select.genericlist', $mitems, $fieldName, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $value);
        return $output;
    }

}

class JFormFieldCategoriesMultiple extends K2ElementCategoriesMultiple
{
    var $type = 'categoriesmultiple';
}

class JElementCategoriesMultiple extends K2ElementCategoriesMultiple
{
    var $_name = 'categoriesmultiple';
}
