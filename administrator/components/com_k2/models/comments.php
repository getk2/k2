<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2025 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

class K2ModelComments extends K2Model
{
    private $getTotal;

    public function getData()
    {
        $app              = JFactory::getApplication();
        $params           = JComponentHelper::getParams('com_k2');
        $option           = JRequest::getCmd('option');
        $view             = JRequest::getCmd('view');
        $db               = JFactory::getDbo();
        $limit            = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart       = $app->getUserStateFromRequest($option . $view . '.limitstart', 'limitstart', 0, 'int');
        $filter_order     = $app->getUserStateFromRequest($option . $view . 'filter_order', 'filter_order', 'c.id', 'cmd');
        $filter_order_Dir = $app->getUserStateFromRequest($option . $view . 'filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
        $filter_state     = $app->getUserStateFromRequest($option . $view . 'filter_state', 'filter_state', -1, 'int');
        $filter_category  = $app->getUserStateFromRequest($option . $view . 'filter_category', 'filter_category', 0, 'int');
        $filter_author    = $app->getUserStateFromRequest($option . $view . 'filter_author', 'filter_author', 0, 'int');
        $search           = $app->getUserStateFromRequest($option . $view . 'search', 'search', '', 'string');

        $queryStart = "/* Backend / K2 / Comments */ SELECT c.*, i.title , i.catid, i.alias AS itemAlias, i.created_by, cat.alias AS catAlias, cat.name as catName";

        $query = " FROM #__k2_comments AS c
			LEFT JOIN #__k2_items AS i ON c.itemID = i.id
			LEFT JOIN #__k2_categories AS cat ON cat.id = i.catid
			LEFT JOIN #__k2_users AS u ON c.userID = u.userID
			WHERE c.id > 0";

        if ($filter_state > -1) {
            $query .= " AND c.published = {$filter_state}";
        }

        if ($filter_category) {
            $query .= " AND i.catid = {$filter_category}";
        }

        if ($filter_author) {
            $query .= " AND i.created_by = {$filter_author}";
        }

        if ($search) {
            if ($params->get('adminSearch') == 'full') {
                $query .= K2GlobalHelper::search($search, [
                    'c.commentText',
                    'c.userName',
                    'c.commentEmail',
                    'c.commentURL',
                    'i.title',
                    'u.userName',
                    'u.ip',
                ]);
            } else {
                $query .= K2GlobalHelper::search($search, [
                    'c.commentText',
                ]);
            }
        }

        if (! $filter_order) {
            $filter_order = "c.commentDate";
        }
        $queryEnd = " ORDER BY {$filter_order} {$filter_order_Dir}";

        // --- Final query ---
        $combinedQuery = $queryStart . $query . $queryEnd;

        $db->setQuery($combinedQuery, $limitstart, $limit);
        $rows = $db->loadObjectList();

        // --- Row counter ---
        if (count($rows)) {
            $countQuery = "/* Backend / K2 / Comments Count */ SELECT COUNT(*)" . $query;
            $db->setQuery($countQuery);
            $this->getTotal = $db->loadResult();
        }

        return $rows;
    }

    public function getTotal()
    {
        return $this->getTotal;
    }

    public function publish()
    {
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();
        $cid  = JRequest::getVar('cid');
        if (! count($cid)) {
            $cid[] = JRequest::getInt('commentID');
        }

        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Comment', 'Table');
            $row->load($id);
            if ($app->isSite()) {
                $item = JTable::getInstance('K2Item', 'Table');
                $item->load($row->itemID);
                if ($item->created_by != $user->id) {
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                    $app->close();
                }
            }
            $row->published = 1;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if (JRequest::getCmd('format') == 'raw') {
            echo 'true';
            $app->close();
        }
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=comments');
        }
    }

    public function unpublish()
    {
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();
        $cid  = JRequest::getVar('cid');
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Comment', 'Table');
            $row->load($id);
            if ($app->isSite()) {
                $item = JTable::getInstance('K2Item', 'Table');
                $item->load($row->itemID);
                if ($item->created_by != $user->id) {
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                    $app->close();
                }
            }
            $row->published = 0;
            $row->store();
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=comments');
        }
    }

    public function remove()
    {
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();
        $db   = JFactory::getDbo();
        $cid  = JRequest::getVar('cid');
        if (! count($cid)) {
            $cid[] = JRequest::getInt('commentID');
        }
        foreach ($cid as $id) {
            $row = JTable::getInstance('K2Comment', 'Table');
            $row->load($id);
            if ($app->isSite()) {
                $item = JTable::getInstance('K2Item', 'Table');
                $item->load($row->itemID);
                if ($item->created_by != $user->id) {
                    JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
                    $app->close();
                }
            }
            $row->delete($id);
        }
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        if (JRequest::getCmd('format') == 'raw') {
            echo 'true';
            $app->close();
        }
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=comments');
        }
    }

    public function deleteUnpublished()
    {
        $app    = JFactory::getApplication();
        $db     = JFactory::getDbo();
        $user   = JFactory::getUser();
        $userID = $user->id;
        if ($app->isSite()) {
            $query = "SELECT c.id FROM #__k2_comments AS c
			LEFT JOIN #__k2_items AS i ON c.itemID=i.id
			WHERE i.created_by = {$userID} AND c.published=0";
            $db->setQuery($query);
            $ids = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
            if (count($ids)) {
                $query = "DELETE FROM #__k2_comments WHERE id IN(" . implode(',', $ids) . ")";
                $db->setQuery($query);
                $db->query();
            }
        } else {
            $query = "DELETE FROM #__k2_comments WHERE published=0";
            $db->setQuery($query);
            $db->query();
        }

        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $app->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
        if (JRequest::getCmd('context') == "modalselector") {
            $app->redirect('index.php?option=com_k2&view=comments&tmpl=component&context=modalselector');
        } else {
            $app->redirect('index.php?option=com_k2&view=comments');
        }
    }

    public function save()
    {
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();
        $db   = JFactory::getDbo();
        $id   = JRequest::getInt('commentID');
        $item = JTable::getInstance('K2Item', 'Table');
        $row  = JTable::getInstance('K2Comment', 'Table');
        $row->load($id);
        if ($app->isSite()) {
            $item->load($row->itemID);
            if ($item->created_by != $user->id) {
                JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
            }
        }
        $row->commentText = JRequest::getVar('commentText', '', 'default', 'string', 4);
        $row->store();
        $cache = JFactory::getCache('com_k2');
        $cache->clean();
        $response          = new stdClass();
        $response->comment = $row->commentText;
        $response->message = JText::_('K2_COMMENT_SAVED');
        echo json_encode($response);
        $app->close();
    }

    public function report()
    {
        $id           = $this->getState('id');
        $name         = JString::trim($this->getState('name'));
        $reportReason = JString::trim($this->getState('reportReason'));
        $params       = K2HelperUtilities::getParams('com_k2');
        $user         = JFactory::getUser();
        $row          = JTable::getInstance('K2Comment', 'Table');
        $row->load($id);
        if (! $row->published) {
            $this->setError(JText::_('K2_COMMENT_NOT_FOUND'));
            return false;
        }
        if (empty($name)) {
            $this->setError(JText::_('K2_PLEASE_TYPE_YOUR_NAME'));
            return false;
        }
        if (empty($reportReason)) {
            $this->setError(JText::_('K2_PLEASE_TYPE_THE_REPORT_REASON'));
            return false;
        }
        if (($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') && $user->guest) {
            require_once JPATH_SITE . '/components/com_k2/helpers/utilities.php';
            if (! K2HelperUtilities::verifyRecaptcha()) {
                $this->setError(JText::_('K2_COULD_NOT_VERIFY_THAT_YOU_ARE_NOT_A_ROBOT'));
                return false;
            }
        }

        $app         = JFactory::getApplication();
        $mail        = JFactory::getMailer();
        $senderEmail = $app->getCfg('mailfrom');
        $senderName  = $app->getCfg('fromname');

        $mail->setSender([$senderEmail, $senderName]);
        $mail->setSubject(JText::_('K2_COMMENT_REPORT'));
        $mail->IsHTML(true);

        switch (substr(strtoupper(PHP_OS), 0, 3)) {
            case 'WIN':
                $mail->LE = "\r\n";
                break;
            case 'MAC':
            case 'DAR':
                $mail->LE = "\r";
            // no break
            default:
                break;
        }

        // K2 embedded email template (to do: move to separate HTML template/override)
        $body = "
        <strong>" . JText::_('K2_NAME') . "</strong>: " . $name . " <br/>
        <strong>" . JText::_('K2_REPORT_REASON') . "</strong>: " . $reportReason . " <br/>
        <strong>" . JText::_('K2_COMMENT') . "</strong>: " . nl2br($row->commentText) . " <br/>
        ";

        $mail->setBody($body);
        $mail->ClearAddresses();
        $mail->AddAddress($params->get('commentsReportRecipient', $app->getCfg('mailfrom')));
        $mail->Send();

        return true;
    }
}
