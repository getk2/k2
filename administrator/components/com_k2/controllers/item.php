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
        JRequest::setVar('view', 'item');
        parent::display();
    }

    public function save()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('item');
        $model->save();
    }

    public function apply()
    {
        $this->save();
    }

    public function cancel()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('item');
        $model->cancel();
    }

    public function deleteAttachment()
    {
        $model = $this->getModel('item');
        $model->deleteAttachment();
    }

    public function tag()
    {
        $model = $this->getModel('tag');
        $model->addTag();
    }

    public function tags()
    {
        $user = JFactory::getUser();
        if ($user->guest) {
            JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
        }
        $model = $this->getModel('tag');
        $model->tags();
    }

    public function download()
    {
        $model = $this->getModel('item');
        $model->download();
    }

    public function extraFields()
    {
        $app = JFactory::getApplication();
        $id = JRequest::getInt('id', null);

        $categoryModel = $this->getModel('category');
        $category = $categoryModel->getData();

        $extraFieldModel = $this->getModel('extraField');
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

    public function resetHits()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('item');
        $model->resetHits();
    }

    public function resetRating()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $model = $this->getModel('item');
        $model->resetRating();
    }
}
