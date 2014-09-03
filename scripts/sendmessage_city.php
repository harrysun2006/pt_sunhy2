<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SEND_CITYMESSAGE_LOCK', dirname(__FILE__).'/send_citymessage.lock');


//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

function killLock() 
{ 
	file_exists(SEND_CITYMESSAGE_LOCK) && unlink(SEND_CITYMESSAGE_LOCK); 
}

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');
 
// 检测是否有同步锁
file_exists(SEND_CITYMESSAGE_LOCK) && exit(0);
 
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
while(true){
	$sids = Better_DAO_User_Assign::getInstance()->getServerIds(); 
	$commondb = Better_DAO_Base::registerDbConnection('common_server');
	$sql = "select * from better_city_message where result=0";
	$rs = Better_DAO_Base::squery($sql, $commondb);
    $rows = $rs->fetchAll();
    foreach($rows as $row){
    	
	    	$id= $row['id'];
	    	$city = $row['city'];
	    	$type = $row['type'];
	    	$title = $row['title'];
	    	$content = $row['content'];
	    	
	    	$t_count = 0;
	    	
	    	foreach($sids as $sid) {
	    		echo "City".$city."Server:".$sid."\n";	
				$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid); 
				$rdb = &$cs['r'];	
				$sqlcount ="select count(*) as t_count from better_profile as p where  p.live_city like '".$city."'";
				echo $sqlcount."\n";
				$rs = Better_DAO_Base::squery($sqlcount, $rdb);
	        	$temp_count = $rs->fetch();	
	        	
				$t_count =  $t_count+$temp_count['t_count'];
				echo 	"总数为:".$t_count."\n";
				$pagenum = 20;			
				$pages = ceil($temp_count['t_count']/$pagenum);
				
				$checksql = "select count(*) as t_count from better_tempcity_message as p where p.messageid='".$id."' and p.sid='".$sid."'";
				
				$rs = Better_DAO_Base::squery($checksql, $commondb);
			    $parthad = $rs->fetch();
			    if($parthad['t_count']>0){
					$beginpage = ceil($parthad['t_count']/$pagenum)-1;
			    } else {
			    	$beginpage = 0;
			    }	
			    Better_Log::getInstance()->logInfo("Run Serverid:".$sid." Run Times:".$beginpage,'runsendmessage');
				try{				
					for($page=$beginpage;$page<$pages;$page++)
					{
						$checksql = "select uid from better_tempcity_message as p where p.messageid='".$id."' and p.sid='".$sid."'";
			        	$rs = Better_DAO_Base::squery($checksql, $commondb);
			        	$checkdata = $rs->fetchAll();	
			            foreach($checkdata as $rows){		            	
			            	$result[$rows['uid']] = $rows['uid'];
			            } 
			             
						$sql = "select p.uid,a.email,p.email4person,p.email4community,p.email4product,p.state,p.nickname,p.live_city from better_profile as p left join better_account as a on a.uid=p.uid where p.live_city like '".$city."' order by a.regtime limit ".$page*$pagenum.",".$pagenum;					
						$rs = Better_DAO_Base::squery($sql, $rdb);	
						$data = $rs->fetchAll();
					   
						echo "This is Server:".$sid." This doing:".count($data)." Page:".$page." Total:".$pages."\n";	
							
			            $insertsql = 'INSERT INTO better_tempcity_message(uid,messageid,sid) VALUES';				
			            $addstr = '';
			            $doinsert = 0;
			            
			        	foreach($data as $row){		        		        		
			        		$uid = $row['uid'];	
			        				        			        		
			        		if(is_array($result) && count($result)>0 && isset($result[$uid])){	
			        		//	echo "live_city:".$row['live_city']." Had Send:".$uid."\n";
			        		} else {
			        			$doinsert = 1;
			        			//$insertsql .=$addstr."(".$uid.",".$id.",".$sid.")";	  
			        			//$addstr = ',';      		
				        		$insertsql = "insert into better_tempcity_message(uid,messageid,sid) select ".$uid.",".$id.",".$sid." from dual where not exists (select * from better_tempcity_message as p where p.uid=".$uid." and p.messageid=".$id." and p.sid=".$sid.")";
				        		//echo $insertsql."\n";
				        		$rs = Better_DAO_Base::squery($insertsql, $commondb);
				        		$email = $row['email'];
				        		$name = $row['nickname'];        		
				        		if($type=='email' && $row['email4product'] && $row['state']!='signup_validating'){
				        			
				        			$lan = 'zh-cn';
				        			$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/new_activity.html';
									$mailer = new Better_Email($uid);
									$mailer->setSubject($title);
									$mailer->setTemplate($template);		
									$mailer->addReceiver($email, $email);					
									$mailer->set(array(
										'CONTENT' => $content,
										'NAME'=> $name
										));					
									$mailer->send2();				        		
				        		}else if($type=='message'){	        			
				        			Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
											'content' => $content,
											'receiver' => $uid
											));
													
				        		}
			        		}        		
			        	}
			        	
			        	echo $beginpage."--".$page."\n";
			        	
		        	sleep(1);		        
				}
				
	    	} catch (Exception $ee) {
				echo $beginpage."--".$page."\n";				
				exit(0);
	    	}  
	    	     		 
    	} 
    	try{
		        $checksql = "select count(*) as total from better_tempcity_message as p where p.messageid='".$id."'";
			    $rs = Better_DAO_Base::squery($checksql, $commondb);
			    $checkdata = $rs->fetch();	
			    echo $checkdata['total']."--".$t_count."\n";
			    Better_Log::getInstance()->logInfo($checkdata['total']."--".$t_count,'endmessage');
			    if($checkdata['total']>=$t_count){   
				    $updatesql = "update  better_city_message set result=1 where id='".$id."'";
					$rs = Better_DAO_Base::squery($updatesql, $commondb);	
			    }
			    exit(0);	
	    	}  catch (Exception $bb) {
	    		exit(0);
	    	} 
    }
	echo "Done.\n";	
	sleep(2);
	exit(0);	
}
exit(0);


