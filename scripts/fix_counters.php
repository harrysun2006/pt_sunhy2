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

$poiDb = Better_DAO_Base::registerDbConnection('poi_server');
$sids = Better_DAO_User_Assign::getInstance()->getServerIds();

$args = $_SERVER['argv'];
$days = (int)$args[1];

foreach($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$rdb = &$cs['r'];
	$wdb = &$cs['w'];
	
        $sql = "SELECT c.uid 
        FROM better_profile_counters AS c
        INNER JOIN better_profile AS p
        	ON p.uid=c.uid
        WHERE 1
        ";
        if ($days) {
        	$sql .= " AND p.last_active>(UNIX_TIMESTAMP()-3600*24*".$days.")";	
        }
        
        $rs = Better_DAO_Base::squery($sql, $rdb);
        $rows = $rs->fetchAll();

        foreach ($rows as $row) {
        	//	好友数
			$uid = $row['uid'];
			$sql = "SELECT COUNT(*) AS total FROM better_friends WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$friends = (int)$d['total'];

			//	关注数
			$sql = "SELECT COUNT(*) AS total FROM better_following WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$followings = (int)$d['total'];

			//	粉丝数
			$sql = "SELECT COUNT(*) AS total FROM better_follower WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$followers = (int)$d['total'];
			
			//	勋章数
			$sql = "SELECT COUNT(*) AS total FROM better_user_badges WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$badges = (int)$d['total'];
			
			//	掌门数
			$sql = "SELECT COUNT(*) AS total FROM better_poi WHERE major='".$uid."' AND closed='0'";
			$rs = Better_DAO_Base::squery($sql, $poiDb);
			$d = $rs->fetch();
			$majors = (int)$d['total'];			
			
			//	宝物数
			$sql = "SELECT COUNT(*) AS total FROM better_user_treasures WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$treasures = (int)$d['total'];		
			
			//	贴士数
			$sql = "SELECT COUNT(*) AS total FROM better_blog WHERE `type`='tips' AND uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$tips = (int)$d['total'];	
			
			//	收藏数
			$sql = "SELECT COUNT(*) AS total FROM better_favorites WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$favorites = (int)$d['total'];		

			//地点收藏数
			$sql = "SELECT COUNT(*) AS total FROM better_poi_favorites WHERE uid='".$uid."'";
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$d = $rs->fetch();
			$poi_favorites = (int)$d['total'];		
						
			$sql = "UPDATE better_profile_counters SET favorites='".$favorites."', now_tips='".$tips."', treasures='".$treasures."', majors='".$majors."', friends='".$friends."', followings='".$followings."', followers='".$followers."', badges='".$badges."', poi_favorites='".$poi_favorites."' WHERE uid='".$uid."'";
			echo $sql."\n";
			Better_DAO_Base::squery($sql, $wdb);
		}
}

echo "Done.\n";
exit(0);
