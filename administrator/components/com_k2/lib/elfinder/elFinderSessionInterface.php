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

/**
 * elFinder - file manager for web.
 * Session Wrapper Interface.
 *
 * @package elfinder
 * @author Naoki Sawada
 **/

interface elFinderSessionInterface
{
	/**
	 * Session start
	 *
	 * @return  self
	 **/
	public function start();

	/**
	 * Session write & close
	 *
	 * @return  self
	 **/
	public function close();

	/**
	 * Get session data
	 *
	 * This method must be equipped with an automatic start / close.
	 *
	 * @param   string  $key   Target key
	 * @param   mixed   $empty Return value of if session target key does not exist
	 *
	 * @return  mixed
	 **/
	public function get($key, $empty = '');

	/**
	 * Set session data
	 *
	 * This method must be equipped with an automatic start / close.
	 *
	 * @param   string  $key   Target key
	 * @param   mixed   $data  Value
	 *
	 * @return  self
	 **/
	public function set($key, $data);

	/**
	 * Get session data
	 *
	 * @param   string  $key   Target key
	 *
	 * @return  self
	 **/
	public function remove($key);
}
