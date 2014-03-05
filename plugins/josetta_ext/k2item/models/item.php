<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

class JosettaK2ModelItem extends JModelLegacy
{

    public function save($item, $front = false)
    {

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.archive');
        require_once (JPATH_ADMINISTRATOR.'/components/com_k2/lib/class.upload.php');
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $row = JTable::getInstance('K2Item', 'Table');
        $params = JComponentHelper::getParams('com_k2');
        $nullDate = $db->getNullDate();

        if (!$row->bind($item))
        {
            $this->setError($row->getError());
            return false;
        }

        $row->catid = (int)$row->catid;

        if ($front && $row->id == NULL)
        {
            JLoader::register('K2HelperPermissions', JPATH_SITE.'/components/com_k2/helpers/permissions.php');
            if (!K2HelperPermissions::canAddItem($row->catid))
            {
                $this->setError(JText::_('K2_YOU_ARE_NOT_ALLOWED_TO_POST_TO_THIS_CATEGORY_SAVE_FAILED'));
                return false;
            }
        }

        ($row->id) ? $isNew = false : $isNew = true;

        if ($params->get('xssFiltering'))
        {
            $filter = new JFilterInput( array(), array(), 1, 1, 0);
            $item['articletext'] = $filter->clean($item['articletext']);
        }
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        $tagPos = preg_match($pattern, $item['articletext']);
        if ($tagPos == 0)
        {
            $row->introtext = $item['articletext'];
            $row->fulltext = '';
        }
        else
        {
            list($row->introtext, $row->fulltext) = preg_split($pattern, $item['articletext'], 2);
        }

        if ($row->id)
        {
            $datenow = JFactory::getDate();
            $row->modified = $datenow->toSql();
            $row->modified_by = $user->get('id');
        }
        else
        {
            $row->ordering = $row->getNextOrder("catid = {$row->catid} AND trash = 0");
            if ($row->featured)
                $row->featured_ordering = $row->getNextOrder("featured = 1 AND trash = 0", 'featured_ordering');
        }

        $row->created_by = $row->created_by ? $row->created_by : $user->get('id');

        if ($front)
        {
            $K2Permissions = K2Permissions::getInstance();
            if (!$K2Permissions->permissions->get('editAll'))
            {
                $row->created_by = $user->get('id');
            }
        }

        if ($row->created && strlen(trim($row->created)) <= 10)
        {
            $row->created .= ' 00:00:00';
        }

        $config = JFactory::getConfig();
        $tzoffset = $config->get('config.offset');
        $date = JFactory::getDate($row->created, $tzoffset);
        $row->created = $date->toSql();

        if (strlen(trim($row->publish_up)) <= 10)
        {
            $row->publish_up .= ' 00:00:00';
        }

        $date = JFactory::getDate($row->publish_up, $tzoffset);
        $row->publish_up = $date->toSql();

        if (trim($row->publish_down) == JText::_('K2_NEVER') || trim($row->publish_down) == '')
        {
            $row->publish_down = $nullDate;
        }
        else
        {
            if (strlen(trim($row->publish_down)) <= 10)
            {
                $row->publish_down .= ' 00:00:00';
            }
            $date = JFactory::getDate($row->publish_down, $tzoffset);
            $row->publish_down = $date->toSql();
        }

        if (!$row->check())
        {
            $this->setError($row->getError());
            return false;
        }

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('k2');
        $result = $dispatcher->trigger('onBeforeK2Save', array(&$row, $isNew));
        if (in_array(false, $result, true))
        {
            $this->setError($row->getError());
            return false;
        }

        //Trigger the finder before save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderBeforeSave', array('com_k2.item', $row, $isNew));

        if (!$row->store())
        {
            $this->setError($row->getError());
            return false;
        }

        if (!$params->get('disableCompactOrdering'))
        {
            $row->reorder("catid = {$row->catid} AND trash = 0");
        }
        if ($row->featured && !$params->get('disableCompactOrdering'))
        {
            $row->reorder("featured = 1 AND trash = 0", 'featured_ordering');
        }

        // Image copy
        $src = md5("Image".$item['ref_id']);
        $target = md5("Image".$row->id);
        $sizes = array('XL', 'L', 'M', 'S', 'XS');
        $savepath = JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache';
        foreach($sizes as $size)
        {
            if(JFile::exists($savepath.DS.$src.'_'.$size.'.jpg') && !JFile::exists($savepath.DS.$target.'_'.$size.'.jpg'))
            {
                JFile::copy($savepath.DS.$src.'_'.$size.'.jpg', $savepath.DS.$target.'_'.$size.'.jpg');
            }
        }

        //Extra fields
        $objects = array();
        $variables = JRequest::get('post', 4);
        foreach ($variables as $key => $value)
        {
            if (( bool )JString::stristr($key, 'K2ExtraField_'))
            {
                $object = new JObject;
                $object->set('id', JString::substr($key, 13));
                $object->set('value', $value);
                unset($object->_errors);
                $objects[] = $object;
            }
        }

        $csvFiles = empty($item['files']) ? array() : $item['files'];
        foreach ($csvFiles as $key => $file)
        {
            if (( bool )JString::stristr($key, 'K2ExtraField_'))
            {
                $object = new JObject;
                $object->set('id', JString::substr($key, 13));
                $csvFile = $file['tmp_name'][0];
                if (!empty($csvFile) && JFile::getExt($file['name'][0]) == 'csv')
                {
                    $handle = @fopen($csvFile, 'r');
                    $csvData = array();
                    while (($data = fgetcsv($handle, 1000)) !== FALSE)
                    {
                        $csvData[] = $data;
                    }
                    fclose($handle);
                    $object->set('value', $csvData);
                }
                else
                {
                    require_once (JPATH_ADMINISTRATOR.'/components/com_k2/lib/JSON.php');
                    $json = new Services_JSON;
                    $object->set('value', $json->decode($item['K2CSV_'.$object->id]));
                    if (!empty($item['K2ResetCSV_'.$object->id])) {
                        $object->set('value', null);
                    }
                }
                unset($object->_errors);
                $objects[] = $object;
            }
        }

        require_once (JPATH_ADMINISTRATOR.'/components/com_k2/lib/JSON.php');
        $json = new Services_JSON;
        $row->extra_fields = $json->encode($objects);

        $row->extra_fields_search = '';

        foreach ($objects as $object)
        {
            $row->extra_fields_search .= $this->getSearchValue($object->id, $object->value);
            $row->extra_fields_search .= ' ';
        }

        $query = "DELETE FROM #__k2_tags_xref WHERE itemID={intval($row->id)}";
        $db->setQuery($query);
        $db->query();

        if (!empty($item['tags']))
        {
            $tags = array_unique($item['tags']);
            foreach ($tags as $tag)
            {
                $tag = JString::str_ireplace('-', '', $tag);
                $query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($tag);
                $db->setQuery($query);
                $tagID = $db->loadResult();
                if ($tagID)
                {
                    $query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tagID)}, {intval($row->id)})";
                    $db->setQuery($query);
                    $db->query();
                }
                else
                {
                    $K2Tag = JTable::getInstance('K2Tag', 'Table');
                    $K2Tag->name = $tag;
                    $K2Tag->published = 1;
                    $K2Tag->check();
                    $K2Tag->store();
                    $query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($K2Tag->id)}, {intval($row->id)})";
                    $db->setQuery($query);
                    $db->query();
                }
            }
        }

        //Image
        if ((int)$params->get('imageMemoryLimit'))
        {
            ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
        }

        if ($front)
        {
            if (!K2HelperPermissions::canPublishItem($row->catid) && $row->published)
            {
                $row->published = 0;
                $this->setError(JText::_('K2_YOU_DONT_HAVE_THE_PERMISSION_TO_PUBLISH_ITEMS'));
            }
        }

        $query = "UPDATE #__k2_items SET video_caption = ".$db->Quote($row->video_caption).", video_credits = ".$db->Quote($row->video_credits).", ";

        if (!is_null($row->video))
        {
            $query .= " video = ".$db->Quote($row->video).", ";
        }
        if (!is_null($row->gallery))
        {
            $query .= " gallery = ".$db->Quote($row->gallery).", ";
        }
        $query .= " extra_fields = ".$db->Quote($row->extra_fields).", extra_fields_search = ".$db->Quote($row->extra_fields_search)." , published = ".$db->Quote($row->published)." WHERE id = ".$row->id;
        $db->setQuery($query);

        if (!$db->query())
        {
            $this->setError($db->getErrorMsg());
            return false;
        }

        $row->checkin();

        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        //$dispatcher->trigger('onAfterK2Save', array(&$row, $isNew));
        $dispatcher->trigger('onContentAfterSave', array(&$row, $isNew));

        //Trigger the finder after save event
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('finder');
        $results = $dispatcher->trigger('onFinderAfterSave', array('com_k2.item', $row, $isNew));

        return $row->id;
    }

    protected function getSearchValue($id, $currentValue)
    {

        $row = JTable::getInstance('K2ExtraField', 'Table');
        $row->load($id);

        require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
        $json = new Services_JSON;
        $jsonObject = $json->decode($row->value);

        $value = '';
        if ($row->type == 'textfield' || $row->type == 'textarea')
        {
            $value = $currentValue;
        }
        else if ($row->type == 'multipleSelect')
        {
            foreach ($jsonObject as $option)
            {
                if (in_array($option->value, $currentValue))
                    $value .= $option->name.' ';
            }
        }
        else if ($row->type == 'link')
        {
            $value .= $currentValue[0].' ';
            $value .= $currentValue[1].' ';
        }
        else if ($row->type == 'labels')
        {
            $parts = explode(',', $currentValue);
            $value .= implode(' ', $parts);
        }
        else
        {
            foreach ($jsonObject as $option)
            {
                if ($option->value == $currentValue)
                    $value .= $option->name;
            }
        }
        return $value;
    }

}
