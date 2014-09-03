<?php

/**
 * 由于新增了apn设置，所以需要运行一下该脚本，为现有用户新建这个基础数据
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

$serverIds = Better_DAO_User_Assign::getInstance()->getServerIds();
foreach ($serverIds as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$wdb = &$cs['w'];
	$rdb = &$cs['r'];
	
	$select = $rdb->select();
	$select->from(BETTER_DB_TBL_PREFIX.'account');
	$rs = Better_DAO_Base::squery($select, $rdb);
	$rows = $rs->fetchAll();
	
	foreach ($rows as $row) {
		$sql = "REPLACE INTO ".BETTER_DB_TBL_PREFIX."user_apn_settings (uid,game,direct_message, request,friends_shout,friends_checkin) VALUES ('".$row['uid']."', 1, 1, 1, 1, 1)";
		$wdb->query($sql);
		echo $row['uid']." done\n";
	}
}

echo "Done\n";

