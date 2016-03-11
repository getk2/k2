<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

class K2ModelComments extends K2Model {

	function getData() {

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDBO();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');
		$filter_order = $mainframe->getUserStateFromRequest($option.$view.'filter_order', 'filter_order', 'c.id', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option.$view.'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', -1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);

		$query = "SELECT c.*, i.title , i.catid,  i.alias AS itemAlias, i.created_by,  cat.alias AS catAlias, cat.name as catName FROM #__k2_comments AS c LEFT JOIN #__k2_items AS i ON c.itemID=i.id LEFT JOIN #__k2_categories AS cat ON cat.id=i.catid WHERE c.id>0";

		if ($filter_state > - 1) {
			$query .= " AND c.published={$filter_state}";
		}

		if ($filter_category) {
			$query .= " AND i.catid={$filter_category}";
		}

		if ($filter_author) {
			$query .= " AND i.created_by={$filter_author}";
		}

		if ($search) {
		    $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$query .= " AND LOWER( c.commentText ) LIKE ".$db->Quote('%'.$escaped.'%', false);
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

		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$db = JFactory::getDBO();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
		$filter_state = $mainframe->getUserStateFromRequest($option.$view.'filter_state', 'filter_state', 1, 'int');
		$filter_category = $mainframe->getUserStateFromRequest($option.$view.'filter_category', 'filter_category', 0, 'int');
		$filter_author = $mainframe->getUserStateFromRequest($option.$view.'filter_author', 'filter_author', 0, 'int');
		$search = $mainframe->getUserStateFromRequest($option.$view.'search', 'search', '', 'string');
		$search = JString::strtolower($search);

		$query = "SELECT COUNT(*) FROM #__k2_comments AS c LEFT JOIN #__k2_items AS i ON c.itemID=i.id WHERE c.id>0";

		if ($filter_state > - 1) {
			$query .= " AND c.published={$filter_state}";
		}

		if ($filter_category) {
			$query .= " AND i.catid={$filter_category}";
		}

		if ($filter_author) {
			$query .= " AND i.created_by={$filter_author}";
		}

		if ($search) {
		    $escaped = K2_JVERSION == '15' ? $db->getEscaped($search, true) : $db->escape($search, true);
			$query .= " AND LOWER( c.commentText ) LIKE ".$db->Quote('%'.$escaped.'%', false);
		}

		$db->setQuery($query);
		$total = $db->loadresult();
		return $total;
	}

	function publish() {

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$cid = JRequest::getVar('cid');
	    if(!count($cid)){
            $cid[]=JRequest::getInt('commentID');
        }

		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($mainframe->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$mainframe->close();
				}
			}
			$row->published = 1;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('format')=='raw'){
			echo 'true';
			$mainframe->close();
		}
		$mainframe->redirect('index.php?option=com_k2&view=comments');
	}

	function unpublish() {

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$cid = JRequest::getVar('cid');
		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($mainframe->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$mainframe->close();
				}
			}
			$row->published = 0;
			$row->store();
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		$mainframe->redirect('index.php?option=com_k2&view=comments');
	}

	function remove() {

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
	  	if(!count($cid)){
            $cid[]=JRequest::getInt('commentID');
        }
		foreach ($cid as $id) {
			$row = JTable::getInstance('K2Comment', 'Table');
			$row->load($id);
			if($mainframe->isSite()){
				$item = JTable::getInstance('K2Item', 'Table');
				$item->load($row->itemID);
				if ($item->created_by != $user->id) {
					JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
					$mainframe->close();
				}
			}
			$row->delete($id);
		}
		$cache = JFactory::getCache('com_k2');
		$cache->clean();
		if(JRequest::getCmd('format')=='raw'){
			echo 'true';
			$mainframe->close();
		}
		$mainframe->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=comments');
	}

	function deleteUnpublished() {

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$userID = $user->id;
		if($mainframe->isSite()){
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
		$mainframe->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		$mainframe->redirect('index.php?option=com_k2&view=comments');
	}

	function save() {

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$id = JRequest::getInt('commentID');
		$item = JTable::getInstance('K2Item', 'Table');
		$row = JTable::getInstance('K2Comment', 'Table');
		$row->load($id);
		if($mainframe->isSite()){
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
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		echo $json->encode($response);
		$mainframe->close();
	}

    function report(){
        $id = $this->getState('id');
        $name = JString::trim($this->getState('name'));
        $reportReason = JString::trim($this->getState('reportReason'));
        $params = &K2HelperUtilities::getParams('com_k2');
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
								require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'recaptchalib.php');
						}
						$privatekey = $params->get('recaptcha_private_key');
						$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
						if (!$resp->is_valid) {
							$this->setError(JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN'));
							return false;
						}
				}

		}

		$mainframe = JFactory::getApplication();
        $mail = JFactory::getMailer();
        $senderEmail = $mainframe->getCfg('mailfrom');
        $senderName = $mainframe->getCfg('fromname');

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

        $body = "
        <strong>".JText::_('K2_NAME')."</strong>: ".$name." <br/>
        <strong>".JText::_('K2_REPORT_REASON')."</strong>: ".$reportReason." <br/>
        <strong>".JText::_('K2_COMMENT')."</strong>: ".nl2br($row->commentText)." <br/>
        ";

        $mail->setBody($body);
        $mail->ClearAddresses();
        $mail->AddAddress($params->get('commentsReportRecipient', $mainframe->getCfg('mailfrom')));
        $mail->Send();

		return true;

    }

}
