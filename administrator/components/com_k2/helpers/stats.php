<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

class K2HelperStats
{

	public static function getScripts()
	{
		$data = self::getData();
		$token = version_compare(JVERSION, '2.5', 'ge') ? JSession::getFormToken() : JUtility::getToken();
		return "

		<script src=\"//cdnjs.cloudflare.com/ajax/libs/jquery-ajaxtransport-xdomainrequest/1.0.3/jquery.xdomainrequest.min.js\" type=\"text/javascript\"></script>
		<script type=\"text/javascript\">
			function K2LogResult(xhr) {
				\$K2.ajax({
					type : 'POST',
					url : 'index.php',
					data : { 'option' : 'com_k2', 'view' : 'items', 'task': 'logStats', '".$token."': '1', 'status' : xhr.status, 'response' : xhr.responseText }
				});
			}
			\$K2.ajax({
				crossDomain: true,
				type : 'POST',
				url : 'https://metrics.getk2.org/gather.php',
				data : ".$data."
			}).done(function(response, result, xhr) {
				K2LogResult(xhr);
			}).fail(function(xhr, result, response) {
				K2LogResult(xhr);
			});
		</script>

		";
	}

	public static function getData()
	{
		$data = new stdClass;
		$data->identifier = self::getIdentifier();
		$data->php = self::getPhpVersion();
		$data->databaseType = self::getDbType();
		$data->databaseVersion = self::getDbVersion();
		$data->server = self::getServer();
		$data->serverInterface = self::getServerInterface();
		$data->cms = self::getCmsVersion();
		$data->extensionName = 'K2';
		$data->extensionVersion = self::getExtensionVersion();
		$data->caching = self::getCaching();
		$data->cachingDriver = self::getCachingDriver();
		if (function_exists('json_encode'))
		{
			return json_encode($data);
		}
		else
		{
			require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php';
			$json = new Services_JSON;
			return $json->encode($data);
		}

	}

	public static function getIdentifier()
	{
		$configuration = JFactory::getConfig();
		$secret = version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('secret') : $configuration->getValue('config.secret');
		return md5($secret.$_SERVER['SERVER_ADDR']);
	}

	public static function getPhpVersion()
	{
		return phpversion();
	}

	public static function getDbType()
	{
		$configuration = JFactory::getConfig();
		$type = version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('dbtype') : $configuration->getValue('config.dbtype');
		if($type == 'mysql' || $type == 'mysqli' || $type == 'pdomysql')
		{
			$db = JFactory::getDbo();
			$query = 'SELECT version();';
			$db->setQuery($query);
			$result = $db->loadResult();
			$result = strtolower($result);
			if(strpos($result, 'mariadb') !== false)
			{
				$type = 'mariadb';
			}
		}
		return $type;
	}

	public static function getDbVersion()
	{
		$db = JFactory::getDbo();
		return $db->getVersion();
	}

	public static function getServer()
	{
		return isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE');
	}

	public static function getServerInterface()
	{
		return php_sapi_name();
	}

	public static function getCmsVersion()
	{
		return JVERSION;
	}

	public static function getExtensionVersion()
	{
		return '2.7.0';
	}

	public static function getCaching()
	{
		$configuration = JFactory::getConfig();
		return version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('caching') : $configuration->getValue('config.caching');
	}

	public static function getCachingDriver()
	{
		$configuration = JFactory::getConfig();
		return version_compare(JVERSION, '2.5', 'ge') ? $configuration->get('cache_handler') : $configuration->getValue('config.cache_handler');
	}

	public static function shouldLog()
	{
		$db = JFactory::getDbo();
		$query = 'SELECT * FROM #__k2_log';
		$db->setQuery($query, 0, 1);
		$result = $db->loadObject();
		if (!$result)
		{
			return true;
		}
		$now = JFactory::getDate()->toUnix();
		$days = floor(($now - strtotime($result->timestamp)) / (60 * 60 * 24));
		if ($days >= 30 || $result->status != 200)
		{
			return true;
		}

		return false;
	}

}
