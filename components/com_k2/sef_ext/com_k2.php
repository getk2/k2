<?php
/**
 * @version		$Id: com_k2.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

if (!function_exists('getCategoryPath'))
{

	function getCategoryPath($catid, $begin = false)
	{
		static $array = array();
		if (intval($catid) == 0)
		{
			return false;
		}
		if ($begin)
		{
			$array = array();
		}

		$user = JFactory::getUser();
		$aid = (int)$user->get('aid');
		$catid = (int)$catid;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_categories WHERE id={$catid} AND published=1";

		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		}
		else
		{
			$query .= " AND access<={$aid} ";
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
			array_push($array, $row->alias);
			getCategoryPath($row->parent, false);
		}

		return array_reverse($array);
	}

}

// ------------------ Standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = shRouter::shGetConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false)
	return;

$shHomePageFlag = false;
$shHomePageFlag = !$shHomePageFlag ? shIsHomepage($string) : $shHomePageFlag;

// Remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
	shRemoveFromGETVarsList('Itemid');
if (!empty($limit))
	shRemoveFromGETVarsList('limit');
if (isset($limitstart))
	shRemoveFromGETVarsList('limitstart');
// limitstart can be zero

// Start by inserting the menu element title (just an idea, this is not required at all)
$task = isset($task) ? @$task : null;
$view = isset($view) ? @$view : null;
$Itemid = isset($Itemid) ? @$Itemid : null;

// Set dummy task for module feeds
if ($view == 'itemlist' && isset($moduleID))
{
	$task = 'module';
}

// K2 parameters
$params = JComponentHelper::getParams('com_k2');
$authorPrefix = $params->get('sh404SefLabelUser', 'blog');
$itemlistPrefix = $params->get('sh404SefLabelCat', '');
$itemPrefix = $params->get('sh404SefLabelItem', 2);
$sh404SefTitleAlias = $params->get('sh404SefTitleAlias', 'alias');
$sh404SefModK2ContentFeedAlias = $params->get('sh404SefModK2ContentFeedAlias', 'feed');
$sh404SefInsertItemId = $params->get('sh404SefInsertItemId');
$sh404SefInsertUniqueItemId = $params->get('sh404SefInsertUniqueItemId');

$database = JFactory::getDBO();
$menu = JSite::getMenu();
$menuparams = NULL;
$menuparams = $menu->getParams($Itemid);

if (isset($task) && ($task == 'calendar' || $task == 'edit' || $task == 'add' || $task == 'save' || $task == 'deleteAttachment' || $task == 'extraFields' || $task == 'checkin' || $task == 'vote' || $task == 'getVotesNum' || $task == 'getVotesPercentage' || $task == 'comment' || $task == 'download'))
	$dosef = false;

if ($view == 'item' && $task == 'tag')
	$dosef = false;

if ($view == 'comments')
	$dosef = false;

switch ($view)
{

	case 'item' :
		if (isset($id) && $id > 0 && $task != 'download')
		{
			$id = (int)$id;

			if ($sh404SefInsertUniqueItemId)
			{
				$query = 'SELECT id, catid, created FROM #__k2_items WHERE id = '.$database->Quote($id);
				$database->setQuery($query);
				if (shTranslateUrl($option, $shLangName))
				{
					$contentElement = $database->loadObject();
				}
				else
				{
					$contentElement = $database->loadObject(false);
				}
				$shTemp = explode(' ', $contentElement->created);
				$title[] = str_replace('-', '', $shTemp[0]).$contentElement->id;
			}

			if (!shTranslateUrl($option, $shLangName))
			{
				$query = 'SELECT '.$sh404SefTitleAlias.', catid FROM #__k2_items WHERE id = '.$id;
			}
			else
			{
				$query = 'SELECT id, '.$sh404SefTitleAlias.', catid FROM #__k2_items WHERE id = '.$id;
			}

			$database->setQuery($query);
			if (shTranslateUrl($option, $shLangName))
				$row = $database->loadObject();
			else
				$row = $database->loadObject(false);

			switch($itemPrefix)
			{
				case 0 :
					break;
				case 1 :
					$fullPath = getCategoryPath($row->catid, true);
					$title[] = array_pop($fullPath);
					break;
				default :
				case 2 :
					$fullPath = getCategoryPath($row->catid, true);
					foreach ($fullPath as $path)
					{
						$title[] = $path;
					}
					break;
			}

			if ($sh404SefInsertItemId)
			{
				$title[] = $row->id.'-'.$row->$sh404SefTitleAlias;
			}
			else
			{
				$title[] = $row->$sh404SefTitleAlias;
			}

			shMustCreatePageId('set', true);
		}
		break;

	case 'itemlist' :
		switch ($task)
		{

			case 'category' :
				if (!empty($itemlistPrefix))
				{
					$title[] = $itemlistPrefix;
				}
				$fullPath = getCategoryPath($id, true);
				foreach ($fullPath as $path)
				{
					$title[] = $path;
				}
				shMustCreatePageId('set', true);
				break;

			case 'user' :
				$user = JFactory::getUser($id);
				if (!empty($authorPrefix))
				{
					$title[] = $authorPrefix;
				}
				$title[] = $user->name;
				break;

			case 'tag' :
				$title[] = 'tag';
				$tag = str_replace('%20', '-', $tag);
				$tag = str_replace('+', '-', $tag);
				$title[] = $tag;
				shMustCreatePageId('set', true);
				break;

			case 'search' :
				$title[] = 'search';
				if (!empty($searchword))
					$title[] = $searchword;
				break;

			case 'date' :
				$title[] = 'date';
				if (!empty($year))
					$title[] = $year;

				if (!empty($month))
					$title[] = $month;

				if (!empty($day))
					$title[] = $day;
				break;

			case 'module' :
				$query = 'SELECT title FROM #__modules WHERE id = '.(int)$moduleID;
				$database->setQuery($query);
				$moduleTitle = $database->loadResult();
				$moduleTitle = str_replace(' ', '-', $moduleTitle);
				if ($sh404SefModK2ContentFeedAlias)
				{
					$title[] = $sh404SefModK2ContentFeedAlias;
				}
				$title[] = $moduleTitle;
				break;

			default :
				if (isset($Itemid))
				{
					$title[] = $menu->getItem($Itemid)->alias;
					shMustCreatePageId('set', true);
				}
				break;
		}

		break;

	case 'latest' :
		if (isset($Itemid))
		{
			$title[] = $menu->getItem($Itemid)->alias;
			shMustCreatePageId('set', true);
		}
		break;
}

if (!empty($format) && $format == 'feed')
{
	$title[] = $format;
	if (!empty($type) && $format != $type)
	{
		$title[] = $type;
	}
}

if (isset($layout))
	shRemoveFromGETVarsList('layout');
if (isset($task))
	shRemoveFromGETVarsList('task');
if (isset($tag))
	shRemoveFromGETVarsList('tag');
if (isset($searchword))
	shRemoveFromGETVarsList('searchword');
if (isset($view))
	shRemoveFromGETVarsList('view');
if (isset($Itemid))
	shRemoveFromGETVarsList('Itemid');
if (isset($year))
	shRemoveFromGETVarsList('year');
if (isset($month))
	shRemoveFromGETVarsList('month');
if (isset($day))
	shRemoveFromGETVarsList('day');
if (isset($id))
	shRemoveFromGETVarsList('id');

/*
 * Only remove format variable if form is html. In all other situations, leave it there as some system plugins
 * may cause pdf and rss to break if they call JFactory::getDocument() in the onAfterInitialize event handler
 * because at this time SEF url are not decoded yet.
 *
 */
if (isset($format) && (!sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR || (sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR && $format == 'html')))
{
	shRemoveFromGETVarsList('format');
}

if (isset($moduleID))
{
	shRemoveFromGETVarsList('moduleID');
}

// ------------------ Standard plugin finalize function - don't change ---------------------------
if ($dosef)
{
	$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString, (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null));
}
// ------------------ Standard plugin finalize function - don't change ---------------------------
