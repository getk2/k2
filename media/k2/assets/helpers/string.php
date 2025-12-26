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

class K2String
{
    private static $useMb    = null;
    private static $encoding = 'UTF-8';

    private static function init()
    {
        if (self::$useMb === null) {
            self::$useMb = function_exists('mb_strlen');
        }
    }

    public static function strlen($str)
    {
        self::init();
        return self::$useMb
            ? mb_strlen($str, self::$encoding)
            : strlen($str);
    }

    public static function substr($str, $start, $length = null)
    {
        self::init();

        if (self::$useMb) {
            return ($length === null)
                ? mb_substr($str, $start, null, self::$encoding)
                : mb_substr($str, $start, $length, self::$encoding);
        }

        return ($length === null)
            ? substr($str, $start)
            : substr($str, $start, $length);
    }

    public static function strpos($haystack, $needle, $offset = 0)
    {
        self::init();
        return self::$useMb
            ? mb_strpos($haystack, $needle, $offset, self::$encoding)
            : strpos($haystack, $needle, $offset);
    }

    public static function stripos($haystack, $needle, $offset = 0)
    {
        self::init();
        return self::$useMb
            ? mb_stripos($haystack, $needle, $offset, self::$encoding)
            : stripos($haystack, $needle, $offset);
    }

    public static function strtolower($str)
    {
        self::init();
        return self::$useMb
            ? mb_strtolower($str, self::$encoding)
            : strtolower($str);
    }

    public static function strtoupper($str)
    {
        self::init();
        return self::$useMb
            ? mb_strtoupper($str, self::$encoding)
            : strtoupper($str);
    }

    public static function str_replace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }

    public static function trim($str, $characters = " \t\n\r\0\x0B")
    {
        return trim($str, $characters);
    }
}
