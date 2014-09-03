<?php

/**
 * 清理Memcache
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

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

define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);

define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);

$sess = Better_Session::factory();
$sess->init();

$args = $_SERVER['argv'];
$todo = isset($args[1]) ? $args[1] : '';
$tk = 'clear';

$cacher = Better_Cache::remote();

Better_Timer::start($tk);
switch ($todo) {
	//	清理勋章主缓存
	case 'badges':
		Better_Cache_Clear::badges();
		echo "Badges cache cleared\nTime used : ".Better_Timer::end($tk)."s\n";
		break;
	//	清理宝物主缓存
	case 'treasures':
		Better_Cache_Clear::treasures();
		echo "Treasures cache cleared\nTime used : ".Better_Timer::end($tk)."s\n";
		break;
	//	清理用户资料缓存
	case 'userinfo':
		$uid = (int)$args[2];
		if ($uid) {
			Better_Cache_Clear::userInfo($uid);
			echo "Cached cleared for UID ".$uid."\nTime used : ".Better_Timer::end($tk)."s\n";
		} else {
			echo "Clearing cache for all ...\n";
			Better_Cache_Clear::allUserInfo();
			echo "All users' cache cleared\nTime used : ".Better_Timer::end($tk)."s\n";
		}
		break;
	//	清理用户头像缓存
	case 'avatar':
		$uid = (int)$args[2];
		if ($uid) {
			Better_Cache_Clear::avatar($uid);
			echo "User avatar cache cleared for UID ".$uid."\nTime used : ".Better_Timer::end($tk)."s\n";
		} else {
			echo "Clearing cache for all users' avatars ...\n";
			Better_Cache_Clear::allAvatar();
			echo "All users's avatar cache cleared\nTime used : ".Better_Timer::end($tk)."s\n";
		}
		break;
	//	清理kai用户的系统缓存
	case 'kai':
		Better_Cache_Clear::kai();
		echo "Kai's publictimeline cache cleared.\n";
		echo "Time used : ".Better_Timer::end($tk)."s\n";
		break;
	//	清理排行榜类缓存
	case 'ranking':
		Better_Cache_Clear::ranking();
		echo "Ranking cache cleared\n";
		echo "Time used : ".Better_Timer::end($tk)."s \n";
		break;
	//	清理blog表（吼吼、签到、贴士）缓存
	case 'blog':
		$uid = (int)$args[2];
		if ($uid) {
			Better_Cache_Clear::blog($uid);
			echo "User blog cache cleared for UID ".$uid."\nTime used : ".Better_Timer::end($tk)."s\n";
		} else {
			echo "Clearing cache for all users' blog ...\n";
			Better_Cache_Clear::allBlog();
			echo "All users's blog cache cleared\nTime used : ".Better_Timer::end($tk)."s\n";
		}
		break;		
	default:
		echo "Usage : php ".$args[0]." {badges|blog|treasures|userinfo|avatar|kai}\n";
		break;
}

