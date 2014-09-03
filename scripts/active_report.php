<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/fix_counters.lock');

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
	$pois = Better_DAO_Base::registerDbConnection('poi_server');	
	$rpoisdb = &$pois;
	$now = BETTER_NOW;
    $sql = "select a.* from better_poi_activerequest as a where result=0";	
    $rs = Better_DAO_Base::squery($sql, $rpoisdb);
    $rows = $rs->fetchAll();
    foreach ($rows as $row) {
    	$request_id =  $row['request_id'];
    	$poi_id = $row['poi_id'];    
    	$rowbegin_tm = $row['begin_tm'];
    	$rowend_tm = $row['end_tm'];    	
    	$rowbadges = $row['badges'];
    	$sql = "select max(dateline) as dateline from better_poi_activereport as p where request_id='".$request_id."'";    	
    	$rs = Better_DAO_Base::squery($sql, $rpoisdb);
   		$dateline = $rs->fetch();   		
    	if($dateline['dateline']>=$rowend_tm){
   			$sql = "update better_poi_activerequest set result=1 where request_id='".$request_id."'";
    		$rs = Better_DAO_Base::squery($sql, $rpoisdb);
   			continue;
   		}
   		
   		if($dateline['dateline']){   					
   			$begin_tm = gmmktime(16, 0, 0, date('m',$dateline['dateline']),date('d',$dateline['dateline']), date('Y',$dateline['dateline']));	   					
   			$tmp_tm =  gmmktime(15, 59, 59, date('m',$begin_tm),date('d',$begin_tm)+1, date('Y',$begin_tm)); 			
   			$end_tm = $rowend_tm>$tmp_tm ? $tmp_tm:$rowend_tm;    								
   		} else {
   			$begin_tm = $rowbegin_tm;   			
   			$tmp_tm =  gmmktime(15, 59, 59, date('m',$begin_tm),date('d',$begin_tm), date('Y',$begin_tm));   			
   			$end_tm = $rowend_tm>$tmp_tm ? $tmp_tm:$rowend_tm;   			
   		}
   		$dateline = $end_tm;
   		if(BETTER_NOW<$dateline){
   			continue;
   		}
    	$sids = Better_DAO_User_Assign::getInstance()->getServerIds();    	
    	$checkinpeoples = 0;
    	$checkins = 0;
    	$shouts = 0;
    	$tips = 0;
    	$badges = 0;
    	$checkinsync = 0;
    	$shoutsync = 0;
    	$tipsync = 0;
    	$friends = 0;
    	$refriend = "";
    	foreach ($sids as $sid) { 
    		try{
		    	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid); 	    	
				$rdb = &$cs['r'];	
		    	$sql= "select count(*) as t_count from better_user_place_log as l where poi_id='".$poi_id."' and checkin_time>='".$begin_tm."' and checkin_time<='".$end_tm."'";
		    	
		    	$rs = Better_DAO_Base::squery($sql, $rdb);
	        	$checkinrows = $rs->fetch();
	        	$checkins = $checkins+$checkinrows['t_count'];
		    	$sql= "select l.uid,f.friend_uid  from better_user_place_log as l left join (select uid,group_concat(friend_uid) as friend_uid from better_friends group by uid) as f on f.uid=l.uid where l.poi_id='".$poi_id."' and l.checkin_time>='".$begin_tm."' and l.checkin_time<='".$end_tm."' group by l.uid";
		    	
		    	$rs = Better_DAO_Base::squery($sql, $rdb);
		    	
	        	$personrows = $rs->fetchAll();
	       		$times = count($personrows);
	        	$checkinpeoples = $checkinpeoples + $times;
	        	foreach ($personrows as $temprow){
	        		if($temprow['friend_uid']){
	        			$refriend = $refriend.",".$temprow['friend_uid'];        			
	        		} 		
	        	}        	
		    	$sql = "select count(*) as t_count,b.type from better_blog as b where poi_id='".$poi_id."' and dateline>='".$begin_tm."' and dateline<='".$end_tm."' group by b.type";
		    	$rs = Better_DAO_Base::squery($sql, $rdb);
	        	$blogrows = $rs->fetchAll();
	        	foreach ($blogrows as $row){
	        		switch($row['type']){
	        			case 'tips':
	        				$tips = $tips+$row['t_count'];
	        				break;
	        			case 'checkin':
	        				$checkins = $checkins+$row['t_count'];
	        				break;
	        			case 'normal':
	        				$shouts = $shouts+$row['t_count'];
	        				break;
	        		}        		
	        	}        	
		    	$sql = "select count(*) as t_count from better_sync_queue as q left join better_blog as b on b.bid=q.bid where q.poi_id='".$poi_id."' and q.queue_time>='".$begin_tm."' and q.queue_time<='".$end_tm."' group by b.type";
		    	
		    	$rs = Better_DAO_Base::squery($sql, $rdb);
	        	$syncrows = $rs->fetchAll();
	        	foreach ($syncrows as $row){
	        		switch($row['type']){
	        			case 'tips':
	        				$tipsync = $tipsync+$row['t_count'];
	        				break;
	        			case 'checkin':
	        				$checkinsync = $checkinsync+$row['t_count'];
	        				break;
	        			case 'normal':
	        				$shouts = $shouts+$row['t_count'];
	        				break;
	        		} 
	        	}
	        	if($rowbadges!='0'){
		        	$sql = "select count(*) as t_count from better_user_badges where bid in ('".$rowbadges."')";
		        	$rs = Better_DAO_Base::squery($sql, $rdb);
		        	$badgerows = $rs->fetch();
		        	$badges = $badges+$badgerows['t_count'];
	        	}	        	
    		}  catch (Exception $bb) {
	    		exit(0);	    	 
    		} 
    	}
    		
    	$friend = split(',',$refriend);
    	$friends = count($friend);
    	$refriends =count(array_unique($friend));
    	/*
    	$text = "";
    	$text .="签到人数:".$checkinpeoples;
    	$text .=" 签到次数:".$checkins;
    	$text .=" 吼吼数:".$shouts;
    	$text .=" 贴士数:".$tips;
    	$text .=" 获得勋章人数:".$badges;
    	$text .=" 签到同步数:".$checkinsync;
    	$text .=" 吼吼同步数:".$shoutsync;
    	$text .=" 贴士同步数:".$tipsync;    	
    	$text .=" 好友人次:".$friends; 
    	$text .=" 好友数:".$refriends;  
    	*/ 	
    	$sql = "insert into better_poi_activereport(request_id,dateline,checkinpeoples,checkins,shouts,tips,badges,checkinsync,shoutsync,tipsync,friends,refriends) value(".$request_id.",".$dateline.",".$checkinpeoples.",".$checkins.",".$shouts.",".$tips.",".$badges.",".$checkinsync.",".$shoutsync.",".$tipsync.",".$friends.",".$refriends.")";
    	$rs = Better_DAO_Base::squery($sql, $rpoisdb);    	
    }
  

echo "Done.\n";
exit(0);
