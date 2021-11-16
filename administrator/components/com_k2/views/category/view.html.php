<?php
/**
 * @version    2.11.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewCategory extends K2View
{
    public function display($tpl = null)
    {
        $document = JFactory::getDocument();

        JHTML::_('behavior.modal');

        $model = $this->getModel();
        $category = $model->getData();
        if (K2_JVERSION == '15') {
            JFilterOutput::objectHTMLSafe($category);
        } else {
            JFilterOutput::objectHTMLSafe($category, ENT_QUOTES, array('params', 'plugins'));
        }
        if (!$category->id) {
            $category->published = 1;
        }
        $this->assignRef('row', $category);

        // Editor
        $wysiwyg = JFactory::getEditor();
        $editor = $wysiwyg->display('description', $category->description, '100%', '250px', '', '', array('pagebreak', 'readmore'));
        $this->assignRef('editor', $editor);
        $onSave = '';
        if (K2_JVERSION == '30') {
            $onSave = $wysiwyg->save('description');
        }
        $this->assignRef('onSave', $onSave);

        // JS
        $document->addScriptDeclaration("
            var K2BasePath = '".JURI::base(true)."/';
            Joomla.submitbutton = function(pressbutton) {
                if (pressbutton == 'cancel') {
                    submitform(pressbutton);
                    return;
                }
                if (\$K2.trim(\$K2('#name').val()) == '') {
                    alert('".JText::_('K2_A_CATEGORY_MUST_AT_LEAST_HAVE_A_TITLE', true)."');
                } else {
                    ".$onSave."
                    submitform(pressbutton);
                }
            };
        ");

        $lists = array();
        $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $category->published);
        $lists['access'] = version_compare(JVERSION, '2.5', 'ge') ? JHTML::_('access.level', 'access', $category->access, '', false) : str_replace('size="3"', "", JHTML::_('list.accesslevel', $category));
        $query = 'SELECT ordering AS value, name AS text FROM #__k2_categories ORDER BY ordering';
        $lists['ordering'] = version_compare(JVERSION, '3.0', 'ge') ? null : JHTML::_('list.specificordering', $category, $category->id, $query);
        $categories[] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'));

        require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
        $categoriesModel = K2Model::getInstance('Categories', 'K2Model');
        $tree = $categoriesModel->categoriesTree($category, true, false);
        $categories = array_merge($categories, $tree);
        $lists['parent'] = JHTML::_('select.genericlist', $categories, 'parent', 'class="inputbox"', 'value', 'text', $category->parent);

        $extraFieldsModel = K2Model::getInstance('ExtraFields', 'K2Model');
        $groups = $extraFieldsModel->getGroups(true); // Fetch entire extra field group list
        $group[] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'), 'id', 'name');
        $group = array_merge($group, $groups);
        $lists['extraFieldsGroup'] = JHTML::_('select.genericlist', $group, 'extraFieldsGroup', 'class="inputbox" size="1" ', 'id', 'name', $category->extraFieldsGroup);

        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            $languages = JHTML::_('contentlanguage.existing', true, true);
            $lists['language'] = JHTML::_('select.genericlist', $languages, 'language', '', 'value', 'text', $category->language);
        }

        // Plugin Events
        JPluginHelper::importPlugin('k2');
        $dispatcher = JDispatcher::getInstance();
        $K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(&$category, 'category'));
        $this->assignRef('K2Plugins', $K2Plugins);

        // Parameters
        $params = JComponentHelper::getParams('com_k2');
        $this->assignRef('params', $params);

        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            jimport('joomla.form.form');
            $form = JForm::getInstance('categoryForm', JPATH_COMPONENT_ADMINISTRATOR.'/models/category.xml');
            $values = array('params' => json_decode($category->params));
            $form->bind($values);
            $inheritFrom = (isset($values['params']->inheritFrom)) ? $values['params']->inheritFrom : 0;
        } else {
            $form = new JParameter('', JPATH_COMPONENT_ADMINISTRATOR.'/models/category.xml');
            $form->loadINI($category->params);
            $inheritFrom = $form->get('inheritFrom');
        }
        $this->assignRef('form', $form);

        $categories[0] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'));
        $lists['inheritFrom'] = JHTML::_('select.genericlist', $categories, 'params[inheritFrom]', 'class="inputbox"', 'value', 'text', $inheritFrom);

        $this->assignRef('lists', $lists);

        // Disable Joomla menu
        JRequest::setVar('hidemainmenu', 1);

        // Toolbar
        (JRequest::getInt('cid')) ? $title = JText::_('K2_EDIT_CATEGORY') : $title = JText::_('K2_ADD_CATEGORY');
        JToolBarHelper::title($title, 'k2.png');

        JToolBarHelper::apply();
        JToolBarHelper::save();
        $saveNewIcon = version_compare(JVERSION, '2.5.0', 'ge') ? 'save-new.png' : 'save.png';
        JToolBarHelper::custom('saveAndNew', $saveNewIcon, 'save_f2.png', 'K2_SAVE_AND_NEW', false);
        JToolBarHelper::cancel();

        parent::display($tpl);
    }
}
