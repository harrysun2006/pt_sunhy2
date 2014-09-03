<?php
 
define('RSS_LOCK', dirname(__FILE__).'/rss.lock');
define('IN_CRON', true);

 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/models'),
    get_include_path(),
)));

 function killLock()
{
	file_exists(RSS_LOCK) && unlink(RSS_LOCK); 
}

set_time_limit(0);
date_default_timezone_set('UTC');
error_reporting(E_ALL);

 // 检测是否有同步锁
if (file_exists(RSS_LOCK)) {
 	exit(0);
}

register_shutdown_function('killLock');
require_once 'Zend/Exception.php';
require_once 'Zend/Debug.php';
require_once 'Zend/Feed/Reader.php';
require_once realpath(dirname(__FILE__).'/../library/Better').'/Log.php';

$conn = mysql_connect('127.0.0.1', 'user0_write', '123456');
mysql_select_db('better_user_part0', $conn);
mysql_query('SET NAMES utf8');

$sql = "SELECT * FROM better_rss_import";
$rs = mysql_query($sql, $conn);
while ($r = mysql_fetch_array($rs)) {
	$url = $r['url'];
	$uid = $r['uid'];
	
	try {
		$feed = Zend_Feed_Reader::import($url);
		Zend_Debug::dump($feed->current());exit;
	} catch (Zend_Feed_Exception $fe) {
		Better_Log::getInstance()->logAlert('Feed import failed ['.$uid.']['.$url.']', 'rss');
	} catch (Exception $e) {
		Better_Log::getInstance()->logEmerg('Feed import exception ['.$uid.']['.$url.']', 'rss');
	}
	
}

echo "Done.\n";
mysql_close($conn);