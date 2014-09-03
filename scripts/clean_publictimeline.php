<?php

/**
 *	清理多余的用户publictimeline
 * 根据设计，服务端只为每个用户维护300条publictimeline数据，为了避免数据库浪费，所以有必要定时清理一下过期的publictimeline
 * 
 * @author leip@peptalk.cn
 *
 */

//	标定环境为Cron
define('IN_CRON', true);

define('BETTER_START_TIME', microtime());

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

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

$sess = Better_Session::factory();
$sess->init();


$oneWeek = 3600*24*7;
$deleteOffset = time()-$oneWeek;

$serverIds = Better_DAO_User_Assign::getInstance()->getServerIds();
foreach ($serverIds as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$wdb = &$cs['w'];
	$rdb = &$cs['r'];
	
	$sql = "SELECT COUNT(*) AS total, uid FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` GROUP BY uid HAVING total>".((int)$appConfig->queue->publictimeline->offset);
	$rs = $rdb->query($sql);
	$rows = $rs->fetchAll();
	foreach ($rows as $row) {
		$sql = "SELECT dateline FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE uid=".$row['uid']." ORDER BY dateline DESC LIMIT ".((int)$appConfig->queue->publictimeline->offset).", 1";
		$rs = $rdb->query($sql);
		$data = $rs->fetch();
		$dateline = $data['dateline'];
		$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE uid=".$row['uid']." AND dateline<".$dateline;
		$wdb->query($sql);
		
		echo $row['uid']." cleaned\n";
	}
	
	Better_Log::getInstance()->logInfo('Validating treasures cleaned', 'daily', true);
}

echo "Done\n";

Better_Log::getInstance()->logInfo('cleaned', 'publictimeline_clean', true);
