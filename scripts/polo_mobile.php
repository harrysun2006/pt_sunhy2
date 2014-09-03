<?php

/**
 * Ping
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

define('BETTER_FORCE_INCLUDE', true);
set_time_limit(0);

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/pinger.lock');

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
define('BETTER_VIRTUAL_UID', $appConfig->user->virtual_user_id);
define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);
define('BETTER_LOG_HASH', md5(uniqid(rand())));
define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);
define('THIS_PID', dirname(__FILE__) . '/pingmobile.pid');

$sess = Better_Session::factory();
$sess->init();

while(true) {
	try {
		$rows = Better_DAO_PoloQueue::getInstance()->popupQueue(20);
		
		if (is_array($rows) && count($rows)>0) {
			foreach ($rows as $row) {
				$poi_id = $row['poi_id'];
				$mobile = $row['mobile'];
				
				$url = 'http://product.moad.cn/NewPoloLBSSendSMS/?Mobile=' . $mobile . '&Code=' . $poi_id;
				$client = new Zend_Http_Client($url, array(
					'keepalive' => true,
					));
				$client->request();
				$html = $client->getLastResponse()->getBody();
				$status = $client->getLastResponse()->getStatus();
				if ($status == '200') {
					$result = $html;
				} else {
					$result = -2; //服务失败
				}
					
				Better_DAO_PoloQueue::getInstance()->updateByCond(array(
					'result' => $result,
					'send_time' => time()
				), array(
					'queue_id' => $row['queue_id'],
				));
				
				touch(THIS_PID);
			}
		}
	} catch (Exception $ee) {
		Better_Log::getInstance()->logEmerg($ee->getTraceAsString(), 'polomobile_exception');
		
		exit(0);
	}
	
	touch(THIS_PID);
	usleep(200000);
}

echo "Done\n";