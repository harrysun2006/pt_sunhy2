<?php

/**
 * 修正已经绑定用户的3rdfollowid
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

define('SYNC_BLOG_LOCK', dirname(__FILE__).'/sync_3rdfollowid.lock');
define('THIS_PID', dirname(__FILE__).'/sync_3rdfollowid.pid');


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
define('BETTER_SYS_UID', $appConfig->user->sys_user_id);
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
			//'kaixin001.com',
			'fanfou.com',
			//'sohu.com',
			//'digu.com',
			//'follow5.com',
			'qq.com'
			);


while (true) {
	$rows = Better_DAO_NewBind::getInstance()->getNew();	
	foreach ($rows as $row) {
		$id = $row['id'];
		$uid = $row['uid'];
		$protocol = $row['protocol'];
		$username = $row['username'];
		$password = $row['password'];
		$dateline = $row['dateline'];
		$oauth_token = $row['oauth_token'];
		$oauth_token_secret = $row['oauth_token_secret'];
		
		if (!in_array($protocol, $protocols)) continue;
		
		$service = Better_Service_PushToOtherSites::factory($protocol, $username, $password, $oauth_token, $oauth_token_secret);
		$logined = $service->fakeLogin();
		if (!$logined) continue;
		
		$userinfo = Better_User::getInstance($uid);
		
		$followers = $service->getFollowerids();
		foreach ($followers as $fid) {
			$user_info = Better_DAO_ThirdBinding::getBindUser($protocol, $fid);
			if ($user_info['uid']) {
				$is_friend = $userinfo->isFriend($user_info['uid']);
				if ($is_friend) continue;
				//记录下来 通知 $user_info['uid'] 这个UID来啦
				
				$data = array(
							'uid' => $user_info['uid'],
							'refuid' => $uid,
							'dateline' => time(),
							'flag' => 0,
							);
				Better_DAO_PushFriend::getInstance($user_info['uid'])->replace($data);
			}
			
		}
		
		$friends = $service->getFriends();
		foreach ($friends as $fid) {
			$user_info = Better_DAO_ThirdBinding::getBindUser($protocol, $fid);
			if ($user_info['uid']) {
				$is_friend = $userinfo->isFriend($user_info['uid']);
				if ($is_friend) continue;
				//记录 通知UID   这个$user_info['uid']来啦
				$data = array(
							'uid' => $uid,
							'refuid' => $user_info['uid'],
							'dateline' => time(),
							'flag' => 0,
							);
				Better_DAO_PushFriend::getInstance($user_info['uid'])->replace($data);
			}
				
		}
		
		Better_DAO_NewBind::getInstance($uid)->delete($id);
	}

	sleep(2);
}			



			
			
exit(0);
