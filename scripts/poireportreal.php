<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/poireportreal.lock');

//	标定环境为Cron
define('IN_CRON', true);

 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

function killLock() 
{ 
	file_exists(SYNC_BLOG_LOCK) && unlink(SYNC_BLOG_LOCK); 
}

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');
 
// 检测是否有同步锁
file_exists(SYNC_BLOG_LOCK) && exit(0);
 
//	没有同步锁则继续执行同步操作
register_shutdown_function('killLock');

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


$businessdb = Better_DAO_Base::registerDbConnection('business_server');


$rbusinessdb = &$businessdb;
$sql = "select poi_id from better_shopkeeper_require where status=1";
$rs = Better_DAO_Base::squery($sql, $rbusinessdb);	
$data = $rs->fetchAll();
$poilist = array();
foreach($data as $row){
	$poilist[] = $row;
}



$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
foreach($poilist as $row){
	$poi_id = $row['poi_id'];	
	$begtm = gmmktime(16,0,0,date("m"),date("d")-2,date("Y"));
	$endtm = gmmktime(16,0,0,date("m"),date("d")-1,date("Y"));
	$dayslog = date("d")-2;
	$datelog = date("Y")."-".date("m")."-".$dayslog;	
	$filename  = "/home/hanc/poireportreal/".$poi_id."_".$datelog.".log";
	if(file_exists($filename)){
		continue;
	}
	try{
	$dayscheckintotal = 0;
	$dayscheckingender = array();
	$dayscheckinhour = array();
	
	foreach($sids as $sid){
		$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);	
		$rdb = &$cs['r'];
		$wdb = &$cs['w'];	
		//每天签到人数
		$sqldayscheckin = "select count(*) as t_count from better_user_place_log where poi_id=".$poi_id." and checkin_time>=".$begtm ." and checkin_time<=".$endtm." and checkin_score>0";	
		$rs = Better_DAO_Base::squery($sqldayscheckin, $rdb);	
		$dayscheckin = $rs->fetch();
		$dayscheckintotal = $dayscheckintotal+$dayscheckin['t_count'];
		
		//每天签到性别		
		$sqlcheckingender = "select count(*) as t_count,pro.gender from better_user_place_log as log,better_profile as pro  where log.poi_id=".$poi_id." and log.checkin_time>=".$begtm ." and log.checkin_time<=".$endtm."  and log.checkin_score>0 and pro.uid=log.uid group by pro.gender";	
		$rs = Better_DAO_Base::squery($sqlcheckingender, $rdb);	
		$gendercheckin = $rs->fetchAll();
		foreach($gendercheckin as $row){
			$gender = $row['gender'];
			if($row['gender']==''){
				$gender = 'secret';
			}
			$dayscheckingender[$gender] = $dayscheckingender[$gender] + $row['t_count'];
		}		
		
		$sqlhourcheckin = "select count(*) as t_count, date_format(from_unixtime(checkin_time),'%H') as timehours from better_user_place_log where poi_id=".$poi_id." and checkin_time>=".$begtm." and checkin_time<=".$endtm."  and checkin_score>0 group by date_format(from_unixtime(checkin_time),'%H')";	
		
		$rs = Better_DAO_Base::squery($sqlhourcheckin, $rdb);	
		$hourscheckin =$rs->fetchAll();
		foreach($hourscheckin as $row){
			$dayscheckinhour[$row['timehours']] = $dayscheckinhour[$row['timehours']] + $row['t_count'];
		}		
		
	}
	$dateline = gmmktime(16,0,1,date("m"),date("d")-2,date("Y"));
	
   
	if($dayscheckintotal>0){
		$sql = "insert into better_poi_checkindays(poi_id,dateline,nums,score) values(".$poi_id.",".$dateline.",".$dayscheckintotal.",1)";		
		$rs = Better_DAO_Base::squery($sql, $rbusinessdb);
	}
	foreach($dayscheckingender as $key=>$row){	
		if($row>0){	
			$gender = $key;			 
			$sql = "insert into better_poi_checkingender(poi_id,dateline,gender,nums,score) values(".$poi_id.",".$dateline.",'".$gender."',".$row.",1)";		
			$rs = Better_DAO_Base::squery($sql, $rbusinessdb);	
		}
	}
	
	foreach($dayscheckinhour as $key=>$row){
		if($row>0){	
			$hour = (int)$key;
			$hourtm = gmmktime(16+$hour+8,0,1,date("m"),date("d")-2,date("Y"));		
			$sql = "insert into better_poi_checkinhours(poi_id,dateline,nums,score) values(".$poi_id.",".$hourtm.",".$row.",1)";		
			$rs = Better_DAO_Base::squery($sql, $rbusinessdb);
		}
	}
	
	
	error_log('',3,$filename);
} catch(Exception $error){
		Better_Log::getInstance()->logInfo($poi_id,'poirealreporterror');
	}
	
}




exit(0);
