<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/fix_counters.lock');

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

$sids = Better_DAO_User_Assign::getInstance()->getServerIds();


$k = 0;
$i = 0;
$j =0;
foreach($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$pois = Better_DAO_Base::registerDbConnection('poi_server');
	$rdb = &$cs['r'];
	$wdb = &$cs['w'];
	$rpoisdb = &$pois;
     //  $sql = "SELECT last_checkin_poi as poi_id,uid FROM better_profile where last_checkin_poi!=0 and (live_province IS  NULL or live_city is null)  order by last_active desc";

        $sql = "SELECT ut.uid,ut.dateline,ub.bid,p.state FROM better_profile as p,better_3rdbinding as ut left join better_user_badges as ub on ut.uid=ub.uid and ub.bid=159  where ut.protocol='fanfou.com' and p.uid=ut.uid and p.state='enabled'";
        $rs = Better_DAO_Base::squery($sql, $rdb);
        $rows = $rs->fetchAll();
        $whatcity = array();
        foreach ($rows as $row) {
        	$uid = $row['uid'];
        	$dateline =  $row['dateline'];
        	//$user = Better_User::getInstance($uid);
			//$userInfo = $user->getUser();
        	if(!$row['bid']){
        		$sql = "insert into better_user_badges(uid,bid,get_time,poi_id) values(".$uid.",159,".$dateline.",0)";
        		Better_DAO_Base::squery($sql, $wdb);
        		echo $uid." Had Done.\n";
        		$i++;
        	} else {
        		echo $uid." Had not  Done.\n";
        		$j++;
        	}   	
									
		}
	
	
		
}
	echo $i." Had Done.\n";
		echo $j." Had not Done.\n";
echo "Done.\n";
exit(0);
