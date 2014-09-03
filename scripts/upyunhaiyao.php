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
$checkintime = time()-60;
$fcheckintime = $checkintime-1800;
$total = 0;
$poi_list = array(6855779,19051741);
$poistr = "6855779,19051741";
$sql = "select poi_id,uid from better_user_place_log where checkin_time>=".$checkintime." and poi_id in(".$poistr.")  and checkin_score>0 group by poi_id,uid";

foreach($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);				
	$rdb = &$cs['r'];
	$wdb = &$cs['w'];	
	$rs = Better_DAO_Base::squery($sql, $rdb);			
	$data = $rs->fetchAll();
	foreach($data as $row){		
		$params = array(
			'poi_id' => $row['poi_id'],
			'uid' => $row['uid']
		);
		$result = Better_DAO_Badge_Calculator_Upyunhaiyao::touch($params);

		if($result){
				  
			$friends = Better_User_Friends::getInstance($row['uid'])->getFriends();

			if(count($friends)>=2){
				$friendstr = implode(",",$friends);
				$sqlf = "select uid from better_user_place_log where poi_id=".$row['poi_id']." and checkin_time>=".$fcheckintime." and checkin_score>0 and uid in (".$friendstr.") group by uid";
			
				$checkinfriend = array();
				foreach($sids as $rows){
					$csd = Better_DAO_Base::assignDbConnection('user_server_'.$rows);				
					$rdbd = &$csd['r'];
					$wdbd = &$csd['w'];	
					$rsd = Better_DAO_Base::squery($sqlf, $rdbd);	
					$datad = $rsd->fetchAll();
					foreach($datad as $row){
						$checkinfriend[] = $row;
					}
				}
			
				foreach($checkinfriend as $row){
					$userid = $row['uid'];
					$friendsgot = Better_User_Badge::getInstance($userid)->getMyBadges();
					if(!in_array(195,$friendsgot)){
						$result = Better_User_Badge::getInstance($userid)->got(195);
						Better_Log::getInstance()->logInfo($userid." got 195",'Upyunhaiyao');
					}
				}
			}
		}
	}	
}			
exit(0);
