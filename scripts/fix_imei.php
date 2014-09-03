<?php

/**
 * 修复imei推广数据
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/imei.lock');

//	标定环境为Cron
define('IN_CRON', true);
define('BETTER_FORCE_INCLUDE', true);

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

$tbl = 'better_imei_mirror';
$now = time();
$from = date('Y-m-d H:i:s', $now - 900);
$to = date('Y-m-d H:i:s', $now);

$db = Better_DAO_Base::registerDbConnection('common_server');
$rdb = &$db;
$wdb = &$db;

$sids = Better_DAO_User_Assign::getInstance()->getServerIds();

$sql = "SELECT DISTINCT(reg_partner) AS p, COUNT(*) AS t FROM better_imei WHERE FROM_UNIXTIME(reg_last_active) BETWEEN '".$from."' AND '".$to."' GROUP BY reg_partner HAVING t>0 ORDER BY t DESC";
$rs = $rdb->query($sql);
$rows = $rs->fetchAll();
echo "From: ".$from.", To:".$to."\n===========================================\n";
foreach ($rows as $row) {
	$all = array();
	$already = array();
	$todo = array();
	$partner = $row['p'];
	
	$sql = "SELECT * FROM better_imei WHERE FROM_UNIXTIME(reg_last_active) BETWEEN '".$from."' AND '".$to."' AND reg_partner='".$partner."'";
	$rs = $rdb->query($sql);
	$rows2 = $rs->fetchAll();
	
	foreach ($rows2 as $row2) {
		$uid = $row2['reg_uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		if ($userInfo['state']!=Better_User_State::SIGNUP_VALIDATING) {
			$all[] = $uid;
		}
	}
	
	$total = count($all);
	if ($total>0) {
		$sql = "SELECT * FROM ".$tbl." WHERE FROM_UNIXTIME(reg_last_active) BETWEEN '".$from."' AND '".$to."' AND reg_partner='".$partner."'";
		$rs = $rdb->query($sql);
		$rows3 = $rs->fetchAll();
		
		foreach ($rows3 as $row3) {
			$already[] = $row3['reg_uid'];
		}

		$rs = $rdb->query("SELECT * FROM better_adrate WHERE partno='".substr($partner, -4)."'");
		$d = $rs->fetch();
		$rate = isset($d['rate']) ? $d['rate'] : 1;
				
		$delta = intval($total*$rate) - count($already);
                echo "Partner:[".$partner."], All:[".count($all)."], Already:[".count($already)."], Delta:[".$delta."], Rate:[".$rate."]\n";
		if ($delta>0) {
			$todo = array_diff($all, $already);
			$done = 0;
                        echo "Todo:[".count($todo)."]\n";
                        $_x = 0;
			foreach ($todo as $uid) {
				if ($done<$delta) {
					$sql = "REPLACE INTO ".$tbl." SELECT * FROM better_imei WHERE reg_uid=".$uid." ";
                    $wdb->query($sql);
					$done++;
				}
			}
                        echo "Done:[".$done."]\n";
		}
	}
	
	echo "Partner: ".$partner." done\n";
	echo "=================================\n\n";
}

echo "Done.\n";
exit(0);
