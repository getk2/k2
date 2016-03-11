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

jimport('joomla.html.parameter');

class K2HelperPermissions
{

    public static function setPermissions()
    {
        $params = K2HelperUtilities::getParams('com_k2');
        $user = JFactory::getUser();
        if ($user->guest)
        {
            return;
        }
        $K2User = K2HelperPermissions::getK2User($user->id);
        if (!is_object($K2User))
        {
            return;
        }
        $K2UserGroup = K2HelperPermissions::getK2UserGroup($K2User->group);
        if (is_null($K2UserGroup))
        {
            return;
        }
        $K2Permissions = K2Permissions::getInstance();
        $permissions = K2_JVERSION == '15' ? new JParameter($K2UserGroup->permissions) : new JRegistry($K2UserGroup->permissions);
        $K2Permissions->permissions = $permissions;
        if ($permissions->get('categories') == 'none')
        {
            return;
        }
        else if ($permissions->get('categories') == 'all')
        {
            if ($permissions->get('add') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
            {
                $K2Permissions->actions[] = 'add.category.all';
                $K2Permissions->actions[] = 'tag';
                $K2Permissions->actions[] = 'extraFields';
            }
            if ($permissions->get('editOwn') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
            {
                $K2Permissions->actions[] = 'editOwn.item.'.$user->id;
                $K2Permissions->actions[] = 'tag';
                $K2Permissions->actions[] = 'extraFields';
            }
            if ($permissions->get('editAll') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
            {
                $K2Permissions->actions[] = 'editAll.category.all';
                $K2Permissions->actions[] = 'tag';
                $K2Permissions->actions[] = 'extraFields';
            }
            if ($permissions->get('publish') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
            {
                $K2Permissions->actions[] = 'publish.category.all';
            }
            if ($permissions->get('comment'))
            {
                $K2Permissions->actions[] = 'comment.category.all';
            }
            if ($permissions->get('editPublished'))
            {
                $K2Permissions->actions[] = 'editPublished.category.all';
            }
        }
        else
        {
            $selectedCategories = $permissions->get('categories', NULL);
            if (is_string($selectedCategories))
            {
                $searchIDs[] = $selectedCategories;
            }
            else
            {
                $searchIDs = $selectedCategories;
            }
            if ($permissions->get('inheritance'))
            {
                $model = K2Model::getInstance('Itemlist', 'K2Model');
                $categories = $model->getCategoryTree($searchIDs);
            }
            else
            {
                $categories = $searchIDs;
            }
            if (is_array($categories) && count($categories))
            {
                foreach ($categories as $category)
                {
                    if ($permissions->get('add') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
                    {
                        $K2Permissions->actions[] = 'add.category.'.$category;
                        $K2Permissions->actions[] = 'tag';
                        $K2Permissions->actions[] = 'extraFields';
                    }
                    if ($permissions->get('editOwn') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
                    {
                        $K2Permissions->actions[] = 'editOwn.item.'.$user->id.'.'.$category;
                        $K2Permissions->actions[] = 'tag';
                        $K2Permissions->actions[] = 'extraFields';
                    }
                    if ($permissions->get('editAll') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
                    {
                        $K2Permissions->actions[] = 'editAll.category.'.$category;
                        $K2Permissions->actions[] = 'tag';
                        $K2Permissions->actions[] = 'extraFields';
                    }
                    if ($permissions->get('publish') && $permissions->get('frontEdit') && $params->get('frontendEditing'))
                    {
                        $K2Permissions->actions[] = 'publish.category.'.$category;
                    }
                    if ($permissions->get('comment'))
                    {
                        $K2Permissions->actions[] = 'comment.category.'.$category;
                    }
		            if ($permissions->get('editPublished'))
		            {
		                $K2Permissions->actions[] = 'editPublished.category.'.$category;
		            }
                }
            }
        }
        return;
    }

    public static function checkPermissions()
    {
        $view = JRequest::getCmd('view');
        if ($view != 'item')
        {
            return;
        }
        $task = JRequest::getCmd('task');
        $user = JFactory::getUser();
        $mainframe = JFactory::getApplication();
        if ($user->guest && ($task == 'add' || $task == 'edit'))
        {
            $uri = JURI::getInstance();
            $return = base64_encode($uri->toString());
			$mainframe->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
            if (K2_JVERSION == '15')
            {
                $mainframe->redirect('index.php?option=com_user&view=login&return='.$return.'&tmpl=component');
            }
            else
            {
                $mainframe->redirect('index.php?option=com_users&view=login&return='.$return.'&tmpl=component');
            }
        }

        switch ($task)
        {

            case 'add' :
                if (!K2HelperPermissions::canAddItem())
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                break;

            case 'edit' :
            case 'deleteAttachment' :
            case 'checkin' :
                $cid = JRequest::getInt('cid');
                if($cid)
                {
                  JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
                  $item = JTable::getInstance('K2Item', 'Table');
                  $item->load($cid);

                  if (!K2HelperPermissions::canEditItem($item->created_by, $item->catid))
                  {
                    // Handle in a different way the case when user can add an item but not edit it.
                    if($task == 'edit' && !$user->guest && $item->created_by == $user->id && (int)$item->modified == 0 && K2HelperPermissions::canAddItem())
                    {
                      echo '<script>parent.location.href = "'.JUri::root().'";</script>';
                      exit;
                    }
                    else
                    {
                      JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                    }

                  }

                }
                break;

            case 'save' :
                $cid = JRequest::getInt('id');
                if ($cid)
                {

                    JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
                    $item = JTable::getInstance('K2Item', 'Table');
                    $item->load($cid);

                    if (!K2HelperPermissions::canEditItem($item->created_by, $item->catid))
                        JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                }
                else
                {
                    if (!K2HelperPermissions::canAddItem())
                        JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                }

                break;

            case 'tag' :
                if (!K2HelperPermissions::canAddTag())
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                break;

            case 'extraFields' :
                if (!K2HelperPermissions::canRenderExtraFields())
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                break;
        }
    }

    public static function getK2User($userID)
    {

        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_users WHERE userID = ".(int)$userID;
        $db->setQuery($query);
        $row = $db->loadObject();
        return $row;
    }

    public static function getK2UserGroup($id)
    {

        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_user_groups WHERE id = ".(int)$id;
        $db->setQuery($query);
        $row = $db->loadObject();
        return $row;
    }

    public static function canAddItem($category = false)
    {

        $user = JFactory::getUser();
        $K2Permissions = K2Permissions::getInstance();
        if (in_array('add.category.all', $K2Permissions->actions))
        {
            return true;
        }
        if ($category)
        {
            return in_array('add.category.'.$category, $K2Permissions->actions);
        }
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__k2_categories WHERE published=1 AND trash=0";
        if (K2_JVERSION != '15')
        {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
        }
        else
        {
            $aid = (int)$user->get('aid');
            $query .= " AND access<={$aid}";
        }
        $db->setQuery($query);
        $categories = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
        foreach ($categories as $category)
        {
            if (in_array('add.category.'.$category, $K2Permissions->actions))
            {
                return true;
            }
        }

        return false;
    }

    public static function canAddToAll()
    {
        $K2Permissions = K2Permissions::getInstance();
        return in_array('add.category.all', $K2Permissions->actions);
    }

    public static function canEditItem($itemOwner, $itemCategory)
    {
        $K2Permissions = K2Permissions::getInstance();
        if (in_array('editAll.category.all', $K2Permissions->actions) || in_array('editOwn.item.'.$itemOwner, $K2Permissions->actions) || in_array('editOwn.item.'.$itemOwner.'.'.$itemCategory, $K2Permissions->actions) || in_array('editAll.category.'.$itemCategory, $K2Permissions->actions))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function canPublishItem($itemCategory)
    {
        $K2Permissions = K2Permissions::getInstance();
        if (in_array('publish.category.all', $K2Permissions->actions) || in_array('publish.category.'.$itemCategory, $K2Permissions->actions))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function canAddTag()
    {
        $K2Permissions = K2Permissions::getInstance();
        return in_array('tag', $K2Permissions->actions);
    }

    public static function canRenderExtraFields()
    {
        $K2Permissions = K2Permissions::getInstance();
        return in_array('extraFields', $K2Permissions->actions);
    }

    public static function canAddComment($itemCategory)
    {
        $K2Permissions = K2Permissions::getInstance();
        return in_array('comment.category.all', $K2Permissions->actions) || in_array('comment.category.'.$itemCategory, $K2Permissions->actions);
    }

    public static function canEditPublished($itemCategory)
    {
        $K2Permissions = K2Permissions::getInstance();
        return in_array('editPublished.category.all', $K2Permissions->actions) || in_array('editPublished.category.'.$itemCategory, $K2Permissions->actions);
    }

}

class K2Permissions
{
    var $actions = array();
    var $permissions = null;
    public static function getInstance()
    {
        static $instance;
        if (!is_object($instance))
        {
            $instance = new K2Permissions();
        }
        return $instance;
    }

}
