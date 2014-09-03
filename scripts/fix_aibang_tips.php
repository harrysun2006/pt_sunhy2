<?php

/**
 * 补充爱帮贴士
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/abtips.lock');

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

$rdb = Better_DAO_Base::registerDbConnection('poi_server');

$abUserId = (int)Better_Config::getAppConfig()->user->aibang_user_id;
		
$sql = "SELECT * FROM aibang_tips WHERE flag=0";
$rs = Better_DAO_Base::squery($sql, $rdb);
$rows = $rs->fetchAll();
foreach ($rows as $row) {
	$poiId = $row['poi_id'];
	$abId = $row['aid'];
	$review = $row['review'];
	
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	
	if ($poiInfo['poi_id'] && $poiInfo['tips']==0) {
		if (strlen($review)) {
			if (mb_strlen($review)>=140) {
				$review .= '...';
			}

			$bid = Better_Blog::post($abUserId, array(
					'message' => $review,
					'upbid' => 0,
					'attach' => '',
					'source' => 'kai',
					'poi_id' => $poiId,
					'type' => 'tips'		
				));
			
			$sql = "UPDATE aibang_tips SET flag=1 WHERE poi_id='".$poiId."' AND aid='".$abId."'";
			Better_DAO_Base::squery($sql, $rdb);
		}
		
		echo "POI : [".$poiId."], BID:[".$bid."] done\n";
	}
}

echo "Done.\n";
exit(0);
