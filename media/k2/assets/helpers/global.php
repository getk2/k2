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

class K2GlobalHelper
{
    public static function search($text, $fields = [])
    {
        $sql    = '';
        $text   = trim(preg_replace('/[^\p{L}\p{N}\s\-.,:!?\'"()]/u', '', $text));
        $length = JString::strlen($text);
        if ($length > 2) {
            $db = JFactory::getDbo();

            // Detect exact search phrase using double quotes in search string
            if (substr($text, 0, 1) == '"' && substr($text, $length - 1, 1) == '"') {
                $type = 'exact';
            } else {
                $type = 'any';
            }

            if ($type == 'exact') {
                $text = trim($text, '"');

                $escaped = (K2_JVERSION == '15') ? $db->getEscaped($text, true) : $db->escape($text, true);
                $quoted  = $db->Quote('%' . $escaped . '%', false);

                $sql .= " AND (";
                foreach ($fields as $count => $field) {
                    $sql .= "LOWER(" . $field . ") LIKE " . $quoted;
                    if ($count < count($fields) - 1) {
                        $sql .= " OR ";
                    }
                }
                $sql .= ")";
            } else {
                $searchWords = explode(' ', $text);
                if (!count($searchWords)) {
                    $searchWords = [$text];
                }

                $searchPerTerm = [];

                $sql .= " AND (";
                foreach ($searchWords as $searchWord) {
                    if (strlen($searchWord) > 1) {
                        $escaped = (K2_JVERSION == '15') ? $db->getEscaped($searchWord, true) : $db->escape($searchWord, true);
                        $quoted  = $db->Quote('%' . $escaped . '%', false);

                        $searchString = "(";
                        foreach ($fields as $count => $field) {
                            $searchString .= "LOWER(" . $field . ") LIKE " . $quoted;
                            if ($count < count($fields) - 1) {
                                $searchString .= " OR ";
                            }
                        }
                        $searchString .= ")";

                        $searchPerTerm[] = $searchString;
                    }
                }
                $sql .= implode(' AND ', $searchPerTerm);
                $sql .= ")";
            }
        }
        return $sql;
    }
}
