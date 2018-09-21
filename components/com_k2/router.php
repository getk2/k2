<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_k2');

// Quick implementation of "Improved K2 router.php" https://gist.github.com/phproberto/4687829
// @TODO Merge the two routers
if ($params->get('k2Sef'))
{

	/**
	 * Build the SEF route from the query
	 *
	 * @param   array  &$query  The array of query string values for which to build a route
	 *
	 * @return  array           The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since	1.5
	 */
	function k2BuildRoute(&$query)
	{
		// Initialize
		$segments = array();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get the menu
		$menu = JFactory::getApplication()->getMenu();

		// Detect the active menu item
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}

		// Load data from the current menu item
		$mView = ( empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mTask = ( empty($menuItem->query['task'])) ? null : $menuItem->query['task'];
		$mId = ( empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
		$mTag = ( empty($menuItem->query['tag'])) ? null : $menuItem->query['tag'];

		if (isset($query['layout']))
		{
			unset($query['layout']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) && @intval($query['id']) > 0)
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['id']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag']))
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['tag']);
		}

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		if (isset($query['cid']))
		{
			$segments[] = $query['cid'];
			unset($query['cid']);
		}

		if (isset($query['tag']))
		{
			$segments[] = $query['tag'];
			unset($query['tag']);
		}

		if (isset($query['year']))
		{
			$segments[] = $query['year'];
			unset($query['year']);
		}

		if (isset($query['month']))
		{
			$segments[] = $query['month'];
			unset($query['month']);
		}

		if (isset($query['day']))
		{
			$segments[] = $query['day'];
			unset($query['day']);
		}

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		// Item view
		if (isset($segments[0]) && $segments[0] == 'item' && @$segments[1] != 'add')
		{
			// Enabled category prefix  for items
			if ($params->get('k2SefLabelItem'))
			{
				// Tasks available for an item
				$itemTasks = array('edit', 'download');

				// If it's a task pick the next key
				if (in_array($segments[1], $itemTasks))
				{
					$ItemId = $segments[2];
				}
				else
				{
					$ItemId = $segments[1];
				}

				// Replace the item with the category slug
				if ($params->get('k2SefLabelItem') == '1')
				{

					// Remove the id from the slug
					if ($params->get('k2SefInsertCatId') == '0')
					{

						// Try to split the slug
						$segments[0] = getCategorySlug((int)$ItemId);
						$temp 	 	 = @explode('-', $segments[0]);

						// If the slug contained an item id do not use it
						if (count($temp) > 1)
						{
							@$segments[0] = $temp[1];
						}

					}
					else
					{
						// Apply the link including the id
						$segments[0] = getCategorySlug((int)$ItemId);
					}

				}
				else
				{
					$segments[0] = $params->get('k2SefLabelItemCustomPrefix');
				}

			}
			// Remove "item" from the URL
			else
			{
				unset($segments[0]);
			}

			// Handle item id and alias
			if ($params->get('k2SefInsertItemId'))
			{
				if ($params->get('k2SefUseItemTitleAlias'))
				{
					if ($params->get('k2SefItemIdTitleAliasSep') == 'slash')
					{
						$segments[1] = JString::str_ireplace(':', '/', $segments[1]);
					}
				}
				else
				{
					$temp = @explode(':', $segments[1]);
					$segments[1] = $temp[0];
				}

			}
			else
			{
				if (isset($segments[1]) && $segments[1] != 'download')
				{
					// Try to split the slud
					$temp = @explode(':', $segments[1]);

					// If the slug contained an item id do not use it
					if (count($temp) > 1)
					{
						$segments[1] = $temp[1];
					}

				}
			}
		}
		// Itemlist view. Check for prefix segments
		elseif (isset($segments[0]) && $segments[0] == 'itemlist')
		{
			if (isset($segments[1]))
			{
				switch ($segments[1])
				{
					case 'category' :
						$segments[0] = $params->get('k2SefLabelCat', 'content');
						unset($segments[1]);
						// Handle category id and alias
						if ($params->get('k2SefInsertCatId'))
						{
							if ($params->get('k2SefUseCatTitleAlias'))
							{
								if ($params->get('k2SefCatIdTitleAliasSep') == 'slash')
								{
									$segments[2] = JString::str_ireplace(':', '/', $segments[2]);
								}
							}
							else
							{
								$temp = @explode(':', $segments[2]);
								$segments[2] = (int)$temp[0];
							}

						}
						else
						{
							// Try to split the slud
							$temp = @explode(':', $segments[2]);
							unset($segments[2]);

							// If the slug contained an item id do not use it
							if (count($temp) > 1)
							{
								@$segments[1] = $temp[1];
							}
						}

						break;
					case 'tag' :
						$segments[0] = $params->get('k2SefLabelTag', 'tag');
						unset($segments[1]);
						break;
					case 'user' :
						$segments[0] = $params->get('k2SefLabelUser', 'author');
						unset($segments[1]);
						break;
					case 'date' :
						$segments[0] = $params->get('k2SefLabelDate', 'date');
						unset($segments[1]);
						break;
					case 'search' :
						$segments[0] = $params->get('k2SefLabelSearch', 'search');
						unset($segments[1]);
						break;
					default :
						$segments[0] = 'itemlist';
						break;
				}
			}

		}
		// Return reordered segments array
		return array_values($segments);
	}

	/**
	 * Get back the url from the segments
	 *
	 * @param   array  $segments  Segments in the SEF URL
	 *
	 * @return  array             Generated vars for the query
	 */
	function k2ParseRoute($segments)
	{

		// Initialize
		$vars = array();

		$params = JComponentHelper::getParams('com_k2');

		$reservedViews = array('item', 'itemlist', 'media', 'users', 'comments', 'latest');

		if (!in_array($segments[0], $reservedViews))
		{
			// Category view
			if ($segments[0] == $params->get('k2SefLabelCat', 'content'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'category');
			}
			// Tag view
			elseif ($segments[0] == $params->get('k2SefLabelTag', 'tag'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'tag');
			}
			// User view
			elseif ($segments[0] == $params->get('k2SefLabelUser', 'author'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'user');
			}
			// Date view
			elseif ($segments[0] == $params->get('k2SefLabelDate', 'date'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'date');
			}
			// Search view
			elseif ($segments[0] == $params->get('k2SefLabelSearch', 'search'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'search');
			}
			// Item view
			else
			{
				// Replace the category prefix with item
				if ($params->get('k2SefLabelItem'))
				{
					$segments[0] = 'item';
				}
				// Reinsert the removed item segment
				else
				{
					array_splice($segments, 0, 0, 'item');
				}
				// Reinsert item id to the item alias
				if (!$params->get('k2SefInsertItemId') && @$segments[1] != 'download' && @$segments[1] != 'edit')
				{
					$segments[1] = str_replace(':', '-', $segments[1]);
					$ItemId = getItemId($segments[1]);
					$segments[1] = $ItemId.':'.$segments[1];
				}
			}

		}

		$vars['view'] = $segments[0];

		if (!isset($segments[1]))
		{
			$segments[1] = '';
		}
		$vars['task'] = $segments[1];
		if ($segments[0] == 'itemlist')
		{
			switch ($segments[1])
			{

				case 'category' :
					if (isset($segments[2]))
					{
						// Reinsert item id to the item alias
						if (!$params->get('k2SefInsertCatId'))
						{
							$segments[2] = str_replace(':', '-', $segments[2]);
							$catId = getCatId($segments[2]);
							$segments[2] = $catId.':'.$segments[2];
						}
						$vars['id'] = $segments[2];
					}
					break;

				case 'tag' :
					if (isset($segments[2]))
					{
						$vars['tag'] = $segments[2];
					}
					break;

				case 'user' :
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				case 'date' :
					if (isset($segments[2]))
					{
						$vars['year'] = $segments[2];
					}
					if (isset($segments[3]))
					{
						$vars['month'] = $segments[3];
					}
					if (isset($segments[4]))
					{
						$vars['day'] = $segments[4];
					}
					break;
			}

		}
		elseif ($segments[0] == 'item')
		{
			switch ($segments[1])
			{
				case 'add' :
				case 'edit' :
					if (isset($segments[2]))
					{
						$vars['cid'] = $segments[2];
					}
					break;

				case 'download' :
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				default :
					$vars['id'] = $segments[1];
					if (isset($segments[2]))
					{
						$vars['id'] .= ':'.str_replace(':', '-', $segments[2]);
					}
					unset($vars['task']);
					break;
			}

		}

		if ($segments[0] == 'comments' && isset($segments[1]) && $segments[1] == 'reportSpammer')
		{
			$vars['id'] = $segments[2];
		}

		return $vars;
	}

	/**
	 * Get a category alias
	 *
	 * @param   integer  $ItemId  The category id
	 *
	 * @return  string            The category alias
	 */
	function getCategorySlug($ItemId = null)
	{
		$slug = null;

		$db = JFactory::getDbo();
		$query = "SELECT items.id, categories.id AS catid, CASE WHEN CHAR_LENGTH(categories.alias) THEN CONCAT_WS('-', categories.id, categories.alias) ELSE categories.id END AS catslug FROM #__k2_items AS items INNER JOIN #__k2_categories AS categories ON items.catid = categories.id WHERE items.id = ".(int)$ItemId;
		$db->setQuery($query);

		try
		{
			if ($result = $db->loadObject())
			{
				$slug = $result->catslug;
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $slug;
	}

	/**
	 * Get id K2.
	 *
	 * @param   string  $alias  The K2 item alias
	 *
	 * @return  integer
	 */
	function getItemId($alias)
	{
		$id = null;
		$db = JFactory::getDbo();
		$query = "SELECT id FROM #__k2_items WHERE alias = ".$db->quote($alias);
		$db->setQuery($query);
		try
		{
			$id = $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		return $id;
	}

	/**
	 * Get id K2.
	 *
	 * @param   string  $alias  The K2 category alias
	 *
	 * @return  integer
	 */
	function getCatId($alias)
	{
		$id = null;
		$db = JFactory::getDbo();
		$query = "SELECT id FROM #__k2_categories WHERE alias = ".$db->quote($alias);
		$db->setQuery($query);
		try
		{
			$id = $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		return $id;
	}

}
else
{
	function K2BuildRoute(&$query)
	{
		$segments = array();
		$application = JFactory::getApplication();
		$menu = $application->getMenu();
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}
		$mView = ( empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mTask = ( empty($menuItem->query['task'])) ? null : $menuItem->query['task'];
		$mId = ( empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
		$mTag = ( empty($menuItem->query['tag'])) ? null : $menuItem->query['tag'];

		if (isset($query['layout']))
		{
			unset($query['layout']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) && @intval($query['id']) > 0)
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['id']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag']))
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['tag']);
		}

		if (isset($query['view']))
		{
			$view = $query['view'];
			$segments[] = $view;
			unset($query['view']);
		}

		if (@ isset($query['task']))
		{
			$task = $query['task'];
			$segments[] = $task;
			unset($query['task']);
		}

		if (isset($query['id']))
		{
			$id = $query['id'];
			$segments[] = $id;
			unset($query['id']);
		}

		if (isset($query['cid']))
		{
			$cid = $query['cid'];
			$segments[] = $cid;
			unset($query['cid']);
		}

		if (isset($query['tag']))
		{
			$tag = $query['tag'];
			$segments[] = $tag;
			unset($query['tag']);
		}

		if (isset($query['year']))
		{
			$year = $query['year'];
			$segments[] = $year;
			unset($query['year']);
		}

		if (isset($query['month']))
		{
			$month = $query['month'];
			$segments[] = $month;
			unset($query['month']);
		}

		if (isset($query['day']))
		{
			$day = $query['day'];
			$segments[] = $day;
			unset($query['day']);
		}

		if (isset($query['task']))
		{
			$task = $query['task'];
			$segments[] = $task;
			unset($query['task']);
		}

		return $segments;
	}

	function K2ParseRoute($segments)
	{
		$vars = array();
		$vars['view'] = $segments[0];
		if (!isset($segments[1]))
			$segments[1] = '';
		$vars['task'] = $segments[1];

		if ($segments[0] == 'itemlist')
		{

			switch($segments[1])
			{

				case 'category' :
					if (isset($segments[2]))
						$vars['id'] = $segments[2];
					break;

				case 'tag' :
					if (isset($segments[2]))
						$vars['tag'] = $segments[2];
					break;

				case 'user' :
					if (isset($segments[2]))
						$vars['id'] = $segments[2];
					break;

				case 'date' :
					if (isset($segments[2]))
						$vars['year'] = $segments[2];
					if (isset($segments[3]))
						$vars['month'] = $segments[3];
					if (isset($segments[4]))
					{
						$vars['day'] = $segments[4];
					}
					break;
			}

		}
		else if ($segments[0] == 'item')
		{

			switch($segments[1])
			{

				case 'add' :
				case 'edit' :
					if (isset($segments[2]))
						$vars['cid'] = $segments[2];
					break;

				case 'download' :
					if (isset($segments[2]))
						$vars['id'] = $segments[2];
					break;

				default :
					$vars['id'] = $segments[1];
					unset($vars['task']);
					break;
			}

		}

		if ($segments[0] == 'comments' && isset($segments[1]) && $segments[1] == 'reportSpammer')
		{
			$vars['id'] = $segments[2];
		}

		return $vars;
	}

}
