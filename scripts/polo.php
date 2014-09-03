<?php

/**
 * Polo活动后端定时任务
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

$cacher = Better_Cache::remote();

$time = now()+3600*8;
$start = gmmktime(7, 0, 0, 10, 2, 2011);
$end = '';

$y = date('Y', $time);
$m = date('m', $time);
$d = date('d', $time);
$h = date('H', $time);

$step1 = false;
$step2 = false;

if (APPLICATION_ENV=='production') {
	if ($m==2 && (in_array($d, array(11, 12, 13)) || ($d==10 && $h>=15) || ($d==14 && $h<=15))) {
		$step1 = true;
	}
} else {
	$step1 = true;
}

if ($m==2 && $d==14 && ($h==19 || $h==20)) {
	$step2 = true;
}

$bjPoiId = $appConfig->market->polo->poi->bj;
$shPoiId = $appConfig->market->polo->poi->sh;
$gzPoiId = $appConfig->market->polo->poi->gz;

$kaiUser = Better_User::getInstance(BETTER_SYS_UID);

if ($step1) {
	$bjPoiIds = implode(',', $appConfig->market->polo->bj_poi_ids);
		
	$bjIdx = (int)$cacher->get('polo_bj_idx');
	$poiId = $bjPoiIds[fmod($bjIdx, 4)];
	$cacher->set('polo_bj_idx', ++$bjIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($bjPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));
	
	$gzPoiIds = implode(',', $appConfig->market->polo->gz_poi_ids);
	
	$gzIdx = (int)$cacher->get('polo_gz_idx');
	$poiId = $gzPoiIds[fmod($gzIdx, 4)];
	$cacher->set('polo_gz_idx', ++$gzIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($gzPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));		
		
	$shPoiIds = implode(',', $appConfig->market->polo->sh_poi_ids);
	
	$shIdx = (int)$cacher->get('polo_sh_idx');
	$poiId = $shPoiIds[fmod($shIdx, 4)];
	$cacher->set('polo_sh_idx', ++$shIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($shPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));		
}

if ($step2) {
	$bjPoiIds = implode(',', $appConfig->market->polo->bj_poi_ids2);
	
	$bjIdx = (int)$cacher->get('polo_bj_idx');
	$poiId = $bjPoiIds[fmod($bjIdx, 2)];
	$cacher->set('polo_bj_idx', ++$bjIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($bjPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));
	
	$gzPoiIds = implode(',', $appConfig->market->polo->gz_poi_ids2);
	$gzIdx = (int)$cacher->get('polo_gz_idx');
	$poiId = $gzPoiIds[fmod($gzIdx, 2)];
	$cacher->set('polo_gz_idx', ++$gzIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($gzPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));		
		
	$shPoiIds = implode(',', $appConfig->market->polo->sh_poi_ids2);
	$shIdx = (int)$cacher->get('polo_sh_idx');
	$poiId = $shPoiIds[fmod($shIdx, 2)];
	$cacher->set('polo_sh_idx', ++$shIdx);
	$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
	Better_Poi_Info::getInstance($shPoiId)->update(array(
		'lon' => $poiInfo['lon'],
		'lat' => $poiInfo['lat'],
		));			
}

$kaiLastBid = $cacher->get('polo_kai_last_bid');
if ($kaiLastBid) {
	$kaiUser->blog()->delete($kaiLastBid);
}

if (APPLICATION_ENV=='production') {
	$message = '';
} else {
	$message = 'hahahaha';
}

$kaiUser->blog()->add(array(
	'message' => $message,
	'upbid' => '',
	'attach' => '',
	'source' => 'web',
	'range' => 0,
	'poi_id' => 0,
	'priv' => 'public',
	'checkin_need_sync' => true,
	'need_sync_true' => true
	));
