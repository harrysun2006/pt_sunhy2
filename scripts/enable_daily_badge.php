<?php

/**
 * 清理缓存
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

define('BETTER_FORCE_INCLUDE', true);

//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');

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
define('BETTER_IN_GAME', true);
$appConfig->ppns->enabled ? define('BETTER_PPNS_ENABLED', true) : define('BETTER_PPNS_ENABLED', false);

$md = date('md', time()+3600*8+3600*24);
$row = Better_DAO_Badge::getInstance()->get(array(
	'class_name' => 'Getitlouder'.$md
	));
if (isset($row['id'])) {
	Better_DAO_Badge::getInstance()->updateByCond(array(
		'active' => 1
	), array(
		'id' => $row['id'],
	));
}

//	清理勋章
Better_Cache::remote()->set('kai_badges', null);

echo "Done\n";