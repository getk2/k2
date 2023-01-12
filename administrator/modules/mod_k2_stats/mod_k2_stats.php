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

$user = JFactory::getUser();

if (K2_JVERSION != '15')
{
	if (!$user->authorise('core.manage', 'com_k2'))
	{
		return;
	}
}

if (K2_JVERSION != '15')
{
	$language = JFactory::getLanguage();
	$language->load('com_k2.dates', JPATH_ADMINISTRATOR);
}

require_once(dirname(__FILE__).'/helper.php');

if ($params->get('latestItems', 1))
{
	$latestItems = modK2StatsHelper::getLatestItems();
}
if ($params->get('popularItems', 1))
{
	$popularItems = modK2StatsHelper::getPopularItems();
}
if ($params->get('mostCommentedItems', 1))
{
	$mostCommentedItems = modK2StatsHelper::getMostCommentedItems();
}
if ($params->get('latestComments', 1))
{
	$latestComments = modK2StatsHelper::getLatestComments();
}
if ($params->get('statistics', 1))
{
	$statistics = modK2StatsHelper::getStatistics();
}

require(JModuleHelper::getLayoutPath('mod_k2_stats'));
