<?php

define('BETTER_START_TIME', microtime());

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'report'));

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
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_VER_CODE', '2010051108');
define('BETTER_NEED_INVITECODE', true);

echo '日期,注册用户数,吼吼数,贴士数,签到数,新建POI数,总用户数,Karma大于300的用户数,好友数大于10的用户数,推广来源,注册来源,寻宝过的用户数';

// @TODO 时区问题
$args = $_SERVER['argv'];
$from = $args[1] ? $args[1] : date('Y-m-d');
$to = $args[2] ? $args[2] : date('Y-m-d');

//	日期
echo $from.',';
//	注册用户数
echo Better_DAO_Counters::getUserCounts(array('from'=>$from, 'to'=>$to)).',';
//	吼吼数
echo Better_DAO_Counters::getBlogCounts('normal', $from, $to).',';
//	贴士数
echo Better_DAO_Counters::getBlogCounts('tips', $from, $to).',';
//	签到数
echo Better_DAO_Counters::getBlogCounts('checkin', $from, $to).',';
//	新建POI数量
echo Better_DAO_Counters::getPoiCounts($from, $to).',';
//	总用户数目
echo Better_DAO_Counters::getUserCounts().',';
//	Karma大于300的用户数
echo Better_DAO_Counters::getUserCounts(array('karma'=>300)).',';
//	好友数大于10的用户数
echo Better_DAO_Counters::getFrinedCounts().',';
//	推广来源
$rows = Better_DAO_Counters::getFromPartnerCounts(array('from'=>$from, 'to'=>$to));
$tmp = array();
foreach ($rows as $key=>$val) {
	$tmp[] = $key.':'.$val;
}
echo implode('|', $tmp).',';

//	注册来源
$rows = Better_DAO_Counters::getPartnerCounts(array('from' => $from, 'to'=>$to));
$tmp = array();
foreach ($rows as $k=>$v) {
	$tmp[] = $k.':'.$v;
}
echo implode('|', $tmp).',';
//	寻宝过的用户
echo Better_DAO_Counters::getUserCounts(array('treasures'=>1));

