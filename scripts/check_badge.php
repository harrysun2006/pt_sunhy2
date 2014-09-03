<?php

/**
 * 检查勋章
 * 
 * @package scripts
 * @author yangl
 */

define('BETTER_FORCE_INCLUDE', true);
set_time_limit(0);

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/checkbadge.lock');

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

define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);
define('BETTER_LOG_HASH', md5(uniqid(rand())));

defined('EMAIL_SENDER') || define('EMAIL_SENDER', (getenv('EMAIL_SENDER') ? getenv('EMAIL_SENDER') : $appConfig->email_sender));

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);
define('THIS_PID', dirname(__FILE__).'/checkbadge.pid');

$smtp = $appConfig->smtp->toArray();

$sess = Better_Session::factory();
$sess->init();

$cacher = Better_Cache::remote();

while(true) {
	$rows = Better_DAO_Admin_Badgealarm::getInstance()->getCheckBadge();

	foreach ($rows as $row) {
		try {
			$badge_id = $row['badge_id'];
			$badge = Better_DAO_Badge::getInstance()->getBadge($badge_id);
			
			$msg = '';
			if($badge){
				if($badge['frist_time'] && $badge['frist_time'] < $row['begin_time']){
					$msg .= "有人在勋章开始时间之前拿到 ".$row['badge_name']." 勋章了<br>";
				}
				if($badge['last_time'] > $row['end_time']){
					$msg .= "有人在勋章结束之后拿到 ".$row['badge_name']." 勋章了<br>";
				}
				if($row['last_total'] == $badge['count']){
					$msg .= $row['interval']."分钟内没人拿到 ".$row['badge_name']." 勋章<br>";
				}
				//发邮件
				if($msg && sendMail($msg, $badge_id)){
					Better_DAO_Admin_Badgealarm::getInstance()->update(array(
						'last_check'=> time(),
						'last_total'=> $badge['count']
					), $badge_id);
				}
				Better_Log::getInstance()->log($badge_id.'=='.$msg, 'debug_check_badge');
			}else{
				Better_Log::getInstance()->log('badge not found: '.$badge_id, 'check_badge');
			}
			
	 	} catch(Exception $ex) {
	 		Better_Log::getInstance()->logAlert('CHECK_BADGE_EXCEPTION_AT_LINE('.__LINE__.'):['.$ex->getMessage()." =========== \n".$ex->getTraceAsString().']', 'check_badge');
	 	}		
	}
	
	touch(THIS_PID);
	sleep(60);
}

/**
 * 发警报邮件
 */
function sendMail($msg, $badge_id){
	$receiver = serialize(array(
		'chenc@peptalk.cn',
		'hanc@peptalk.cn',
		'fengj@peptalk.cn',
		'gaosj@peptalk.cn',
		'shiyy@peptalk.cn',
		//'william@peptalk.cn',
	));
	
	if ($badge_id == '423') {
		$receiver = serialize(array(
			'chenc@peptalk.cn',
		));		
	}
	
	$f = Better_DAO_EmailCommonQueue::getInstance()->insert(array(
			'uid' => 0,
			'receiver' => $receiver,
			'body' => $msg,
			'queue_time' => time(),
			'subject' => '勋章警报'
	));
	
	return $f;
}