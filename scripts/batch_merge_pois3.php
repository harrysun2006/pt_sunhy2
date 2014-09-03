<?php

/**
 * 从数据库中取数据自动合并poi
 * 
 * @package scripts
 * @author yangl
 */

define('BETTER_FORCE_INCLUDE', true);

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/batch3.lock');

//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

function killLock() 
{ 
	file_exists(SYNC_BLOG_LOCK) && unlink(SYNC_BLOG_LOCK); 
}

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

define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);


	$rows = Better_DAO_AutoMergePoisQueue::getInstance()->getAllQueue();
	try{
		foreach ($rows as $row) {
			$pids_str = $row['ids'];
			$pids = explode(',', $pids_str);
			$pids = array_unique($pids);
			$refParams = array(
					'pids'=> $pids,
					'target_poi_id' => $row['refid']
			);
				
			$simipoi = new Better_Admin_Simipoi();
			$return = $simipoi->mergeMutiPOI($pids, $row['refid']);
			
			echo $return."  ".$row['refid']."\n";
			Better_DAO_AutoMergePoisQueue::getInstance()->update(array('flag'=>$return), $row['refid']);
			Better_DAO_AutoMergePoisQueue::getInstance()->delete($row['refid']);
		}
	}catch(Exception $e){
		die($e);
	}
	

echo "Done\n";