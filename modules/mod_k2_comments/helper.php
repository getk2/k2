<?php
/**
 * @version		$Id: helper.php 1951 2013-03-22 12:15:28Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

class modK2CommentsHelper
{

	public static function getLatestComments(&$params)
	{

		$mainframe = JFactory::getApplication();
		$limit = $params->get('comments_limit', '5');
		$user = JFactory::getUser();
		$aid = $user->get('aid');
		$db = JFactory::getDBO();
		$cid = $params->get('category_id', NULL);

		$jnow = JFactory::getDate();
		$now = K2_JVERSION != '15' ? $jnow->toSql() : $jnow->toMySQL();
		$nullDate = $db->getNullDate();

		$model = K2Model::getInstance('Item', 'K2Model');

		$componentParams = JComponentHelper::getParams('com_k2');

		$query = "SELECT c.*, i.catid, i.title, i.alias, category.alias as catalias, category.name as categoryname
		FROM #__k2_comments as c
		LEFT JOIN #__k2_items as i ON i.id=c.itemID
		LEFT JOIN #__k2_categories as category ON category.id=i.catid
		WHERE i.published=1
		AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )
		AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )
		AND i.trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		}
		else
		{
			$query .= " AND i.access<={$aid} ";
		}
		$query .= " AND category.published=1 AND category.trash=0 ";
		if (K2_JVERSION != '15')
		{
			$query .= " AND category.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		}
		else
		{
			$query .= " AND category.access<={$aid} ";
		}
		$query .= " AND c.published=1 ";

		if ($params->get('catfilter'))
		{
			if (!is_null($cid))
			{
				if (is_array($cid))
				{
					JArrayHelper::toInteger($cid);
					$query .= " AND i.catid IN(".implode(',', $cid).")";
				}
				else
				{
					$query .= " AND i.catid=".(int)$cid;
				}
			}
		}

		if (K2_JVERSION != '15')
		{
			if ($mainframe->getLanguageFilter())
			{
				$languageTag = JFactory::getLanguage()->getTag();
				$query .= " AND category.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
			}
		}

		$query .= " ORDER BY c.commentDate DESC ";

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();
		$pattern = "@\b(https?://)?(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@";

		if (count($rows))
		{
			foreach ($rows as $row)
			{

				if ($params->get('commentDateFormat') == 'relative')
				{
					$config = JFactory::getConfig();
					$now = new JDate();
					if (K2_JVERSION == '30')
					{
						$tzoffset = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
						$now->setTimezone($tzoffset);
					}
					else
					{
						$tzoffset = $config->getValue('config.offset');
						$now->setOffset($tzoffset);
					}

					$created = new JDate($row->commentDate);
					$diff = $now->toUnix() - $created->toUnix();
					$dayDiff = floor($diff / 86400);

					if ($dayDiff == 0)
					{
						if ($diff < 5)
						{
							$row->commentDate = JText::_('K2_JUST_NOW');
						}
						elseif ($diff < 60)
						{
							$row->commentDate = $diff.' '.JText::_('K2_SECONDS_AGO');
						}
						elseif ($diff < 120)
						{
							$row->commentDate = JText::_('K2_1_MINUTE_AGO');
						}
						elseif ($diff < 3600)
						{
							$row->commentDate = floor($diff / 60).' '.JText::_('K2_MINUTES_AGO');
						}
						elseif ($diff < 7200)
						{
							$row->commentDate = JText::_('K2_1_HOUR_AGO');
						}
						elseif ($diff < 86400)
						{
							$row->commentDate = floor($diff / 3600).' '.JText::_('K2_HOURS_AGO');
						}
					}
				}
				$row->commentText = K2HelperUtilities::wordLimit($row->commentText, $params->get('comments_word_limit'));
				$row->commentText = preg_replace($pattern, '<a target="_blank" rel="nofollow" href="\0">\0</a>', $row->commentText);
				$row->itemLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($row->itemID.':'.urlencode($row->alias), $row->catid.':'.urlencode($row->catalias))));
				$row->link = $row->itemLink."#comment{$row->id}";
				$row->catLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->catid.':'.urlencode($row->catalias))));

				if ($row->userID > 0)
				{
					$row->userLink = JRoute::_(K2HelperRoute::getUserRoute($row->userID));
					$getExistingUser = JFactory::getUser($row->userID);
					$row->userUsername = $getExistingUser->username;
				}
				else
				{
					$row->userUsername = $row->userName;
				}

				// Switch between commenter name and username
				if ($params->get('commenterName', 1) == 2)
					$row->userName = $row->userUsername;

				$row->userImage = '';

				if ($params->get('commentAvatar'))
				{
					$row->userImage = K2HelperUtilities::getAvatar($row->userID, $row->commentEmail, $componentParams->get('commenterImgWidth'));
				}

				$comments[] = $row;

			}

			return $comments;
		}

	}

	public static function getTopCommenters(&$params)
	{

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
		$limit = $params->get('commenters_limit', '5');
		$user = JFactory::getUser();
		$aid = $user->get('aid');
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) as counter, userName, userID, commentEmail FROM #__k2_comments WHERE userID > 0 AND published = 1 GROUP BY userID ORDER BY counter DESC";
		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();
		$pattern = "@\b(https?://)?(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@";

		$model = K2Model::getInstance('Item', 'K2Model');

		$componentParams = JComponentHelper::getParams('com_k2');

		if (count($rows))
		{

			foreach ($rows as $row)
			{

				if ($row->counter > 0)
				{
					$row->link = JRoute::_(K2HelperRoute::getUserRoute($row->userID));

					if ($params->get('commenterNameOrUsername', 1) == 2)
					{
						$getExistingUser = JFactory::getUser($row->userID);
						$row->userName = $getExistingUser->username;
					}

					if ($params->get('commentAvatar'))
					{
						$row->userImage = K2HelperUtilities::getAvatar($row->userID, $row->commentEmail, $componentParams->get('commenterImgWidth'));
					}

					if ($params->get('commenterLatestComment'))
					{
						$query = "SELECT * FROM #__k2_comments WHERE userID = ".(int)$row->userID." AND published = 1 ORDER BY commentDate DESC";

						$db->setQuery($query, 0, 1);
						$comment = $db->loadObject();

						$item = JTable::getInstance('K2Item', 'Table');
						$item->load($comment->itemID);

						$category = JTable::getInstance('K2Category', 'Table');
						$category->load($item->catid);

						$row->latestCommentText = $comment->commentText;
						$row->latestCommentText = preg_replace($pattern, '<a target="_blank" rel="nofollow" href="\0">\0</a>', $row->latestCommentText);
						$row->latestCommentLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($category->alias))))."#comment{$comment->id}";
						$row->latestCommentDate = $comment->commentDate;
					}

					$commenters[] = $row;
				}
			}
			if (isset($commenters))
				return $commenters;
		}

	}

}
