<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/activityrun.lock');

//	标定环境为Cron
define('IN_CRON', true);
define('APPLICATION_ENV', 'new_testing_main');
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
/*
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
*/
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
$now = time();
$sql1 = "update better_poi_activity set checked=1 where checked=0 and begintm<=".$now." and endtm>=".$now;
$sql2 = "update better_poi_activity set checked=0 where checked=1 and (begintm>=".$now." or endtm<=".$now.")";
$pois = Better_DAO_Base::registerDbConnection('poi_server');
$rpoisdb = &$pois;	
$rs1 = Better_DAO_Base::squery($sql1, $rpoisdb);
$rs2 = Better_DAO_Base::squery($sql2, $rpoisdb);




$commondb = Better_DAO_Base::registerDbConnection('common_server');

$rcommondb = &$commondb;


//把过期的干掉
$sqlbanneroffline = "update better_webbanner set checked=0 where checked=1 and (begintm>=".$now." or endtm<=".$now.")";
$offline = Better_DAO_Base::squery($sqlbanneroffline, $rcommondb);

$sqlbanneronline = "select id,rank,checked from better_webbanner where (checked=0 and begintm<=".$now." and endtm>=".$now.") or (checked=1) order by rank,uptm desc";
$online = Better_DAO_Base::squery($sqlbanneronline, $rcommondb);

$banner_online = $online->fetchAll();
foreach($banner_online as $key =>$row){
	$rank = $key +1;	
	$sqlonline = "update better_webbanner set checked=1,rank=".$rank." where id=".$row['id'];
	$changestatus = Better_DAO_Base::squery($sqlonline, $rcommondb);
}


exit(0);
