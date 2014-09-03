<?php

/**
 * 同步地方用户关注的人
 * 
 * @package scripts
 * @author fengjun <fengj@peptalk.cn>
 */
ini_set('display_errors', 1);
ini_set('error_reporting', 'E_ALL & ~E_NOTICE');

$args = $_SERVER['argv'];
unset($args[0]);

define('BETTER_FORCE_INCLUDE', true);
define('BETTER_START_TIME', microtime());

//	进程锁

define('SYNC_BLOG_LOCK', dirname(__FILE__).'/sync_friends.lock');
define('THIS_PID', dirname(__FILE__).'/sync_friends.pid');


//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


function killLock() 
{ 
	file_exists(SYNC_BLOG_LOCK) && unlink(SYNC_BLOG_LOCK); 
}

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');
 
// 检测是否有同步锁
file_exists(SYNC_BLOG_LOCK) && exit(0);
 
//	没有同步锁则继续执行同步操作
register_shutdown_function('killLock');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Better/Cache.php';
require_once 'Better/Cache/Handler/Base.php';
require_once 'Better/Loader.php';
Better_Loader::getInstance()->register();

Better_Config::load();
$appConfig = Better_Config::getAppConfig();

//	定义常量，减少array_key_exists调用
define('BETTER_NOW', time());
define('BETTER_BASE_URL', Better_Config::getAppConfig()->base_url);
define('BETTER_DB_TBL_PREFIX', Better_Config::getDbConfig()->global->tbl_prefix);
define('BETTER_SYS_UID', $aPpConfig->user->sys_user_id);
define('BETTER_VIRTUAL_UID', $appConfig->user->virtual_user_id);
define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);
define('BETTER_LOG_HASH', md5(uniqid(rand())));
define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);


$protocols = array(
				'sina.com',
				'qq.com',
			);

$rows = Better_DAO_ThirdBinding::getAllBind($protocols);
var_dump(count($rows));

foreach ($rows as $row) {
	$protocol = $row['protocol'];
	$username = $row['username'];
	$password = $row['password'];
	$oauth_token = $row['oauth_token'];
	$oauth_token_secret = $row['oauth_token_secret'];
	$uid = $row['uid'];
	$tid = $row['tid'];
	if (!$tid && $protocol == 'qq.com') {
		$tid = $username;
	}	
	
	if (!$oauth_token || !$oauth_token_secret || !$tid) continue;
	$service = Better_Service_PushToOtherSites::factory($protocol, '', '', $oauth_token, $oauth_token_secret);
	$friends = $service->getFriends();
	if (!$friends) continue;	
	addFriends($protocol, $tid, $friends, $uid);
var_dump($protocol . ',' . $uid . ',' . count($friends));	
}


function addFriends($protocol, $tid, $friends, $uid )
{
	$data['partner'] = $protocol;
	$data['tid'] = $tid;
	$data['tid_friends'] = join(',', $friends);
	$data['uid'] = $uid;
	
	Better_DAO_3rdFriends::getInstance($uid)->replace($data);
}


exit(0);