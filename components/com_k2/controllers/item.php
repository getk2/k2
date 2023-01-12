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

jimport('joomla.application.component.controller');

class K2ControllerItem extends K2Controller
{
    public function display($cachable = false, $urlparams = array())
    {
        $model = $this->getModel('itemlist');
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('item', $viewType);
        $view->setModel($model);
        JRequest::setVar('view', 'item');
        $user = JFactory::getUser();
        if ($user->guest) {
            $cache = true;
        } else {
            $cache = true;
            JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load(JRequest::getInt('id'));
            if (K2HelperPermissions::canEditItem($row->created_by, $row->catid)) {
                $cache = false;
            }
            $params = K2HelperUtilities::getParams('com_k2');
            if ($row->created_by == $user->id && $params->get('inlineCommentsModeration')) {
                $cache = false;
            }
            if ($row->access > 0) {
                $cache = false;
            }
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load($row->catid);
            if ($category->access > 0) {
                $cache = false;
            }
            if ($params->get('comments') && $document->getType() == 'html') {
                $itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
                $profile = $itemListModel->getUserProfile($user->id);
                $script = "
                    \$K2(document).ready(function() {
                        \$K2('#userName').val(".json_encode($user->name).").attr('disabled', 'disabled');
                        \$K2('#commentEmail').val('".$user->email."').attr('disabled', 'disabled');
                ";
                if (is_object($profile) && $profile->url) {
                    $script .= "
                        \$K2('#commentURL').val('".htmlspecialchars($profile->url, ENT_QUOTES, 'UTF-8')."').attr('disabled', 'disabled');
                    ";
                }
                $script .= "
                    });
                ";
                $document->addScriptDeclaration($script);
            }
        }

        if (K2_JVERSION != '15') {
            $urlparams['id'] = 'INT';
            $urlparams['print'] = 'INT';
            $urlparams['lang'] = 'CMD';
            $urlparams['Itemid'] = 'INT';
            $urlparams['m'] = 'INT';
            $urlparams['amp'] = 'INT';
            $urlparams['tmpl'] = 'CMD';
            $urlparams['template'] = 'CMD';
        }
        parent::display($cache, $urlparams);
    }

    public function edit()
    {
        JRequest::setVar('tmpl', 'component');
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $params = K2HelperUtilities::getParams('com_k2');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);

        K2HelperHTML::loadHeadIncludes(true, true, true);

        // CSS
        $document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
        $document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');

        $this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.'/models');
        $this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.'/views');
        $view = $this->getView('item', 'html');
        $view->frontendTheme = $params->get('theme');
        $view->setLayout('itemform');

        if ($params->get('category')) {
            JRequest::setVar('catid', $params->get('category'));
        }

        $view->display();
    }

    public function add()
    {
        $this->edit();
    }

    public function cancel()
    {
        $this->setRedirect(JURI::root(true));
        return false;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        JRequest::checkToken() or jexit('Invalid Token');
        JRequest::setVar('tmpl', 'component');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/item.php');
        $model = new K2ModelItem;
        $model->save(true);
        $app->close();
    }

    public function deleteAttachment()
    {
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/item.php');
        $model = new K2ModelItem;
        $model->deleteAttachment();
    }

    public function tag()
    {
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/tag.php');
        $model = new K2ModelTag;
        $model->addTag();
    }

    public function tags()
    {
        $user = JFactory::getUser();
        if ($user->guest) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/tag.php');
        $model = new K2ModelTag;
        $model->tags();
    }

    public function download()
    {
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/item.php');
        $model = new K2ModelItem;
        $model->download();
    }

    public function extraFields()
    {
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);

        $app = JFactory::getApplication();
        $id = JRequest::getInt('id', null);

        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/category.php');
        $categoryModel = new K2ModelCategory;
        $category = $categoryModel->getData();

        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/extrafield.php');
        $extraFieldModel = new K2ModelExtraField;
        $extraFields = $extraFieldModel->getExtraFieldsByGroup($category->extraFieldsGroup);

        if (!empty($extraFields) && count($extraFields)) {
            $output = '<div id="extraFields">';
            foreach ($extraFields as $extraField) {
                if ($extraField->type == 'header') {
                    $output .= '
                    <div class="itemAdditionalField fieldIs'.ucfirst($extraField->type).'">
                        <h4>'.$extraField->name.'</h4>
                    </div>
                    ';
                } else {
                    $output .= '
                    <div class="itemAdditionalField fieldIs'.ucfirst($extraField->type).'">
                        <div class="itemAdditionalValue">
                            <label for="K2ExtraField_'.$extraField->id.'">'.$extraField->name.'</label>
                        </div>
                        <div class="itemAdditionalData">
                            '.$extraFieldModel->renderExtraField($extraField, $id).'
                        </div>
                    </div>
                    ';
                }
            }
            $output .= '</div>';
        } else {
            $output = '
                <div class="k2-generic-message">
                    <h3>'.JText::_('K2_NOTICE').'</h3>
                    <p>'.JText::_('K2_THIS_CATEGORY_DOESNT_HAVE_ASSIGNED_EXTRA_FIELDS').'</p>
                </div>
            ';
        }

        echo $output;

        $app->close();
    }

    public function checkin()
    {
        $model = $this->getModel('item');
        $model->checkin();
    }

    public function vote()
    {
        $model = $this->getModel('item');
        $model->vote();
    }

    public function getVotesNum()
    {
        $model = $this->getModel('item');
        $model->getVotesNum();
    }

    public function getVotesPercentage()
    {
        $model = $this->getModel('item');
        $model->getVotesPercentage();
    }

    public function comment()
    {
        $model = $this->getModel('item');
        $model->comment();
    }

    public function resetHits()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        JRequest::setVar('tmpl', 'component');
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/item.php');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $model = new K2ModelItem;
        $model->resetHits();
    }

    public function resetRating()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        JRequest::setVar('tmpl', 'component');
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/item.php');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $model = new K2ModelItem;
        $model->resetRating();
    }

    public function media()
    {
        JRequest::setVar('tmpl', 'component');
        $params = K2HelperUtilities::getParams('com_k2');
        $document = JFactory::getDocument();
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);
        $user = JFactory::getUser();
        if ($user->guest) {
            $uri = JFactory::getURI();
            if (K2_JVERSION != '15') {
                $url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());
            } else {
                $url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
            }
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
            $app->redirect(JRoute::_($url, false));
        }

        K2HelperHTML::loadHeadIncludes(false, true, true);

        $this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.'/views');
        $view = $this->getView('media', 'html');
        $view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.'/views/media/tmpl');
        $view->setLayout('default');
        $view->display();
    }

    public function connector()
    {
        JRequest::setVar('tmpl', 'component');
        $user = JFactory::getUser();
        if ($user->guest) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        require_once(JPATH_COMPONENT_ADMINISTRATOR.'/controllers/media.php');
        $controller = new K2ControllerMedia();
        $controller->connector();
    }

    public function users()
    {
        $itemID = JRequest::getInt('itemID');
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        $item = JTable::getInstance('K2Item', 'Table');
        $item->load($itemID);
        if (!K2HelperPermissions::canAddItem() && !K2HelperPermissions::canEditItem($item->created_by, $item->catid)) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        $K2Permissions = K2Permissions::getInstance();
        if (!$K2Permissions->permissions->get('editAll')) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        JRequest::setVar('tmpl', 'component');
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $language = JFactory::getLanguage();
        $language->load('com_k2', JPATH_ADMINISTRATOR);

        $document = JFactory::getDocument();

        K2HelperHTML::loadHeadIncludes(true, true, true);

        $this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.'/views');
        $this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.'/models');
        $view = $this->getView('users', 'html');
        $view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.'/views/users/tmpl');
        $view->display();
    }
}
