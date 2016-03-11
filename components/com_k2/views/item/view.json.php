<?php
/**
 * @version     2.7.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class K2ViewItem extends K2View
{

    function display($tpl = null)
    {

        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $document = JFactory::getDocument();
        if (K2_JVERSION == '15')
        {
            $document->setMimeEncoding('application/json');
            $document->setType('json');
        }
        $params = K2HelperUtilities::getParams('com_k2');
        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');

        $db = JFactory::getDBO();
        $jnow = JFactory::getDate();
        $now =  K2_JVERSION == '15'?$jnow->toMySQL():$jnow->toSql();
        $nullDate = $db->getNullDate();

        // Get item
        $model = $this->getModel();
        $item = $model->getData();

        // Does the item exists?
        if (!is_object($item) || !$item->id)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        // Override some params because we want to show all elements in JSON
        $itemParams = class_exists('JParameter') ? new JParameter($item->params) : new JRegistry($item->params);
        $itemParams->set('itemIntroText', true);
        $itemParams->set('itemFullText', true);
        $itemParams->set('itemTags', true);
        $itemParams->set('itemExtraFields', true);
        $itemParams->set('itemAttachments', true);
        $itemParams->set('itemRating', true);
        $itemParams->set('itemAuthor', true);
        $itemParams->set('itemImageGallery', true);
        $itemParams->set('itemVideo', true);
        $item->params = $itemParams->toString();

        // Prepare item
        $item = $model->prepareItem($item, $view, $task);

        // Plugins
        $item = $model->execPlugins($item, $view, $task);

        // Access check
        if (K2_JVERSION != '15')
        {
            if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($item->category->access, $user->getAuthorisedViewLevels()))
            {
               JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }
        else
        {
            if ($item->access > $user->get('aid', 0) || $item->category->access > $user->get('aid', 0))
            {
                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }

        // Published check
        if (!$item->published || $item->trash)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_up != $nullDate && $item->publish_up > $now)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if ($item->publish_down != $nullDate && $item->publish_down < $now)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }

        if (!$item->category->published || $item->category->trash)
        {
            JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
        }
        
        // Increase hits counter
        $model->hit($item->id);

        // Set default image
        K2HelperUtilities::setDefaultImage($item, $view);

        // Build the output object
        $row = $model->prepareJSONItem($item);

        // Output
        $response = new stdClass();

        // Site
        $response->site = new stdClass();
        $uri = JURI::getInstance();
        $response->site->url = $uri->toString(array('scheme', 'host', 'port'));
        $config = JFactory::getConfig();
        $response->site->name = K2_JVERSION == '30' ? $config->get('sitename') : $config->getValue('config.sitename');

        $response->item = $row;

        $json = json_encode($response);
        $callback = JRequest::getCmd('callback');
        if ($callback)
        {
            $document->setMimeEncoding('application/javascript');
            echo $callback.'('.$json.')';
        }
        else
        {
            echo $json;
        }
    }

}
