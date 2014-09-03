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

$poiIds = array();

$sids = Better_DAO_User_Assign::getInstance()->getServerIds();

foreach($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$rdb = &$cs['r'];
	$wdb = &$cs['w'];
	
	$select = $rdb->select();
	$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
		new Zend_Db_Expr('DISTINCT(poi_id) AS poi_id')
		));
	$rs = Better_DAO_Base::squery($select, $rdb);
	$rows = $rs->fetchAll();
	
	foreach ($rows as $row) {
		$poiIds[] = $row['poi_id'];
	}
}

$poiIds = array_unique($poiIds);

foreach ($poiIds as $poiId) {
	$visitors = 0;
	$tips = 0;
	$checkins = 0;
	$posts = 0;
	
	foreach ($sids as $sid) {
		$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
		$rdb = &$cs['r'];
		$wdb = &$cs['w'];
		
		//	访客数
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
			new Zend_Db_Expr('COUNT(DISTINCT(uid)) AS total')
			));
		$select->where('poi_id=?', $poiId);
		
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		
		$visitors += (int)$row['total'];
		
		//	吼吼数
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', $poiId);
		$select->where('type=?', 'normal');
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		
		$posts += (int)$row['total'];
		
		//	签到数
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', $poiId);
		$select->where('type=?', 'checkin');
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		
		$checkins += (int)$row['total'];

		//	贴士数
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', $poiId);
		$select->where('type=?', 'tips');
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		
		$tips += (int)$row['total'];		
	}
	
	$db = Better_DAO_Base::registerDbConnection('poi_server');
	$sql = "UPDATE `".BETTER_DB_TBL_PREFIX."poi` SET `visitors`='".$visitors."', posts='".$posts."', tips='".$tips."', checkins='".$checkins."' WHERE poi_id='".$poiId."'";
	$db->query($sql);
	
	echo 'Poi '.$poiId.' Done.'."\n";
}
echo "Done.\n";
exit(0);
