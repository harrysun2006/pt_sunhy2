<?php
// 除了日志和ZF Application外，其它处理(常量定义，Cache/Loader加载)同index.php
define('BETTER_FORCE_INCLUDE', getenv('BETTER_FORCE_INCLUDE')!='' ? (bool)getenv('BETTER_FORCE_INCLUDE') : true);
define('BETTER_START_TIME', microtime());

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : ($_SERVER['APPLICATION_ENV'] ? $_SERVER['APPLICATION_ENV'] : 'production')));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

function log2($message = '')
{
	$mt = microtime(true);
	$logfile = APPLICATION_PATH . '/../logs/pss.log';
	error_log('[' . number_format($mt, 4, '.', '') . ']: ' . $message . "\n", 3, $logfile);
}
$r = $_SERVER['REQUEST_URI'] . ': receveid ' . number_format(microtime(true), 4, '.', '');

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
define('BETTER_VER_CODE', APPLICATION_ENV!='production' ? date('YmdHis') : '20110218000002');
define('BETTER_LOG_HASH', md5(uniqid(rand())));
define('BETTER_NEED_INVITECODE', $appConfig->in_testing ? true : false);
define('BETTER_PPNS_ENABLED', $appConfig->ppns->enabled ? true : false);
define('BETTER_8HOURS', 28800);
define('BETTER_ADMIN_DAYS', 680400);
define('BETETR_ACTIVITY_POI', $appConfig->activity_poi);
define('BETTER_HELP_UID', 126844);

// if ($_SERVER['REQUEST_METHOD'] != 'POST') error();
$fields = json_decode($_REQUEST['FIELDS'], true);
$plugin = $fields['PLUGIN'];
$params = $fields['PARAMS'];
$extra = $fields['EXTRA'];
if (!isset($params)) $params = array();
if (!isset($extra)) $extra = array();
$r .= ', inited ' . number_format(microtime(true), 4, '.', '');
Better_Plugin::service($plugin, $params, $extra);
$r .= ', sent ' . number_format(microtime(true), 4, '.', '');
// log2($r);
?>