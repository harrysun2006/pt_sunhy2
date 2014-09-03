<?php

/**
 * 修复用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/sync_blog.lock');

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

define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);

define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);

$sess = Better_Session::factory();
$sess->init();

$sids = Better_DAO_User_Assign::getInstance()->getAll();

foreach($sids as $row) {
	$uid = $row['uid'];
	
	$trs = Better_DAO_User_Treasure::getInstance($uid)->getAll(array(
		'uid' => $uid,
		));	
	$ts = count($trs);
	
	$frs = Better_DAO_Friends::getInstance($uid)->getAll(array(
		'uid' => $uid,
		));
	$fs = count($frs);
	
	$bds = Better_DAO_User_Badge::getInstance($uid)->getAll(array(
		'uid' => $uid,
		));
	$bs = count($bds);
	
	$mjs = Better_DAO_Poi_Info::getInstance()->getAll(array(
		'major' => $uid,
		));
	$ms = count($mjs);
	
	$_fls = Better_DAO_Follower::getInstance($uid)->getAll(array(
		'uid' => $uid,
		));
	$fls = count($_fls);
	
	$_fis = Better_DAO_Following::getInstance($uid)->getAll(array(
		'uid' => $uid,
		));
	$fis = count($_fis);
	
	$checkins = Better_DAO_User_Blog::getInstance($uid)->getCount('checkin');
	
	$nowPosts = Better_DAO_User_Blog::getInstance($uid)->getCount('normal');
	
	Better_DAO_User::getInstance($uid)->update(array(
		'checkins' => $checkins,
		'now_posts' => $nowPosts,
		'majors' => $ms,
		'followings' => $fis,
		'followers' => $fls,
		'badges' => $bs,
		'friends' => $fs,
		'treasures' => $ts,
		'uid' => $uid,
		));
	echo $uid.":[".$ts."], friends:[".$fs."], Checkins:[".$checkins."], Posts:[".$nowPosts."], Majors:[".$ms."], Followings:[".$fis."], Followers:[".$fls."], Badges:[".$bs."]\n";
}
echo "Done.\n";
exit(0);
