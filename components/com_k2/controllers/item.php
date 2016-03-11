<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

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
		if ($user->guest)
		{
			$cache = true;
		}
		else
		{
			$cache = true;
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
			$row = JTable::getInstance('K2Item', 'Table');
			$row->load(JRequest::getInt('id'));
			if (K2HelperPermissions::canEditItem($row->created_by, $row->catid))
			{
				$cache = false;
			}
			$params = K2HelperUtilities::getParams('com_k2');
			if ($row->created_by == $user->id && $params->get('inlineCommentsModeration'))
			{
				$cache = false;
			}
			if ($row->access > 0)
			{
				$cache = false;
			}
			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($row->catid);
			if ($category->access > 0)
			{
				$cache = false;
			}
			if ($params->get('comments') && $document->getType() == 'html')
			{
				$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
				$profile = $itemListModel->getUserProfile($user->id);
				$script = "
\$K2(document).ready(function() {
\$K2('#userName').val(".json_encode($user->name).").attr('disabled', 'disabled');
\$K2('#commentEmail').val('".$user->email."').attr('disabled', 'disabled');";
				if (is_object($profile) && $profile->url)
				{
					$script .= " \$K2('#commentURL').val('".htmlspecialchars($profile->url, ENT_QUOTES, 'UTF-8')."').attr('disabled', 'disabled');";
				}
				$script .= " });";
				$document->addScriptDeclaration($script);
			}
		}

		if (K2_JVERSION != '15')
		{
			$urlparams['id'] = 'INT';
			$urlparams['print'] = 'INT';
			$urlparams['lang'] = 'CMD';
			$urlparams['Itemid'] = 'INT';
		}
		parent::display($cache, $urlparams);
	}

	function edit()
	{
		JRequest::setVar('tmpl', 'component');
		$mainframe = JFactory::getApplication();
		$params = K2HelperUtilities::getParams('com_k2');
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);

		$document = JFactory::getDocument();

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			JHtml::_('behavior.framework');
		}
		else
		{
			JHTML::_('behavior.mootools');
		}

		// CSS
		$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v=2.7.0');
		$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.frontend.css?v=2.7.0');
		$document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
		$document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');

		// JS
		K2HelperHTML::loadjQuery(true);
		$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v=2.7.0&amp;sitepath='.JURI::root(true).'/');

		$this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$view = $this->getView('item', 'html');
		$view->setLayout('itemform');

		if ($params->get('category'))
		{
			JRequest::setVar('catid', $params->get('category'));
		}

		// Look for template files in component folders
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates');
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.'default');

		// Look for overrides in template folder (K2 template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (Joomla! template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'default');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2');

		// Look for specific K2 theme files
		if ($params->get('theme'))
		{
			$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.$params->get('theme'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.$params->get('theme'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.$params->get('theme'));
		}
		$view->display();
	}

	function add()
	{
		$this->edit();
	}

	function cancel()
	{
		$this->setRedirect(JURI::root(true));
		return false;
	}

	function save()
	{
		$mainframe = JFactory::getApplication();
		JRequest::checkToken() or jexit('Invalid Token');
		JRequest::setVar('tmpl', 'component');
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;
		$model->save(true);
		$mainframe->close();

	}

	function deleteAttachment()
	{

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;
		$model->deleteAttachment();
	}

	function tag()
	{

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'tag.php');
		$model = new K2ModelTag;
		$model->addTag();
	}

	function tags()
	{
		$user = JFactory::getUser();
		if($user->guest)
		{
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'tag.php');
		$model = new K2ModelTag;
		$model->tags();
	}

	function download()
	{

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;
		$model->download();
	}

	function extraFields()
	{
		$mainframe = JFactory::getApplication();
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);
		$itemID = JRequest::getInt('id', NULL);

		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		$catid = JRequest::getInt('cid');
		$category = JTable::getInstance('K2Category', 'Table');
		$category->load($catid);

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extrafield.php');
		$extraFieldModel = new K2ModelExtraField;

		$extraFields = $extraFieldModel->getExtraFieldsByGroup($category->extraFieldsGroup);

		$output = '<table class="admintable" id="extraFields">';
		$counter = 0;
		if (count($extraFields))
		{
			foreach ($extraFields as $extraField)
			{

				if ($extraField->type == 'header')
				{
					$output .= '<tr><td colspan="2" ><h4 class="k2ExtraFieldHeader">'.$extraField->name.'</h4></td></tr>';
				}
				else
				{
					$output .= '<tr><td align="right" class="key"><label for="K2ExtraField_'.$extraField->id.'">'.$extraField->name.'</label></td>';
					$output .= '<td>'.$extraFieldModel->renderExtraField($extraField, $itemID).'</td></tr>';

				}
				$counter++;
			}
		}
		$output .= '</table>';

		if ($counter == 0)
			$output = JText::_('K2_THIS_CATEGORY_DOESNT_HAVE_ASSIGNED_EXTRA_FIELDS');

		echo $output;
		$mainframe->close();
	}

	function checkin()
	{

		$model = $this->getModel('item');
		$model->checkin();
	}

	function vote()
	{

		$model = $this->getModel('item');
		$model->vote();
	}

	function getVotesNum()
	{

		$model = $this->getModel('item');
		$model->getVotesNum();
	}

	function getVotesPercentage()
	{

		$model = $this->getModel('item');
		$model->getVotesPercentage();
	}

	function comment()
	{

		$model = $this->getModel('item');
		$model->comment();
	}

	function resetHits()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		JRequest::setVar('tmpl', 'component');
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;
		$model->resetHits();

	}

	function resetRating()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		JRequest::setVar('tmpl', 'component');
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;
		$model->resetRating();

	}

	function media()
	{
		K2HelperHTML::loadjQuery(true, true);
		JRequest::setVar('tmpl', 'component');
		$params = K2HelperUtilities::getParams('com_k2');
		$document = JFactory::getDocument();
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);
		$user = JFactory::getUser();
		if ($user->guest)
		{
			$uri = JFactory::getURI();
			if (K2_JVERSION != '15')
			{
				$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());
			}
			else
			{
				$url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
			}
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
			$mainframe->redirect(JRoute::_($url, false));
		}

		// CSS
		$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v=2.7.0');

		// JS
		K2HelperHTML::loadjQuery(true);
		$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v=2.7.0&amp;sitepath='.JURI::root(true).'/');
		$this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
		$view = $this->getView('media', 'html');
		$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'media'.DS.'tmpl');
		$view->setLayout('default');
		$view->display();

	}

	function connector()
	{
		JRequest::setVar('tmpl', 'component');
		$user = JFactory::getUser();
		if ($user->guest)
		{
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.'media.php');
		$controller = new K2ControllerMedia();
		$controller->connector();

	}

	function users()
	{

		$itemID = JRequest::getInt('itemID');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		$item = JTable::getInstance('K2Item', 'Table');
		$item->load($itemID);
		if (!K2HelperPermissions::canAddItem() && !K2HelperPermissions::canEditItem($item->created_by, $item->catid))
		{
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}
		$K2Permissions = K2Permissions::getInstance();
		if (!$K2Permissions->permissions->get('editAll'))
		{
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}
		JRequest::setVar('tmpl', 'component');
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);

		$document = JFactory::getDocument();

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			JHtml::_('behavior.framework');
		}
		else
		{
			JHTML::_('behavior.mootools');
		}

		// CSS
		$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.css?v=2.7.0');

		// JS
		K2HelperHTML::loadjQuery(true);
		$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.js?v=2.7.0&amp;sitepath='.JURI::root(true).'/');

		$this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$view = $this->getView('users', 'html');
		$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'users'.DS.'tmpl');
		$view->setLayout('element');
		$view->display();

	}

}
