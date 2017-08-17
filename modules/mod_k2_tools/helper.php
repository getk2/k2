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

require_once(JPATH_SITE.'/components/com_k2/helpers/route.php');
require_once(JPATH_SITE.'/components/com_k2/helpers/utilities.php');
require_once(JPATH_SITE.'/media/k2/assets/vendors/cascade/calendar/calendar.php');

class modK2ToolsHelper
{
	public static $paths = array();

	public static function getAuthors(&$params)
	{
		$application = JFactory::getApplication();
		$componentParams = JComponentHelper::getParams('com_k2');
		$where = '';
		$cid = $params->get('authors_module_category');
		if ($cid > 0)
		{
			$categories = modK2ToolsHelper::getCategoryChildren($cid);
			$categories[] = $cid;
			JArrayHelper::toInteger($categories);
			$where = " catid IN(".implode(',', $categories).") AND ";

		}

		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		if (K2_JVERSION != '15')
		{
			$languageCheck = '';
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$languageCheck = "AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
			}
			$query = "SELECT DISTINCT created_by FROM #__k2_items
	        WHERE {$where} published=1
	        AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." )
	        AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." )
	        AND trash=0
	        AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")
	        AND created_by_alias=''
			{$languageCheck}
	        AND EXISTS (SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck})";
		}
		else
		{
			$query = "SELECT DISTINCT created_by FROM #__k2_items
	        WHERE {$where} published=1
	        AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." )
	        AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." )
	        AND trash=0
	        AND access<={$aid}
	        AND created_by_alias=''
	        AND EXISTS (SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND access<={$aid} )";
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$authors = array();
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$author = JFactory::getUser($row->created_by);
				$author->link = JRoute::_(K2HelperRoute::getUserRoute($author->id));

				$query = "SELECT id, gender, description, image, url, `group`, plugins FROM #__k2_users WHERE userID=".(int)$author->id;
				$db->setQuery($query);
				$author->profile = $db->loadObject();

				if ($params->get('authorAvatar'))
				{
					$author->avatar = K2HelperUtilities::getAvatar($author->id, $author->email, $componentParams->get('userImageWidth'));
				}

				if (K2_JVERSION != '15')
				{
					$languageCheck = '';
					if ($application->getLanguageFilter())
					{
						$languageTag = JFactory::getLanguage()->getTag();
						$languageCheck = "AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
					}
					$query = "SELECT i.*, c.alias as categoryalias FROM #__k2_items as i
			        LEFT JOIN #__k2_categories c ON c.id = i.catid
			        WHERE i.created_by = ".(int)$author->id."
			        AND i.published = 1
			        AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")
			        AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
			        AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )
			        AND i.trash = 0 AND created_by_alias='' AND c.published = 1 AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") AND c.trash = 0 {$languageCheck} ORDER BY created DESC";
				}
				else
				{
					$query = "SELECT i.*, c.alias as categoryalias FROM #__k2_items as i
			        LEFT JOIN #__k2_categories c ON c.id = i.catid
			        WHERE i.created_by = ".(int)$author->id."
			        AND i.published = 1
			        AND i.access <= {$aid}
			        AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
			        AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )
			        AND i.trash = 0 AND created_by_alias='' AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0 ORDER BY created DESC";
				}

				$db->setQuery($query, 0, 1);
				$author->latest = $db->loadObject();
				$author->latest->id = (int)$author->latest->id;
				$author->latest->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($author->latest->id.':'.urlencode($author->latest->alias), $author->latest->catid.':'.urlencode($author->latest->categoryalias))));

				$query = "SELECT COUNT(*) FROM #__k2_comments WHERE published=1 AND itemID={$author->latest->id}";
				$db->setQuery($query);
				$author->latest->numOfComments = $db->loadResult();

				if ($params->get('authorItemsCounter'))
				{
					if (K2_JVERSION != '15')
					{
						$languageCheck = '';
						if ($application->getLanguageFilter())
						{
							$languageTag = JFactory::getLanguage()->getTag();
							$languageCheck = "AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
						}
						$query = "SELECT COUNT(*) FROM #__k2_items  WHERE {$where} published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") AND created_by_alias='' AND created_by={$row->created_by} {$languageCheck} AND EXISTS (SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck} )";
					}
					else
					{
						$query = "SELECT COUNT(*) FROM #__k2_items  WHERE {$where} published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 AND access<={$aid} AND created_by_alias='' AND created_by={$row->created_by} AND EXISTS (SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND access<={$aid} )";
					}
					$db->setQuery($query);
					$numofitems = $db->loadResult();
					$author->items = $numofitems;
				}
				$authors[] = $author;
			}
		}
		return $authors;
	}

	public static function getArchive(&$params)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

		$nullDate = $db->getNullDate();

		$query = "SELECT DISTINCT MONTH(created) as m, YEAR(created) as y FROM #__k2_items  WHERE published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0";
		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access<={$aid} ";
		}

		$catid = $params->get('archiveCategory', 0);
		if ($catid > 0)
			$query .= " AND catid=".(int)$catid;

		$query .= " ORDER BY created DESC";

		$db->setQuery($query, 0, 12);
		$rows = $db->loadObjectList();
		$months = array(
			JText::_('K2_JANUARY'),
			JText::_('K2_FEBRUARY'),
			JText::_('K2_MARCH'),
			JText::_('K2_APRIL'),
			JText::_('K2_MAY'),
			JText::_('K2_JUNE'),
			JText::_('K2_JULY'),
			JText::_('K2_AUGUST'),
			JText::_('K2_SEPTEMBER'),
			JText::_('K2_OCTOBER'),
			JText::_('K2_NOVEMBER'),
			JText::_('K2_DECEMBER'),
		);
		if (count($rows))
		{

			foreach ($rows as $row)
			{
				if ($params->get('archiveItemsCounter'))
				{
					$row->numOfItems = modK2ToolsHelper::countArchiveItems($row->m, $row->y, $catid);
				}
				else
				{
					$row->numOfItems = '';
				}
				$row->name = $months[($row->m) - 1];

				if ($params->get('archiveCategory', 0) > 0)
				{
					$row->link = JRoute::_(K2HelperRoute::getDateRoute($row->y, $row->m, null, $params->get('archiveCategory')));
				}
				else
				{
					$row->link = JRoute::_(K2HelperRoute::getDateRoute($row->y, $row->m));
				}

				$archives[] = $row;
			}

			return $archives;

		}
	}

	public static function tagCloud(&$params)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

		$nullDate = $db->getNullDate();

		$query = "SELECT i.id FROM #__k2_items as i";
		$query .= " LEFT JOIN #__k2_categories c ON c.id = i.catid";
		$query .= " WHERE i.published=1 ";
		$query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." ) ";
		$query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
		$query .= " AND i.trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		}
		else
		{
			$query .= " AND i.access <= {$aid} ";
		}
		$query .= " AND c.published=1 ";
		$query .= " AND c.trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		}
		else
		{
			$query .= " AND c.access <= {$aid} ";
		}

		$cloudCategory = $params->get('cloud_category');
		if (is_array($cloudCategory))
		{
			$cloudCategory = array_filter($cloudCategory);
		}
		if ($cloudCategory)
		{
			if (!is_array($cloudCategory))
			{
				$cloudCategory = (array)$cloudCategory;
			}
			foreach ($cloudCategory as $cloudCategoryID)
			{
				$categories[] = $cloudCategoryID;
				if ($params->get('cloud_category_recursive'))
				{
					$children = modK2ToolsHelper::getCategoryChildren($cloudCategoryID);
					$categories = @array_merge($categories, $children);
				}
			}
			$categories = @array_unique($categories);
			JArrayHelper::toInteger($categories);
			if (count($categories) == 1)
			{
				$query .= " AND i.catid={$categories[0]}";
			}
			else
			{
				$query .= " AND i.catid IN(".implode(',', $categories).")";
			}
		}

		if (K2_JVERSION != '15')
		{
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}

		$db->setQuery($query);
		$IDs = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();

		if (!is_array($IDs) || !count($IDs))
		{
			return array();
		}

		$query = "SELECT tag.name, tag.id
        FROM #__k2_tags as tag
        LEFT JOIN #__k2_tags_xref AS xref ON xref.tagID = tag.id
        WHERE xref.itemID IN (".implode(',', $IDs).")
        AND tag.published = 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$cloud = array();
		if (count($rows))
		{

			foreach ($rows as $tag)
			{

				if (@array_key_exists($tag->name, $cloud))
				{
					$cloud[$tag->name]++;
				}
				else
				{
					$cloud[$tag->name] = 1;
				}
			}

			$max_size = $params->get('max_size');
			$min_size = $params->get('min_size');
			$max_qty = max(array_values($cloud));
			$min_qty = min(array_values($cloud));
			$spread = $max_qty - $min_qty;
			if (0 == $spread)
			{
				$spread = 1;
			}

			$step = ($max_size - $min_size) / ($spread);

			$counter = 0;
			arsort($cloud, SORT_NUMERIC);
			$cloud = @array_slice($cloud, 0, $params->get('cloud_limit'), true);
			uksort($cloud, "strnatcasecmp");

			foreach ($cloud as $key => $value)
			{
				$size = $min_size + (($value - $min_qty) * $step);
				$size = ceil($size);
				$tmp = new stdClass;
				$tmp->tag = $key;
				$tmp->count = $value;
				$tmp->size = $size;
				$tmp->link = urldecode(JRoute::_(K2HelperRoute::getTagRoute($key)));
				$tags[$counter] = $tmp;
				$counter++;
			}

			return $tags;
		}
	}

	public static function getSearchCategoryFilter(&$params)
	{
		$result = '';
		$cid = $params->get('category_id', NULL);
		if ($params->get('catfilter'))
		{
			if (!is_null($cid))
			{
				if (is_array($cid))
				{
					if ($params->get('getChildren'))
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$result = @implode(',', $categories);
					}
					else
					{
						JArrayHelper::toInteger($cid);
						$result = implode(',', $cid);
					}

				}
				else
				{
					if ($params->get('getChildren'))
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories = $itemListModel->getCategoryTree($cid);
						$result = @implode(',', $categories);
					}
					else
					{
						$result = (int)$cid;
					}

				}
			}
		}

		return $result;
	}

	public static function hasChildren($id)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$id = (int)$id;
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_categories  WHERE parent={$id} AND published=1 AND trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}

		}
		else
		{
			$query .= " AND access <= {$aid}";
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		if (count($rows))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function treerecurse(&$params, $id = 0, $level = 0, $begin = false)
	{

		static $output;
		if ($begin)
		{
			$output = '';
		}
		$application = JFactory::getApplication();
		$root_id = (int)$params->get('root_id');
		$end_level = $params->get('end_level', NULL);
		$id = (int)$id;
		$catid = JRequest::getInt('id');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');

		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$db = JFactory::getDbo();

		switch ($params->get('categoriesListOrdering'))
		{

			case 'alpha' :
				$orderby = 'name';
				break;

			case 'ralpha' :
				$orderby = 'name DESC';
				break;

			case 'order' :
				$orderby = 'ordering';
				break;

			case 'reversedefault' :
				$orderby = 'id DESC';
				break;

			default :
				$orderby = 'id ASC';
				break;
		}

		if (($root_id != 0) && ($level == 0))
		{
			$query = "SELECT * FROM #__k2_categories WHERE parent={$root_id} AND published=1 AND trash=0 ";

		}
		else
		{
			$query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
		}

		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}

		}
		else
		{
			$query .= " AND access <= {$aid}";
		}

		$query .= " ORDER BY {$orderby}";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		if ($level < intval($end_level) || is_null($end_level))
		{
			$output .= '<ul class="level'.$level.'">';
			foreach ($rows as $row)
			{
				if ($params->get('categoriesListItemsCounter'))
				{
					$row->numOfItems = ' ('.modK2ToolsHelper::countCategoryItems($row->id).')';
				}
				else
				{
					$row->numOfItems = '';
				}

				if (($option == 'com_k2') && ($view == 'itemlist') && ($catid == $row->id))
				{
					$active = ' class="activeCategory"';
				}
				else
				{
					$active = '';
				}

				if (modK2ToolsHelper::hasChildren($row->id))
				{
					$output .= '<li'.$active.'><a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"><span class="catTitle">'.$row->name.'</span><span class="catCounter">'.$row->numOfItems.'</span></a>';
					modK2ToolsHelper::treerecurse($params, $row->id, $level + 1);
					$output .= '</li>';
				}
				else
				{
					$output .= '<li'.$active.'><a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"><span class="catTitle">'.$row->name.'</span><span class="catCounter">'.$row->numOfItems.'</span></a></li>';
				}
			}
			$output .= '</ul>';
		}

		return $output;
	}

	public static function treeselectbox(&$params, $id = 0, $level = 0)
	{

		$application = JFactory::getApplication();
		$root_id = (int)$params->get('root_id2');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$category = JRequest::getInt('id');
		$id = (int)$id;
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$db = JFactory::getDbo();
		if (($root_id != 0) && ($level == 0))
		{
			$query = "SELECT * FROM #__k2_categories WHERE parent={$root_id} AND published=1 AND trash=0 ";
		}
		else
		{
			$query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
		}

		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access <= {$aid}";
		}

		$query .= " ORDER BY ordering";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		if ($level == 0)
		{
			echo '
<div class="k2CategorySelectBlock '.$params->get('moduleclass_sfx').'">
	<form action="'.JRoute::_('index.php').'" method="get">
		<select name="category" onchange="window.location=this.form.category.value;">
			<option value="'.JURI::base(true).'/">'.JText::_('K2_SELECT_CATEGORY').'</option>
			';
		}
		$indent = "";
		for ($i = 0; $i < $level; $i++)
		{
			$indent .= '&ndash; ';
		}

		foreach ($rows as $row)
		{
			if (($option == 'com_k2') && ($category == $row->id))
			{
				$selected = ' selected="selected"';
			}
			else
			{
				$selected = '';
			}
			if (modK2ToolsHelper::hasChildren($row->id))
			{
				echo '<option value="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"'.$selected.'>'.$indent.$row->name.'</option>';
				modK2ToolsHelper::treeselectbox($params, $row->id, $level + 1);
			}
			else
			{
				echo '<option value="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"'.$selected.'>'.$indent.$row->name.'</option>';
			}
		}

		if ($level == 0)
		{

			echo '
			</select>
			<input name="option" value="com_k2" type="hidden" />
			<input name="view" value="itemlist" type="hidden" />
			<input name="task" value="category" type="hidden" />
			<input name="Itemid" value="'.JRequest::getInt('Itemid').'" type="hidden" />';

			// For Joom!Fish compatibility
			if (JRequest::getCmd('lang'))
			{
				echo '<input name="lang" value="'.JRequest::getCmd('lang').'" type="hidden" />';
			}

			echo '
	</form>
</div>
			';

		}
	}

	public static function breadcrumbs($params)
	{

		$application = JFactory::getApplication();
		$array = array();
		$view = JRequest::getCmd('view');
		$id = JRequest::getInt('id');
		$option = JRequest::getCmd('option');
		$task = JRequest::getCmd('task');

		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');

		$menu = $application->getMenu();
		$active = $menu->getActive();

		if ($option == 'com_k2')
		{

			switch ($view)
			{

				case 'item' :
					if (K2_JVERSION != '15')
					{
						$languageCheck = '';
						if ($application->getLanguageFilter())
						{
							$languageTag = JFactory::getLanguage()->getTag();
							$languageCheck = " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
						}
						$query = "SELECT * FROM #__k2_items  WHERE id={$id} AND published=1 AND trash=0 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck} AND EXISTS (SELECT * FROM #__k2_categories WHERE #__k2_categories.id= #__k2_items.catid AND published=1 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")  {$languageCheck} )";
					}
					else
					{
						$query = "SELECT * FROM #__k2_items  WHERE id={$id} AND published=1 AND trash=0 AND access<={$aid} AND EXISTS (SELECT * FROM #__k2_categories WHERE #__k2_categories.id= #__k2_items.catid AND published=1 AND access<={$aid})";
					}
					$db->setQuery($query);
					$row = $db->loadObject();
					if ($db->getErrorNum())
					{
						echo $db->stderr();
						return false;
					}

					$matchItem = !is_null($active) && @$active->query['view'] == 'item' && @$active->query['id'] == $id;
					$matchCategory = !is_null($active) && @$active->query['view'] == 'itemlist' && @$active->query['task'] == 'category' && @$active->query['id'] == $row->catid;

					if($matchItem || $matchCategory)
					{
						$title = ($matchCategory) ? $row->title : '';
						$path = modK2ToolsHelper::getSitePath();
						return array($path, $title);
					}

					$title = $row->title;
					$path = modK2ToolsHelper::getCategoryPath($row->catid);

					break;

				case 'itemlist' :
					if ($task == 'category')
					{

						$match = !is_null($active) && @$active->query['view'] == 'itemlist' && @$active->query['task'] == 'category' && @$active->query['id'] == $id;
						if($match)
						{
							$title = '';
							$path = modK2ToolsHelper::getSitePath();
							return array($path, $title);
						}


						$query = "SELECT * FROM #__k2_categories  WHERE id={$id} AND published=1 AND trash=0 ";
						if (K2_JVERSION != '15')
						{
							$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
							if ($application->getLanguageFilter())
							{
								$languageTag = JFactory::getLanguage()->getTag();
								$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
							}
						}
						else
						{
							$query .= " AND access <= {$aid}";
						}

						$db->setQuery($query);
						$row = $db->loadObject();
						if ($db->getErrorNum())
						{
							echo $db->stderr();
							return false;
						}
						$title = $row->name;
						$path = modK2ToolsHelper::getCategoryPath($row->parent);

					}
					else
					{
						$document = JFactory::getDocument();
						$title = $document->getTitle();
						$path = modK2ToolsHelper::getSitePath();
					}
					break;

				case 'latest' :
					$document = JFactory::getDocument();
					$title = $document->getTitle();
					$path = modK2ToolsHelper::getSitePath();
					break;
			}

		}
		else
		{
			$document = JFactory::getDocument();
			$title = $document->getTitle();
			$path = modK2ToolsHelper::getSitePath();
		}

		return array(
			$path,
			$title
		);
	}

	public static function getSitePath()
	{

		$application = JFactory::getApplication();
		$pathway = $application->getPathway();
		$items = $pathway->getPathway();
		$count = count($items);
		$path = array();
		for ($i = 0; $i < $count; $i++)
		{
			if (!empty($items[$i]->link))
			{
				$items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_QUOTES, 'UTF-8'));
				$items[$i]->link = JRoute::_($items[$i]->link);
				array_push($path, '<a href="'.JRoute::_($items[$i]->link).'">'.$items[$i]->name.'</a>');
			}

		}
		return $path;

	}

	public static function getCategoryPath($catid, &$array = array())
	{
		if(isset(self::$paths[$catid]))
		{
			return self::$paths[$catid];
		}

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$catid = (int)$catid;
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_categories WHERE id={$catid} AND published=1 AND trash=0 ";

		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access <= {$aid}";
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		foreach ($rows as $row)
		{
			array_push($array, '<a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'">'.$row->name.'</a>');
			modK2ToolsHelper::getCategoryPath($row->parent, $array);
		}
		$return = array_reverse($array);
		self::$paths[$catid] = $return;
		return $return;
	}

	public static function getCategoryChildren($catid)
	{

		static $array = array();
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$catid = (int)$catid;
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access <= {$aid}";
		}
		$query .= " ORDER BY ordering ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}
		foreach ($rows as $row)
		{
			array_push($array, $row->id);
			if (modK2ToolsHelper::hasChildren($row->id))
			{
				modK2ToolsHelper::getCategoryChildren($row->id);
			}
		}
		return $array;
	}

	public static function countArchiveItems($month, $year, $catid = 0)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$month = (int)$month;
		$year = (int)$year;
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

		$nullDate = $db->getNullDate();

		$query = "SELECT COUNT(*) FROM #__k2_items WHERE MONTH(created)={$month} AND YEAR(created)={$year} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access <= {$aid}";
		}
		if ($catid > 0)
		{
			$query .= " AND catid={$catid}";
		}
		$db->setQuery($query);
		$total = $db->loadResult();
		return $total;

	}

	public static function countCategoryItems($id)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$id = (int)$id;
		$db = JFactory::getDbo();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

		$nullDate = $db->getNullDate();

		$query = "SELECT COUNT(*) FROM #__k2_items WHERE catid={$id} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
			if ($application->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
			}
		}
		else
		{
			$query .= " AND access <= {$aid}";
		}
		$db->setQuery($query);
		$total = $db->loadResult();
		return $total;
	}

	public static function calendar($params)
	{

		$month = JRequest::getInt('month');
		$year = JRequest::getInt('year');

		$months = array(
			JText::_('K2_JANUARY'),
			JText::_('K2_FEBRUARY'),
			JText::_('K2_MARCH'),
			JText::_('K2_APRIL'),
			JText::_('K2_MAY'),
			JText::_('K2_JUNE'),
			JText::_('K2_JULY'),
			JText::_('K2_AUGUST'),
			JText::_('K2_SEPTEMBER'),
			JText::_('K2_OCTOBER'),
			JText::_('K2_NOVEMBER'),
			JText::_('K2_DECEMBER'),
		);
		$days = array(
			JText::_('K2_SUN'),
			JText::_('K2_MON'),
			JText::_('K2_TUE'),
			JText::_('K2_WED'),
			JText::_('K2_THU'),
			JText::_('K2_FRI'),
			JText::_('K2_SAT'),
		);

		$cal = new MyCalendar;
		$cal->category = $params->get('calendarCategory', 0);
		$cal->setStartDay(1);
		$cal->setMonthNames($months);
		$cal->setDayNames($days);

		if (($month) && ($year))
		{
			return $cal->getMonthView($month, $year);
		}
		else
		{
			return $cal->getCurrentMonthView();
		}
	}

	public static function renderCustomCode($params)
	{
		jimport('joomla.filesystem.file');
		$document = JFactory::getDocument();
		if ($params->get('parsePhp'))
		{
			$filename = tempnam(JPATH_SITE.'/cache/mod_k2_tools', 'tmp');
			$customCode = $params->get('customCode');
			JFile::write($filename, $customCode);
			ob_start();
			include ($filename);
			$output = ob_get_contents();
			ob_end_clean();
			JFile::delete($filename);
		}
		else
		{
			$output = $params->get('customCode');
		}
		if ($document->getType() != 'feed')
		{
			$dispatcher = JDispatcher::getInstance();
			if ($params->get('JPlugins'))
			{
				JPluginHelper::importPlugin('content');
				$row = new JObject();
				$row->text = $output;
				if (K2_JVERSION != '15')
				{
					$dispatcher->trigger('onContentPrepare', array(
						'mod_k2_tools',
						&$row,
						&$params
					));
				}
				else
				{
					$dispatcher->trigger('onPrepareContent', array(
						&$row,
						&$params
					));
				}
				$output = $row->text;
			}
			if ($params->get('K2Plugins'))
			{
				JPluginHelper::importPlugin('k2');
				$row = new JObject();
				$row->text = $output;
				$dispatcher->trigger('onK2PrepareContent', array(
					&$row,
					&$params
				));
				$output = $row->text;
			}

		}
		return $output;
	}

}

class MyCalendar extends Calendar
{

	var $category = null;
	var $cache = null;

	function getDateLink($day, $month, $year)
	{

		if(is_null($this->cache)) {

			$this->cache = array();
			$application = JFactory::getApplication();
			$user = JFactory::getUser();
			$aid = $user->get('aid');
			$db = JFactory::getDbo();

			$jnow = JFactory::getDate();
			$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

			$nullDate = $db->getNullDate();

			$languageCheck = '';
			if (K2_JVERSION != '15')
			{
				$accessCheck = " access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				if ($application->getLanguageFilter())
				{
					$languageTag = JFactory::getLanguage()->getTag();
					$languageCheck = " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
				}
			}
			else
			{
				$accessCheck = " access <= {$aid}";
			}

			$query = "SELECT DAY(created) AS day, COUNT(*) AS counter FROM #__k2_items WHERE YEAR(created)={$year} AND MONTH(created)={$month} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 AND {$accessCheck} {$languageCheck} AND EXISTS(SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND {$accessCheck} {$languageCheck})";

			$catid = $this->category;
			if ($catid > 0)
				$query .= " AND catid={$catid}";

			$query .= ' GROUP BY day';

			$db->setQuery($query);
			$objects = $db->loadObjectList();
			if ($db->getErrorNum())
			{
				echo $db->stderr();
				return false;
			}
			foreach($objects as $object) {
				$this->cache[$object->day] = $object->counter;
			}

		}
		$result = isset($this->cache[$day]) ? $this->cache[$day] : 0;

		if ($result > 0)
		{
			if ($this->category > 0)
				return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day, $this->category));
			else
				return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day));
		}
		else
		{
			return false;
		}
	}

	function getCalendarLink($month, $year)
	{
		$itemID = JRequest::getInt('Itemid');
		if ($this->category > 0)
			return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month={$month}&amp;year={$year}&amp;catid={$this->category}&amp;Itemid={$itemID}";
		else
			return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month=$month&amp;year=$year&amp;Itemid={$itemID}";
	}

}
