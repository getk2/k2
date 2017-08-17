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

class K2ModelComments extends K2Model {

	function getData() {
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDbo();
		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $application->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.id', 'cmd');
		$filter_order_Dir = $application->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $application->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\"\.\@\-_]/u', '', $search));

		$query = "SELECT c.*, i.title , i.catid,  i.alias AS itemAlias, i.created_by,  cat.alias AS catAlias, cat.name as catName FROM #__k2_comments AS c LEFT JOIN #__k2_items AS i ON c.itemID=i.id LEFT JOIN #__k2_categories AS cat ON cat.id=i.catid LEFT JOIN #__k2_users AS u ON c.userID=u.userID WHERE c.id>0";

		if ($filter_state > - 1) {
			$query .= " AND c.published={$filter_state}";
		}

		if ($filter_category) {
			$query .= " AND i.catid={$filter_category}";
		}

		if ($filter_author) {
			$query .= " AND i.created_by={$filter_author}";
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
							"LOWER(c.commentText) LIKE ".$quotedWord." ".
							"OR LOWER(c.userName) LIKE ".$quotedWord." ".
							"OR LOWER(c.commentEmail) LIKE ".$quotedWord." ".
							"OR LOWER(c.commentURL) LIKE ".$quotedWord." ".
							"OR LOWER(i.title) LIKE ".$quotedWord." ".
							"OR LOWER(u.userName) LIKE ".$quotedWord." ".
							"OR LOWER(u.ip) LIKE ".$quotedWord." ".
							" )";
					}
				}
				else
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND LOWER(c.commentText) LIKE ".$quotedWord;
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
						"LOWER(c.commentText) LIKE ".$quoted." ".
						"OR LOWER(c.userName) LIKE ".$quoted." ".
						"OR LOWER(c.commentEmail) LIKE ".$quoted." ".
						"OR LOWER(c.commentURL) LIKE ".$quoted." ".
						"OR LOWER(i.title) LIKE ".$quoted." ".
						"OR LOWER(u.userName) LIKE ".$quoted." ".
						"OR LOWER(u.ip) LIKE ".$quoted." ".
						" )";
				}
				else
				{
					$query .= " AND LOWER(c.commentText) LIKE ".$quoted;
				}
			}
		}

		if (!$filter_order) {
			$filter_order = "c.commentDate";
		}

		$query .= " ORDER BY {$filter_order} {$filter_order_Dir}";
		$db->setQuery($query, $limitstart, $limit);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function getTotal() {
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDbo();
		$limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->getCfg('list_limit'), 'int');
		$limitstart = $application->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
		$filter_state = $application->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
		$filter_category = $application->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $application->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$search = $application->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);
		$search = trim(preg_replace('/[^\p{L}\p{N}\s\"\.\@\-_]/u', '', $search));

		$query = "SELECT COUNT(*) FROM #__k2_comments AS c LEFT JOIN #__k2_items AS i ON c.itemID=i.id LEFT JOIN #__k2_users AS u ON c.userID=u.userID WHERE c.id>0";

		if ($filter_state > - 1) {
			$query .= " AND c.published={$filter_state}";
		}

		if ($filter_category) {
			$query .= " AND i.catid={$filter_category}";
		}

		if ($filter_author) {
			$query .= " AND i.created_by={$filter_author}";
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
							"LOWER(c.commentText) LIKE ".$quotedWord." ".
							"OR LOWER(c.userName) LIKE ".$quotedWord." ".
							"OR LOWER(c.commentEmail) LIKE ".$quotedWord." ".
							"OR LOWER(c.commentURL) LIKE ".$quotedWord." ".
							"OR LOWER(i.title) LIKE ".$quotedWord." ".
							"OR LOWER(u.userName) LIKE ".$quotedWord." ".
							"OR LOWER(u.ip) LIKE ".$quotedWord." ".
							" )";
					}
				}
				else
				{
					foreach($quoted as $quotedWord)
					{
						$query .= " AND LOWER(c.commentText) LIKE ".$quotedWord;
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
						"LOWER(c.commentText) LIKE ".$quoted." ".
						"OR LOWER(c.userName) LIKE ".$quoted." ".
						"OR LOWER(c.commentEmail) LIKE ".$quoted." ".
						"OR LOWER(c.commentURL) LIKE ".$quoted." ".
						"OR LOWER(i.title) LIKE ".$quoted." ".
						"OR LOWER(u.userName) LIKE ".$quoted." ".
						"OR LOWER(u.ip) LIKE ".$quoted." ".
						" )";
				}
				else
				{
					$query .= " AND LOWER(c.commentText) LIKE ".$quoted;
				}
			}
		}
		$db->setQuery($query);
		$total = $db->loadresult();
		return $total;
	}

	function publish() {
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$cid = JRequest::getVar('cid');
	    if(!count($cid)){
            $cid[]=JRequest::getInt('commentID');
        }

		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($application->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$application->close();
				}
			}
			$row->published = 1;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('format')=='raw'){
			echo 'true';
			$application->close();
		}
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=comments');
		}
	}

	function unpublish() {
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($application->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$application->close();
				}
			}
			$row->published = 0;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=comments');
		}
	}

	function remove() {
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$cid = JRequest::getVar('cid');
	  	if(!count($cid)){
            $cid[]=JRequest::getInt('commentID');
        }
		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($application->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$application->close();
				}
			}
			$row->delete($id);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('format')=='raw'){
			echo 'true';
			$application->close();
		}
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=comments');
		}
	}

	function deleteUnpublished() {
		$application = JFactory::getApplication();
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$userID = $user->id;
		if($application->isSite()){
			$query = "SELECT c.id FROM #__k2_comments AS c
			LEFT JOIN #__k2_items AS i ON c.itemID=i.id
			WHERE i.created_by = {$userID} AND c.published=0";
			$db->setQuery($query);
			$ids = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
			if (count($ids)){
				$query = "DELETE FROM #__k2_comments WHERE id IN(".implode(',', $ids).")";
				$db->setQuery($query);
				$db->query();
			}
		}
		else {
			$query = "DELETE FROM #__k2_comments WHERE published=0";
			$db->setQuery($query);
			$db->query();
		}

		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		if(JRequest::getCmd('context') == "modalselector"){
			$application->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
		} else {
			$application->redirect('index.php?option=com_k2&view=comments');
		}
	}

	function save() {
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$id = JRequest::getInt('commentID');
		$item = JTable::getInstance('K2Item', 'Table');
		$row = JTable::getInstance('K2Comment', 'Table');
		$row->load($id);
		if($application->isSite()){
			$item->load($row->itemID);
			if ($item->created_by != $user->id) {
				JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
			}
		}
		$row->commentText = JRequest::getVar('commentText', '', 'default', 'string', 4);
		$row->store();
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$response = new JObject;
		$response->comment = $row->commentText;
		$response->message = JText::_('K2_COMMENT_SAVED');
		unset($response->_errors);
		echo json_encode($response);
		$application->close();
	}

    function report(){
        $id = $this->getState('id');
        $name = JString::trim($this->getState('name'));
        $reportReason = JString::trim($this->getState('reportReason'));
        $params = K2HelperUtilities::getParams('com_k2');
        $user = JFactory::getUser();
        $row = JTable::getInstance('K2Comment', 'Table');
        $row->load($id);
        if(!$row->published){
            $this->setError(JText::_('K2_COMMENT_NOT_FOUND'));
            return false;
        }
        if(empty($name)){
            $this->setError(JText::_('K2_PLEASE_TYPE_YOUR_NAME'));
            return false;
        }
        if(empty($reportReason)){
            $this->setError(JText::_('K2_PLEASE_TYPE_THE_REPORT_REASON'));
            return false;
        }
    	if (($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') && $user->guest) {

				if($params->get('recaptchaV2'))
				{
					require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
					if (!K2HelperUtilities::verifyRecaptcha())
					{
						$this->setError(JText::_('K2_COULD_NOT_VERIFY_THAT_YOU_ARE_NOT_A_ROBOT'));
						return false;
					}
				}
				else
				{
					if(!function_exists('_recaptcha_qsencode'))
					{
						require_once(JPATH_SITE.'/media/k2/assets/vendors/google/recaptcha_legacy/recaptcha.php');
					}
					$privatekey = $params->get('recaptcha_private_key');
					$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
					if (!$resp->is_valid) {
						$this->setError(JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN'));
						return false;
					}
				}
		}

		$application = JFactory::getApplication();
        $mail = JFactory::getMailer();
        $senderEmail = $application->getCfg('mailfrom');
        $senderName = $application->getCfg('fromname');

        $mail->setSender(array($senderEmail, $senderName));
        $mail->setSubject(JText::_('K2_COMMENT_REPORT'));
        $mail->IsHTML(true);

        switch(substr(strtoupper(PHP_OS), 0, 3)) {
            case 'WIN':
                $mail->LE = "\r\n";
                break;
            case 'MAC':
            case 'DAR':
                $mail->LE = "\r";
            default:
                break;
        }

		// K2 embedded email template (to do: move to separate HTML template/override)
        $body = "
        <strong>".JText::_('K2_NAME')."</strong>: ".$name." <br/>
        <strong>".JText::_('K2_REPORT_REASON')."</strong>: ".$reportReason." <br/>
        <strong>".JText::_('K2_COMMENT')."</strong>: ".nl2br($row->commentText)." <br/>
        ";

        $mail->setBody($body);
        $mail->ClearAddresses();
        $mail->AddAddress($params->get('commentsReportRecipient', $application->getCfg('mailfrom')));
        $mail->Send();

		return true;
    }
}
