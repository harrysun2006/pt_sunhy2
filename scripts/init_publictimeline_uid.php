<?php
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

$args = $_SERVER['argv'];
$uid = (int)$args[1];

$tmp = Better_DAO_User_Status::getInstance($uid)->tinyWebFollowings(array(
	'page' => 1,
	'page_size' => 300,
	'with_self' => true,
	'without_kai' => true
	));
$t = array_chunk($tmp, 300);
$data = $t[0];

foreach ($data as $row) {
	$bid = $row['bid'];
	$dateline = $row['dateline'];
	
	echo $uid."|".$bid.'|'.$dateline."\n";
	
	Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
		'uid' => $uid,
		'bid' => $bid,
		'dateline' => $dateline
		));	
}

echo "Done\n";

