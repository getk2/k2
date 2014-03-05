<?php
/**
 * @version     2.6.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JFormHelper::loadFieldClass('list');
JFormHelper::loadFieldClass('category');

class JFormFieldK2LanguageCategory extends JFormFieldCategory
{

    public $type = 'K2LanguageCategory';

    public function __get($name)
    {

        switch ($name)
        {
            case 'element' :
                return $this->$name;
                break;
        }

        $value = parent::__get($name);
        return $value;
    }

    protected function getOptions()
    {

        // Initialise variables.
        $options = array();
        $published = (string)$this->element['published'];
        $languages = (string)$this->element['languages'];
        $name = (string)$this->element['name'];

        // insert custom options passed in xml file
        $options = array();
        if (!is_null($this->element->option))
        {
            foreach ($this->element->option as $option)
            {
                $options[] = JHtml::_('select.option', $option->getAttribute('value'), JText::_($option->data()));
            }
        }

        // Filter over published state or not depending upon if it is present.
        // include k2item helper, which has the method we want
        require_once JPATH_PLUGINS.'/josetta_ext/k2item/helpers/helper.php';
        if ($published)
        {
            $categoriesoptions = JosettaK2ItemHelper::getCategoryOptionsPerLanguage(array('filter.published' => explode(',', $published), 'filter.languages' => explode(',', $languages)));
        }
        else
        {
            $categoriesoptions = JosettaK2ItemHelper::getCategoryOptionsPerLanguage(array('filter.languages' => explode(',', $languages)));
        }

        $options = array_merge($options, $categoriesoptions);

        if (!empty($this->element['show_root']) && strtolower($this->element['show_root']) == 'yes')
        {
            array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
        }

        return $options;
    }

}
