<?php
/**
 * 导入团800的数据
 * @var unknown_type
 */

define('BETTER_FORCE_INCLUDE', true);
define('BETTER_START_TIME', microtime());

//	标定环境为Cron
define('IN_CRON', true);

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
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_VER_CODE', '2010051108');
define('BETTER_NEED_INVITECODE', true);

// clear old data at first
Better_DAO_Roundmore_Tuangou::getInstance()->truncate();

// import data today
try{
$rows = Better_DAO_Tuan800::getInstance()->getAll();

foreach($rows['rows'] as $row){
	$lon = $row['lon'];
	$lat = $row['lat'];
	list($x, $y) = Better_Functions::LL2XY($lon, $lat);
	$data = array(
		'poi_id'=>$row['poi_id'],
		'content'=>$row['title'],
		'img_url'=>$row['imgbig'],
		'begintm'=>(int)strtotime($row['startTime'])-8*3600,
		'endtm'=>(int)strtotime($row['endTime'])-8*3600,
		'detail_url'=>$row['detailurl'],
		'phone'=> $row['tel'],
	 	'source'=> $row['website'],
		'expired'=>0,
		'x'=> $x,
		'y'=> $y,
		'import_time'=>time(),
		'icon'=>'http://k.ai/images/tuan.png',
		'value'=> $row['value'],
		'price'=> $row['price']
	);
	
	$flag = Better_DAO_Roundmore_Tuangou::getInstance()->insert($data);
	if($flag){
		Better_DAO_Tuan800::getInstance()->update(array('flag'=>1), $row['identifier']);
	}
	
}

echo $rows['count'].' rows done'."\n";

}catch(Exception $e){
	die($e);
}

	
