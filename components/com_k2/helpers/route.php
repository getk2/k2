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

jimport('joomla.application.component.helper');

class K2HelperRoute
{
	private static $anyK2Link = null;
	private static $multipleCategoriesMapping = array();
	private static $tree = null;
	private static $model = null;
	private static $cache = array(
		'item' => array(),
		'category' => array(),
		'user' => array(),
		'tag' => array()
	);

	public static function getItemRoute($id, $catid = 0)
	{
		$key = (string)(int)$id.'|'.(int)$catid;
		if (isset(self::$cache['item'][$key]))
		{
			return self::$cache['item'][$key];
		}

		$needles = array(
			'item' => (int)$id,
			'category' => (int)$catid,
		);
		$link = 'index.php?option=com_k2&view=item&id='.$id;
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		self::$cache['item'][$key] = $link;
		return $link;
	}

	public static function getCategoryRoute($catid)
	{
		$key = (int)$catid;
		if (isset(self::$cache['category'][$key]))
		{
			return self::$cache['category'][$key];
		}
		$needles = array('category' => (int)$catid);
		$link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$catid;
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		self::$cache['category'][$key] = $link;
		return $link;
	}

	public static function getUserRoute($userID)
	{
		$key = (int)$userID;
		if (isset(self::$cache['user'][$key]))
		{
			return self::$cache['user'][$key];
		}

		$needles = array('user' => (int)$userID);
		$user = JFactory::getUser($userID);
		if (K2_JVERSION != '15' && JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$alias = JApplication::stringURLSafe($user->name);
		}
		else if (JPluginHelper::isEnabled('system', 'unicodeslug') || JPluginHelper::isEnabled('system', 'jw_unicodeSlugsExtended'))
		{
			$alias = JFilterOutput::stringURLSafe($user->name);
		}
		else
		{
			mb_internal_encoding("UTF-8");
			mb_regex_encoding("UTF-8");
			$alias = trim(mb_strtolower($user->name));
			$alias = str_replace('-', ' ', $alias);
			$alias = mb_ereg_replace('[[:space:]]+', ' ', $alias);
			$alias = trim(str_replace(' ', '', $alias));
			$alias = str_replace('.', '', $alias);

			$stripthese = ',|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°|«|»|…';
			$strips = explode('|', $stripthese);
			foreach ($strips as $strip)
			{
				$alias = str_replace($strip, '', $alias);
			}
			$params = K2HelperUtilities::getParams('com_k2');
			$SEFReplacements = array();
			$items = explode(',', $params->get('SEFReplacements', NULL));
			foreach ($items as $item)
			{
				if (!empty($item))
				{
					@list($src, $dst) = explode('|', trim($item));
					$SEFReplacements[trim($src)] = trim($dst);
				}
			}
			foreach ($SEFReplacements as $key => $value)
			{
				$alias = str_replace($key, $value, $alias);
			}
			$alias = trim($alias, '-.');
			if (trim(str_replace('-', '', $alias)) == '')
			{
				$datenow = JFactory::getDate();
				$alias = K2_JVERSION == '15' ? $datenow->toFormat("%Y-%m-%d-%H-%M-%S") : $datenow->format("Y-m-d-H-i-s");
			}
		}
		$link = 'index.php?option=com_k2&view=itemlist&task=user&id='.$userID.':'.$alias;
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		self::$cache['user'][$key] = $link;
		return $link;
	}

	public static function getTagRoute($tag)
	{
		$key = $tag;
		if (isset(self::$cache['tag'][$key]))
		{
			return self::$cache['tag'][$key];
		}

		$needles = array('tag' => $tag);
		$link = 'index.php?option=com_k2&view=itemlist&task=tag&tag='.urlencode($tag);
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		self::$cache['tag'][$key] = $link;
		return $link;
	}

	public static function getDateRoute($year, $month, $day = null, $catid = null)
	{
		$needles = array('year' => $year);
		$link = 'index.php?option=com_k2&view=itemlist&task=date&year='.$year.'&month='.$month;
		if ($day)
		{
			$link .= '&day='.$day;
		}
		if ($catid)
		{
			$link .= '&catid='.$catid;
		}
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	public static function getSearchRoute()
	{
		$needles = array('search' => 'search');
		$link = 'index.php?option=com_k2&view=itemlist&task=search';
		if ($item = K2HelperRoute::_findItem($needles))
		{
			$link .= '&Itemid='.$item->id;
		}
		return $link;
	}

	public static function _findItem($needles)
	{
		$component = JComponentHelper::getComponent('com_k2');
		$application = JFactory::getApplication();
		$menus = $application->getMenu('site', array());
		if (K2_JVERSION != '15')
		{
			$items = $menus->getItems('component_id', $component->id);
		}
		else
		{
			$items = $menus->getItems('componentid', $component->id);
		}
		$match = null;
		foreach ($needles as $needle => $id)
		{
			if (count($items))
			{
				foreach ($items as $item)
				{

					// Detect multiple K2 categories link and set the generic K2 link ( if any )
					if (@$item->query['view'] == 'itemlist' && @$item->query['task'] == '')
					{

						if (!isset(self::$multipleCategoriesMapping[$item->id]))
						{
							if (K2_JVERSION == '15')
							{
								$menuparams = explode("\n", $item->params);
								foreach ($menuparams as $param)
								{
									if (strpos($param, 'categories=') === 0)
									{
										$array = explode('categories=', $param);
										$item->K2Categories = explode('|', $array[1]);
									}
								}
								if (!isset($item->K2Categories))
								{
									$item->K2Categories = array();
								}
							}
							else
							{
								$menuparams = json_decode($item->params);
								$item->K2Categories = isset($menuparams->categories) ? $menuparams->categories : array();
							}

							self::$multipleCategoriesMapping[$item->id] = $item->K2Categories;

							if (count($item->K2Categories) === 0)
							{

								self::$anyK2Link = $item;
							}

						}
					}

					if ($needle == 'user' || $needle == 'category')
					{
						if ((@$item->query['task'] == $needle) && (@$item->query['id'] == $id))
						{
							$match = $item;
							break;
						}

					}
					else if ($needle == 'tag')
					{
						if ((@$item->query['task'] == $needle) && (@$item->query['tag'] == $id))
						{
							$match = $item;
							break;
						}
					}
					else
					{
						if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id))
						{
							$match = $item;
							break;
						}
					}

					if (!is_null($match))
					{
						break;
					}
				}
				// Second pass [START]
				// Only for multiple categories links. Triggered only if we do not have find any match (link to direct category)
				if (is_null($match) && $needle == 'category')
				{
					foreach ($items as $item)
					{
						if (@$item->query['view'] == 'itemlist' && @$item->query['task'] == '')
						{
							if (isset(self::$multipleCategoriesMapping[$item->id]) && is_array(self::$multipleCategoriesMapping[$item->id]))
							{
								foreach (self::$multipleCategoriesMapping[$item->id] as $catid)
								{
									if ((int)$catid == $id)
									{
										$match = $item;
										break;
									}
								}
							}
							if (!is_null($match))
							{
								break;
							}
						}
					}
				}
				// Second pass [END]
			}
			if (!is_null($match))
			{
				break;
			}
		}

		if (is_null($match))
		{
			// Try to detect any parent category menu item....
			if ($needle == 'category')
			{
				if (is_null(self::$tree))
				{
					K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
					$model = K2Model::getInstance('Itemlist', 'K2Model');
					self::$model = $model;
					self::$tree = $model->getCategoriesTree();
				}
				$parents = self::$model->getTreePath(self::$tree, $id);
				if (is_array($parents))
				{
					foreach ($parents as $categoryID)
					{
						if ($categoryID != $id)
						{
							$match = K2HelperRoute::_findItem(array('category' => $categoryID));
							if (!is_null($match))
							{
								break;
							}
						}
					}
				}
			}
			if (is_null($match) && !is_null(self::$anyK2Link))
			{
				$match = self::$anyK2Link;
			}
		}

		return $match;
	}

}
