<?php

/**
 * 用户Publictimeline队列处理
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

$args = $_SERVER['argv'];
unset($args[0]);

define('BETTER_FORCE_INCLUDE', true);
define('BETTER_START_TIME', microtime());
define('THIS_BASE_NAME', basename(__FILE__));
//define('BETTER_IN_CONSOLE', 1);

//	进程锁
define('THIS_FILE_LOCK', dirname(__FILE__).'/'.THIS_BASE_NAME.'.lock');
define('THIS_PID', dirname(__FILE__).'/'.THIS_BASE_NAME.'.pid');

//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

function killLock() 
{ 
	file_exists(THIS_FILE_LOCK) && unlink(THIS_FILE_LOCK); 
}

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');
 
// 检测是否有同步锁
file_exists(THIS_FILE_LOCK) && die("File lock exists, so I will exit:(");
 
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
define('BETTER_STATIC_URL', Better_Config::getAppConfig()->static_url);
define('BETTER_DB_TBL_PREFIX', Better_Config::getDbConfig()->global->tbl_prefix);
define('BETTER_SYS_UID', $appConfig->user->sys_user_id);
define('BETTER_VIRTUAL_UID', $appConfig->user->virtual_user_id);
define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);
define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MERGE_PAGE_SIZE', $appConfig->blog->merge_page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_MAX_LIST_ITEMS_START', $appConfig->blog->list_max_items_start);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_AIBANG_POI', $appConfig->service->aibang->enabled ? true : false);
define('BETTER_VER_CODE', APPLICATION_ENV!='production' ? date('YmdHis') : '201012151105001');
define('BETTER_LOG_HASH', md5(uniqid(rand())));
define('BETTER_NEED_INVITECODE', $appConfig->in_testing ? true : false);
define('BETTER_PPNS_ENABLED', $appConfig->ppns->enabled ? true : false);
define('BETTER_8HOURS', 28800);
define('BETTER_ADMIN_DAYS', 680400);
define('BETETR_ACTIVITY_POI', $appConfig->activity_poi);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);

$sess = Better_Session::factory();
$sess->init();

if (count($args)>0 && $args[1]) {
	$actTypes = $args[1];
	define('PID_FILE', dirname(___FILE__).'/publictimeline_'.$actTypes.'.pid');
} else {
	$actTypes = array(
		1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12
		);
	define('PID_FILE', dirname(__FILE__).'/publictimeline.pid');
}

$friendStatus = Better_Config::getAppConfig()->friendstatus->open;
while(true) {
	$hasData = false;
	
	try {
		$module = Better_Queue_Module_Publictimeline::getInstance();
		if ( $friendStatus ) {
			$module_new = Better_Queue_Module_Friend::getInstance();
		}
		
		$data = $module->pop(array(
			'act_type' => $actTypes
			));
			
		if ($data['id']) {
			$hasData = true;
			$result = $module->cal($data);
			$module_new && $module_new->cal($data);
			
			if ($result) {
				$module->complete($data['id']);
			} else {
				$module->complete($data['id'], -1);
				Better_Log::getInstance()->logInfo(serialize($data), 'publictimeline_error', true);
			}
			
			echo $data['id']." done.\n";	
		} 
	} catch (Exception $e) {
		echo $e->getMessage()."\n".$e->getTraceAsString()."\n";
		Better_Log::getInstance()->logInfo("\n========================================\nAct Types : ".serialize($actTypes)."\nError Message : ".$e->getMessage()."\nTrace: ".$e->getTraceAsString(), 'publictimeline_exception', true);
		exit(0);
	}
	
	Better_User::destroyAllInstances();
	
	if (!$hasData) {
		sleep(3);
	}
	
	touch(PID_FILE);
	
}


