<?php

/**
 * 每天集中私信提醒一次，时间在下午3点，内容：
 * “您获得了{5x?}Karma的奖励，因为你在{{POI_NAME}}等地点发表的贴士被评为优质贴士。”
 * 
 * @package scripts
 * @author sunhy <sunhy@peptalk.cn>
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
define('PHP_EXE', Better_Config::getAppConfig()->php_exe);

$sess = Better_Session::factory();
$sess->init();

$tt = time();
$tf = $tt - 24*3600;
$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
foreach ($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$wdb = &$cs['w'];
	$rdb = &$cs['r'];
	$sql = "SELECT t1.uid, SUM(t1.rp) rp, t2.* FROM better_user_rp_log t1 "
		. " INNER JOIN (SELECT uid, bid, poi_id, message, dateline FROM better_blog b1 "
		. "   WHERE b1.`type`='tips' AND b1.featured=1 " 
		. "     AND b1.dateline=(SELECT MAX(b2.dateline) FROM better_blog b2 "
		. "       WHERE b2.uid=b1.uid AND b2.`type`='tips' AND b2.featured=1 GROUP BY b2.uid) limit 1) t2"
		. " ON t1.uid=t2.uid"
		. " WHERE t1.category='featuredtips' AND t1.dateline<=$tt AND t1.dateline>$tf GROUP BY t1.uid";
	$rdb->getConnection();

	$rs = $rdb->query($sql);
	$rows = $rs->fetchAll();
	foreach ($rows as $row) {
		$uid = $row['uid'];
		$poi = Better_Poi_Info::getInstance($row['poi_id']);
		$content = '您获得了' . $row['rp'] . 'Karma的奖励，因为你在{' . $poi->name . '}等地点发表的贴士被评为优质贴士。';
		Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
			'receiver' => $row['uid'],
			'content' => $content,
			'skip_filter' => 1,
		));
	}
}
