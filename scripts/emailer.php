<?php

/**
 * 发送EMail
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

define('BETTER_FORCE_INCLUDE', true);
set_time_limit(0);

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/emailer.lock');

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
define('THIS_PID', dirname(__FILE__).'/emailer.pid');

$smtp = $appConfig->smtp->toArray();

$sess = Better_Session::factory();
$sess->init();

$cacher = Better_Cache::remote();
$cacheKey = 'last_sms_exception_sms';

while(true) {
	$rows = Better_DAO_EmailQueue::getAllQueue();

	foreach ($rows as $row) {
		try {
			$goSmtp = $row['go_smtp'] ? true : false;
			
			if ($goSmtp) {
				$tr = new Zend_Mail_Transport_Smtp($smtp['host'], array(
					'auth' => 'login',
					'username' => $smtp['user'],
					'password' => $smtp['password'],
					'port' => 25,
					));
			} else {
				$tr = new Zend_Mail_Transport_Sendmail('-f'.EMAIL_SENDER);
			}
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyHtml($row['body']);
			$mail->setFrom(EMAIL_SENDER);
			$mail->setSubject($row['subject']);
			
			$validReceivers = 0;
			$receivers = unserialize($row['receiver']);
			foreach($receivers as $name=>$email) {
				$email = trim($email);
				$name = trim($email);
				if (Better_Functions::checkEmail($email)) {
					$mail->addTo($email, $name);
					$validReceivers++;
				}
			}
	
			if ($validReceivers>0) {
				$flag = $mail->send($tr);
			} else {
				$flag = false;
			}
			$result = $flag ? 'SUCCESS' : 'FAILED';
			
			Better_DAO_EmailQueue::getInstance($row['uid'])->updateByCond(array(
				'tried' => $row['tried']+1,
				'send_time' => time(),
				'result' => $result,
				), array(
				'queue_id' => $row['queue_id']
				));
			
			if ($result=='FAILED') {
				Better_Log::getInstance()->logAlert('SEND_EMAIL_FAILED', 'email');
			}
	 	} catch(Exception $ex) {
	 		try {
		 		Better_DAO_EmailQueue::getInstance($row['uid'])->updateByCond(array(
		 			'tried' => 9999,
		 			'send_time' => time(),
		 			'result' => 'FAILED',
		 			), array(
		 				'queue_id' => $row['queue_id']
		 			));
	 		} catch (Exception $ex1) {
	 			Better_Log::getInstance()->logAlert('SEND_EMAIL_EXCEPTION_AT_LINE('.__LINE__.'):['.$ex1->getMessage()." =========== \n".$ex1->getTraceAsString().']', 'email');
	 			exit(0);
	 		}
	 	}		
	}
	
	touch(THIS_PID);
	sleep(3);
}

echo "Done\n";