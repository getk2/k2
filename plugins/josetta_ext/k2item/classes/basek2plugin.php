<?php
/**
 * @version     2.6.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

// include base class of josetta plugins
require_once JPATH_ADMINISTRATOR.DS.'components/com_josetta/classes/extensionplugin.php';

class plgJosetta_extBaseK2Plugin extends JosettaadminClass_Extensionplugin
{

    protected $_context = 'com_k2';

    public function loadLanguages()
    {
        // load Joomla global language files
        parent::loadLanguages();
        $language = JFactory::getLanguage();
        // load the administrator english language of the component
        $language->load('com_k2', JPATH_ADMINISTRATOR, 'en-GB', true);
        // load the administrator default language of the component
        $language->load('com_k2', JPATH_ADMINISTRATOR, null, true);
    }

    protected function _setPath()
    {
        $this->_path = JPATH_PLUGINS.'/josetta_ext/'.$this->_name;
    }

    /**
     * Hook for 3rd party extensions to add a path to search for
     * additional subtypes fields definitions
     * To be used by extensions, for instance to handle specific menu items
     * subtypes, or module subtypes
     *
     * @param string context
     */
    public function onJosettaAddSubTypePath($context, $subType)
    {

        if (!empty($context) && ($context != $this->_context))
        {
            return;
        }

        // a 3rd party extension will use this hook to store a full path to
        // a directory where fields_*.xml files can be found
        // to be appended to the translation form,
        // for instance:
        $this->_subTypePath[] = JPATH_PLUGINS.'/josetta_ext/'.$this->_name;
    }

    /**
     * Method to build a list filter for the main translator view
     * Used when such filter is not one of the Josetta built in filters type
     *
     * @return array
     *
     */
    public function onJosettaGet3rdPartyFilter($context, $filterType, $filterName, $current)
    {

        if (!empty($context) && ($context != $this->_context))
        {
            return;
        }

        $filterHtml = '';

        switch( $filterType)
        {

            case 'k2languagecategory' :

                // this is a category, so use Joomla html helper to build the drop down
                $filterHtml = '';
                $filterHtml .= JText::_('COM_JOSETTA_CONTENT_CATEGORY');
                $filterHtml .= '<select name="'.$filterName.'" id="'.$filterName.'" class="inputbox" onchange="this.form.submit()">'."\n";
                $filterHtml .= '<option value="0">'.JText::_('JOPTION_SELECT_CATEGORY').'</option>'."\n";

                $categoriesSelectConfig = array('filter.published' => array(0, 1), 'filter.languages' => array('*', JosettaHelper::getSiteDefaultLanguage()));
                require_once JPATH_PLUGINS.'/josetta_ext/k2item/helpers/helper.php';
                $categoriesOptionsHtml = JosettaK2ItemHelper::getCategoryOptionsPerLanguage($categoriesSelectConfig);

                $filterHtml .= JHtml::_('select.options', $categoriesOptionsHtml, 'value', 'text', (int)($current))."\n";
                $filterHtml .= "</select>\n";
                break;

            default :
                break;
        }

        return empty($filterHtml) ? null : $filterHtml;
    }

    /**
     *
     * Compute the subtitle of a provided item
     * Subtitle is used for display to the user of an item
     * to provide more context
     * By default, we use an item category
     *
     * @param string $context
     * @param mixed $item
     */
    public function onJosettaGetSubtitle($context, $item)
    {

        if (!empty($context) && ($context != $this->_context))
        {
            return;
        }

        $subTitle = '';
        if (empty($item))
        {
            return $subTitle;
        }

        return $subTitle;
    }

    /**
     * Hook for module to add their own fields processing
     * to the form xml
     *
     * @return string
     */
    protected function _output3rdPartyFieldsXml($xmlData, $field, $itemType, $item, $originalItem, $targetLanguage)
    {
        switch( $xmlData->fieldType)
        {

            case 'k2languagecategory' :
                //add extension tag if type category is present
                //add option tag in list if present in jform
                foreach ($field->option as $option)
                {
                    $xmlData->subfield .= '<option value="'.$option->value->data().'">'.$option->title->data().'</option>';
                }
                //Important for developer if using category type extension must be defined in xml
                $xmlData->other .= ' languages="'.$targetLanguage.'"';
                $multiple = !empty($field->multiple) && $field->multiple->data() == 'yes';
                $xmlData->other .= $multiple ? ' multiple="true"' : '';

                break;
            default :
                break;
        }

        return $xmlData;
    }

    /**
     * Format a the original field value for display on the translate view
     *
     * @param object $originalItem the actual data of the original item
     * @param string $originalFieldTitle the field title
     * @param object $field the Joomla! field object
     * @param string the formatted, ready to display, string
     */
    public function onJosettaGet3rdPartyFormatOriginalField($originalItem, $originalFieldTitle, $field)
    {

        $html = null;

        switch( strtolower( $field->type))
        {
            case 'k2languagecategory' :
                // element id can be stored in 2 different locations, depending on plugin
                $elementId = empty($originalItem->request) || !isset($originalItem->request['id']) ? null : $originalItem->request['id'];
                $elementId = is_null($elementId) ? $originalItem->$originalFieldTitle : $elementId;

                if (is_array($elementId))
                {
                    // mmultiple categories selected

                    $size = $field->element->getAttribute('size');
                    $size = empty($size) ? 10 : $size;
                    $html = '<select name="josetta_dummy" id="josetta_dummy" class="inputbox" size="'.$size.'" multiple="multiple" disabled="disabled">'."\n";
                    $categoriesSelectConfig = array('filter.published' => array(0, 1), 'filter.languages' => array('*', JosettaHelper::getSiteDefaultLanguage()));
                    require_once JPATH_PLUGINS.'/josetta_ext/k2item/helpers/helper.php';
                    $categoriesOptionsHtml = JosettaK2ItemHelper::getCategoryOptionsPerLanguage($categoriesSelectConfig);
                    $html .= JHtml::_('select.options', $categoriesOptionsHtml, 'value', 'text', $elementId)."\n";
                    $html .= "</select>\n";

                }
                else
                {
                    // just one category
                    if (empty($elementId))
                    {
                        $html = JText::_('ROOT');
                    }
                    else
                    {
                        require_once JPATH_PLUGINS.'/josetta_ext/k2item/helpers/helper.php';
                        $categories = JosettaK2ItemHelper::getCategoriesPerLanguage(null, 'id');
                        $categoryDetails = empty($categories[$elementId]) ? null : $categories[$elementId];
                        $html = empty($categoryDetails) ? $elementId : $categoryDetails->title;
                        if ($html == 'ROOT')
                        {
                            $html = JText::_('ROOT');
                        }
                    }
                }
                break;
        }

        return $html;
    }

}
