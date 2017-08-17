<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.'/tables');

class K2ModelCategories extends K2Model
{
    function getData()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();
        $limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
        $limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
        $search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\"\-_]/u', '', $search));
        $filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.ordering', 'cmd');
        $filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_trash = $application->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
        $filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
        $language = $application->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');
        $filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');

        $query = "SELECT c.*, g.name AS groupname, exfg.name as extra_fields_group FROM #__k2_categories as c LEFT JOIN #__groups AS g ON g.id = c.access LEFT JOIN #__k2_extra_fields_groups AS exfg ON exfg.id = c.extraFieldsGroup WHERE c.id>0";

        if (!$filter_trash)
        {
            $query .= " AND c.trash=0";
        }

		if ($search)
		{

			// Detect exact search phrase using double quotes in search string
			if(substr($search, 0, 1)=='"' && substr($search, -1)=='"')
			{
				$exact = true;
			}
			else
			{
				$exact = false;
			}

			// Now completely strip double quotes
			$search = trim(str_replace('"', '', $search));

			// Escape remaining string
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);

			// Full phrase or set of words
			if(strpos($escaped, ' ')!==false && !$exact)
			{
				$escaped=explode(' ', $escaped);
				$quoted = array();
				foreach($escaped as $key=>$escapedWord)
				{
					$quoted[] = $db->Quote('%'.$escapedWord.'%', false);
				}
				if ($params->get('adminSearch') == 'full')
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND ( ".
							"LOWER(c.name) LIKE ".$quotedWord." ".
							"OR LOWER(c.description) LIKE ".$quotedWord." ".
							" )";
					}
				}
				else
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND LOWER(c.name) LIKE ".$quotedWord;
					}
				}
			}
			// Single word or exact phrase to search for (wrapped in double quotes in the search block)
			else
			{
				$quoted = $db->Quote('%'.$escaped.'%', false);

				if ($params->get('adminSearch') == 'full')
				{
					$query .= " AND ( ".
						"LOWER(c.name) LIKE ".$quoted." ".
						"OR LOWER(c.description) LIKE ".$quoted." ".
						" )";
				}
				else
				{
					$query .= " AND LOWER(c.name) LIKE ".$quoted;
				}
			}
		}

        if ($filter_state > -1)
        {
            $query .= " AND c.published={$filter_state}";
        }
        if ($language)
        {
            $query .= " AND c.language = ".$db->Quote($language);
        }

        if ($filter_category)
        {
            K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
            $ItemlistModel = K2Model::getInstance('Itemlist', 'K2Model');
            $tree = $ItemlistModel->getCategoryTree($filter_category);
            $query .= " AND c.id IN (".implode(',', $tree).")";
        }

        $query .= " ORDER BY {$filter_order} {$filter_order_Dir}";

        if (K2_JVERSION != '15')
        {
            $query = JString::str_ireplace('#__groups', '#__viewlevels', $query);
            $query = JString::str_ireplace('g.name AS groupname', 'g.title AS groupname', $query);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if (K2_JVERSION != '15')
        {
            foreach ($rows as $row)
            {
                $row->parent_id = $row->parent;
                $row->title = $row->name;
            }
        }
        $categories = array();

        if ($search)
        {
            foreach ($rows as $row)
            {
                $row->treename = $row->name;
                $categories[] = $row;
            }

        }
        else
        {
            if ($filter_category)
            {
                $db->setQuery('SELECT parent FROM #__k2_categories WHERE id = '.$filter_category);
                $root = $db->loadResult();
            }
            else if($language && count($categories))
            {
            	$root = $categories[0]->parent;
            }
            else
            {
                $root = 0;
            }
            $categories = $this->indentRows($rows, $root);
        }
        if (isset($categories))
        {
            $total = count($categories);
        }
        else
        {
            $total = 0;
        }
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $categories = @array_slice($categories, $pageNav->limitstart, $pageNav->limit);
        foreach ($categories as $category)
        {
            $category->parameters = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
            if ($category->parameters->get('inheritFrom'))
            {
                $db->setQuery("SELECT name FROM #__k2_categories WHERE id = ".(int)$category->parameters->get('inheritFrom'));
                $category->inheritFrom = $db->loadResult();
            }
            else
            {
                $category->inheritFrom = '';
            }
        }
        return $categories;
    }

    function getTotal()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $db = JFactory::getDbo();
        $limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
        $limitstart = $application->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
        $search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
        $search = JString::strtolower($search);
        $search = trim(preg_replace('/[^\p{L}\p{N}\s\"\-_]/u', '', $search));
        $filter_trash = $application->getUserStateFromRequest($option.$view.'filter_trash', 'filter_trash', 0, 'int');
        $filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
        $language = $application->getUserStateFromRequest($option.$view.'language', 'language', '', 'string');
        $filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');

        $query = "SELECT COUNT(*) FROM #__k2_categories WHERE id>0";

        if (!$filter_trash)
        {
            $query .= " AND trash=0";
        }

		if ($search)
		{
			// Detect exact search phrase using double quotes in search string
			if(substr($search, 0, 1)=='"' && substr($search, -1)=='"')
			{
				$exact = true;
			}
			else
			{
				$exact = false;
			}

			// Now completely strip double quotes
			$search = trim(str_replace('"', '', $search));

			// Escape remaining string
			$escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);

			// Full phrase or set of words
			if(strpos($escaped, ' ')!==false && !$exact)
			{
				$escaped=explode(' ', $escaped);
				$quoted = array();
				foreach($escaped as $key=>$escapedWord)
				{
					$quoted[] = $db->Quote('%'.$escapedWord.'%', false);
				}
				if ($params->get('adminSearch') == 'full')
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND ( ".
							"LOWER(name) LIKE ".$quotedWord." ".
							"OR LOWER(description) LIKE ".$quotedWord." ".
							" )";
					}
				}
				else
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND LOWER(name) LIKE ".$quotedWord;
					}
				}
			}
			// Single word or exact phrase to search for (wrapped in double quotes in the search block)
			else
			{
				$quoted = $db->Quote('%'.$escaped.'%', false);

				if ($params->get('adminSearch') == 'full')
				{
					$query .= " AND ( ".
						"LOWER(name) LIKE ".$quoted." ".
						"OR LOWER(description) LIKE ".$quoted." ".
						" )";
				}
				else
				{
					$query .= " AND LOWER(name) LIKE ".$quoted;
				}
			}
		}

        if ($filter_state > -1)
        {
            $query .= " AND published={$filter_state}";
        }

        if ($language)
        {
            $query .= " AND language = ".$db->Quote($language);
        }

        if ($filter_category)
        {
            K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
            $ItemlistModel = K2Model::getInstance('Itemlist', 'K2Model');
            $tree = $ItemlistModel->getCategoryTree($filter_category);
            $query .= " AND id IN (".implode(',', $tree).")";
        }

        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;

    }

    function indentRows(&$rows, $root = 0)
    {
        $children = array();
        if (count($rows))
        {
            foreach ($rows as $v)
            {
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $categories = JHTML::_('menu.treerecurse', $root, '', array(), $children);
        return $categories;
    }

    function publish()
    {
        $application = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2Category', 'Table');
            $row->load($id);
			$row->published = 1;
            $row->store();
        }
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onFinderChangeState', array('com_k2.category', $cid, 1));
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=categories&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=categories');
		}
    }

    function unpublish()
    {
        $application = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2Category', 'Table');
            $row->load($id);
            $row->published = 0;
			$row->store();
        }
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onFinderChangeState', array('com_k2.category', $cid, 0));
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=categories&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=categories');
		}
    }

    function saveorder()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');
        $total = count($cid);
        $order = JRequest::getVar('order', array(0), 'post', 'array');
        JArrayHelper::toInteger($order, array(0));
        $groupings = array();
        for ($i = 0; $i < $total; $i++)
        {
        	$row = JTable::getInstance('K2Category', 'Table');
            $row->load(( int )$cid[$i]);
            $groupings[] = $row->parent;
            if ($row->ordering != $order[$i])
            {
                $row->ordering = $order[$i];
                if (!$row->store())
                {
                    JError::raiseError(500, $db->getErrorMsg());
                }
            }
        }
        if (!$params->get('disableCompactOrdering'))
        {
            $groupings = array_unique($groupings);
            foreach ($groupings as $group)
            {
            	$row = JTable::getInstance('K2Category', 'Table');
                $row->reorder('parent = '.( int )$group.' AND trash=0');
            }
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        return true;
    }

    function orderup()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Category', 'Table');
        $row->load($cid[0]);
        $row->move(-1, 'parent = '.$row->parent.' AND trash=0');
        if (!$params->get('disableCompactOrdering'))
            $row->reorder('parent = '.(int)$row->parent.' AND trash=0');
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
		$application->enqueueMessage($msg);
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=categories&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=categories');
		}
	}

    function orderdown()
    {
        $application = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_k2');
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Category', 'Table');
        $row->load($cid[0]);
        $row->move(1, 'parent = '.$row->parent.' AND trash=0');
        if (!$params->get('disableCompactOrdering'))
            $row->reorder('parent = '.(int)$row->parent.' AND trash=0');
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ORDERING_SAVED');
		$application->enqueueMessage($msg);
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=categories&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=categories');
		}
    }

    function accessregistered()
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $row = JTable::getInstance('K2Category', 'Table');
        $cid = JRequest::getVar('cid');
        $row->load($cid[0]);
        $row->access = 1;
        if (!$row->check())
        {
            return $row->getError();
        }
        if (!$row->store())
        {
            return $row->getError();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
        $application->enqueueMessage($msg);
        $application->redirect('index.php?option=com_k2&view=categories');
    }

    function accessspecial()
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $row = JTable::getInstance('K2Category', 'Table');
        $cid = JRequest::getVar('cid');
        $row->load($cid[0]);
        $row->access = 2;
        if (!$row->check())
        {
            return $row->getError();
        }
        if (!$row->store())
        {
            return $row->getError();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
        $application->enqueueMessage($msg);
        $application->redirect('index.php?option=com_k2&view=categories');
	}

    function accesspublic()
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $row = JTable::getInstance('K2Category', 'Table');
        $cid = JRequest::getVar('cid');
        $row->load($cid[0]);
        $row->access = 0;
        if (!$row->check())
        {
            return $row->getError();
        }
        if (!$row->store())
        {
            return $row->getError();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $msg = JText::_('K2_NEW_ACCESS_SETTING_SAVED');
        $application->enqueueMessage($msg);
        $application->redirect('index.php?option=com_k2&view=categories');
	}

    function trash()
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        $row = JTable::getInstance('K2Category', 'Table');
        JArrayHelper::toInteger($cid);
        K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
        $model = K2Model::getInstance('Itemlist', 'K2Model');
        $categories = $model->getCategoryTree($cid);
        $sql = @implode(',', $categories);
        $db = JFactory::getDbo();
        $query = "UPDATE #__k2_categories SET trash=1  WHERE id IN ({$sql})";
        $db->setQuery($query);
        $db->query();
        $query = "UPDATE #__k2_items SET trash=1  WHERE catid IN ({$sql})";
        $db->setQuery($query);
        $db->query();

        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onFinderChangeState', array('com_k2.category', $cid, 0));
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
		$application->enqueueMessage(JText::_('K2_CATEGORIES_MOVED_TO_TRASH'));
        $application->redirect('index.php?option=com_k2&view=categories');
    }

    function restore()
    {
        $application = JFactory::getApplication();
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        $warning = false;
        $restored = array();
        foreach ($cid as $id)
        {
        	$row = JTable::getInstance('K2Category', 'Table');
            $row->load($id);
            if ((int)$row->parent == 0)
            {
                $row->trash = 0;
                $row->store();
                $restored[] = $id;
            }
            else
            {
                $query = "SELECT COUNT(*) FROM #__k2_categories WHERE id={$row->parent} AND trash = 0";
                $db->setQuery($query);
                $result = $db->loadResult();
                if ($result)
                {
                    $row->trash = 0;
                    $row->store();
                    $restored[] = $id;
                }
                else
                {
                    $warning = true;
                }

            }

        }
        // Restore also the items of the categories
        if (count($restored))
        {
            JArrayHelper::toInteger($restored);
            $db->setQuery('UPDATE #__k2_items SET trash = 0 WHERE catid IN ('.implode(',', $restored).') AND trash = 1');
            $db->query();
        }
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onFinderChangeState', array('com_k2.category', $cid, 1));
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if ($warning)
            $application->enqueueMessage(JText::_('K2_SOME_OF_THE_CATEGORIES_HAVE_NOT_BEEN_RESTORED_BECAUSE_THEIR_PARENT_CATEGORY_IS_IN_TRASH'), 'notice');
		$application->enqueueMessage(JText::_('K2_CATEGORIES_MOVED_TO_TRASH'));
        $application->redirect('index.php?option=com_k2&view=categories');
    }

    function remove()
    {
        $application = JFactory::getApplication();
        jimport('joomla.filesystem.file');
        $db = JFactory::getDbo();
        $cid = JRequest::getVar('cid');
        JArrayHelper::toInteger($cid);
        JPluginHelper::importPlugin('finder');
        $dispatcher = JDispatcher::getInstance();
        $warningItems = false;
        $warningChildren = false;
        $cid = array_reverse($cid);
        for ($i = 0; $i < sizeof($cid); $i++)
        {
        	$row = JTable::getInstance('K2Category', 'Table');
            $row->load($cid[$i]);

            $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid={$cid[$i]}";
            $db->setQuery($query);
            $num = $db->loadResult();

            if ($num > 0)
            {
                $warningItems = true;
            }

            $query = "SELECT COUNT(*) FROM #__k2_categories WHERE parent={$cid[$i]}";
            $db->setQuery($query);
            $children = $db->loadResult();

            if ($children > 0)
            {
                $warningChildren = true;
            }

            if ($children == 0 && $num == 0)
            {

                if ($row->image)
                {
                    JFile::delete(JPATH_ROOT.'/media/k2/categories/'.$row->image);
                }
                $row->delete($cid[$i]);
                $dispatcher->trigger('onFinderAfterDelete', array('com_k2.category', $row));

            }
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();

        if ($warningItems)
        {
            $application->enqueueMessage(JText::_('K2_SOME_OF_THE_CATEGORIES_HAVE_NOT_BEEN_DELETED_BECAUSE_THEY_HAVE_ITEMS'), 'notice');
        }
        if ($warningChildren)
        {
            $application->enqueueMessage(JText::_('K2_SOME_OF_THE_CATEGORIES_HAVE_NOT_BEEN_DELETED_BECAUSE_THEY_HAVE_CHILD_CATEGORIES'), 'notice');
        }

		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        $application->redirect('index.php?option=com_k2&view=categories');
    }

    function categoriesTree($row = NULL, $hideTrashed = false, $hideUnpublished = true)
    {
        $db = JFactory::getDbo();
        if (isset($row->id))
        {
            $idCheck = ' AND id != '.( int )$row->id;
        }
        else
        {
            $idCheck = null;
        }
        if (!isset($row->parent))
        {
            if (is_null($row))
            {
                $row = new stdClass;
            }
            $row->parent = 0;
        }
        $query = "SELECT m.* FROM #__k2_categories m WHERE id > 0 {$idCheck}";

        if ($hideUnpublished)
        {
            $query .= " AND published=1 ";
        }

        if ($hideTrashed)
        {
            $query .= " AND trash=0 ";
        }

        $query .= " ORDER BY parent, ordering";
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
            if (!$item->published)
                $item->treename .= ' [**'.JText::_('K2_UNPUBLISHED_CATEGORY').'**]';
            if ($item->trash)
                $item->treename .= ' [**'.JText::_('K2_TRASHED_CATEGORY').'**]';
            $mitems[] = JHTML::_('select.option', $item->id, $item->treename);
        }
        return $mitems;
    }

    function copy($batch = false)
    {
        jimport('joomla.filesystem.file');
        $application = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        JArrayHelper::toInteger($cid);
        $copies = array();
        foreach ($cid as $id)
        {
            // Load source category
            $category = JTable::getInstance('K2Category', 'Table');
            $category->load($id);

            // Save target category
            $row = JTable::getInstance('K2Category', 'Table');
            $row = $category;
            $row->id = NULL;
            $row->name = JText::_('K2_COPY_OF').' '.$category->name;
            $row->published = 0;
            $row->store();
            $copies[] = $row->id;
            // Target image
            if ($category->image && JFile::exists(JPATH_SITE.'/media/k2/categories/'.$category->image))
            {
                JFile::copy(JPATH_SITE.'/media/k2/categories/'.$category->image, JPATH_SITE.'/media/k2/categories/'.$row->id.'.jpg');
                $row->image = $row->id.'.jpg';
                $row->store();
            }
        }
        if($batch)
        {
            return $copies;
        }
        else
        {
            $application->enqueueMessage(JText::_('K2_COPY_COMPLETED'));
            $application->redirect('index.php?option=com_k2&view=categories');
        }
    }

    function saveBatch()
    {
        $application = JFactory::getApplication();
        $cid = JRequest::getVar('cid');
        $batchMode = JRequest::getCmd('batchMode');
        $catid = JRequest::getCmd('batchCategory');
        $access = JRequest::getCmd('batchAccess');
        $extraFieldsGroups = JRequest::getCmd('batchExtraFieldsGroups');
        $language = JRequest::getVar('batchLanguage');
        if($batchMode == 'clone'){
            $cid = $this->copy(true);
        }
        if(in_array($catid, $cid))
        {
            $application->redirect('index.php?option=com_k2&view=categories');
            return;
        }
        foreach ($cid as $id)
        {
            $row = JTable::getInstance('K2Category', 'Table');
            $row->load($id);
            if(is_numeric($catid) && $catid != '')
            {
                $row->parent = $catid;
                $row->ordering = $row->getNextOrder('parent = '.(int)$catid.' AND published = 1');
            }
            if($access)
            {
                $row->access = $access;
            }
            if(is_numeric($extraFieldsGroups) && $extraFieldsGroups != '')
            {
                $row->extraFieldsGroup = intval($extraFieldsGroups);
            }
            if($language)
            {
                $row->language = $language;
            }
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $application->enqueueMessage(JText::_('K2_BATCH_COMPLETED'));
        $application->redirect('index.php?option=com_k2&view=categories');
    }
}
