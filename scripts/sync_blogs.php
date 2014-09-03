<?php

/**
 * 抄送到第三方处理
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */
ini_set('display_errors', 1);
ini_set('error_reporting', 'E_ALL & ~E_NOTICE');

$args = $_SERVER['argv'];
unset($args[0]);

define('BETTER_FORCE_INCLUDE', true);
define('BETTER_START_TIME', microtime());

//	进程锁
if (count($args)>0 && $args[1]) {
	define('SYNC_BLOG_LOCK', dirname(__FILE__).'/sync_blogs_'.$args[1].'.lock');
	define('THIS_PID', dirname(__FILE__).'/sync_blogs_'.$args[1].'.pid');
} else {
	define('SYNC_BLOG_LOCK', dirname(__FILE__).'/sync_blogs.lock');
	define('THIS_PID', dirname(__FILE__).'/sync_blogs.pid');
}

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

$retries = array();
$last_uid = '';

if (count($args)>0 && $args[1]) {
	$protocols = $args;
} else {
	if (APPLICATION_ENV=='production') {
		$protocols = array(
					'51.com',
					'plurk.com',
					'kaixin.com',
		
					);
	} else {
		$protocols = array(
					'4sq.com',
					'foursquare.com',
					'twitter.com',
					'facebook.com',
		
					'sina.com',
					'sohu.com',
					'9911.com',
					'douban.com',
					'zuosa.com',
					'digu.com',
		
					'follow5.com',
					'139.com',
		
					'renren.com',
					'51.com',
					'plurk.com',
					'kaixin.com',
					'kaixin001.com',
					'msn.com',
					'fanfou.com',
					'163.com',
					'bedo.cn',
					'qq.com',
					);		
	}
}

while(true) {
	try {
		$row = Better_DAO_SyncQueue::popupQueue($protocols);
		!isset($retries[$row['uid']]) && $retries[$row['uid']] = array();
		!isset($retries[$row['uid']][$row['protocol']]) && $retries[$row['uid']][$row['protocol']] = 1;
		if (isset($row['queue_id'])) {
			$queueId = $row['queue_id'];
			$message = $row['message'];
			$type = $row['type'];
			Better_Registry::set('type', $type);
			Better_Registry::set('suid', $row['uid']);
			$username = $row['username'];
			$password = $row['password'];
			$protocol = $row['protocol'];
			$attach = $row['attach'];
			$uid = $row['uid'];
			$poiId = $row['poi_id'];
			$bid = $row['bid'];
			$ip = $row['ip'];
			Better_Registry::set('ip', $ip);
			
			
			if ($protocol == 'qq.com' && $last_uid == $row['uid']) {
				var_dump('10s');
				sleep(10);
			}
			
			$isGetFollowers = false;
			if (in_array($protocol, array('sina.com', 'qq.com', 'fanfou.com', 'twitter.com', 'sohu.com', '163.com', 'digu.com', 'follow5.com', 'zuosa.com'))) {
				$isGetFollowers = true;
			}

			if ($row['content'] == 'unbind') {
				$type = 'unbind';
			}
			
			$geo = array();
			if ($row['x'] && $row['y']) {
				list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
				$geo['lon'] = $row['lon'];
				$geo['lat'] = $row['lat'];
				$geo['city'] = $row['city'];
				$geo['location'] = $row['address'];
				$geo['country'] = 'country';
				$geo['province'] = 'province';
			}
		
			$tokens = Better_DAO_SyncQueue::getToken($row['uid'], $protocol);
			$oauth_token = $tokens['oauth_token'];
			$oauth_token_secret = $tokens['oauth_token_secret'];
						
			$queue_time = $row['queue_time'];
			$queue_time_0 = $row['queue_time_0'];

		
			$dao = Better_DAO_SyncQueue::getInstance($uid);
			if ( $protocol == 'msn.com' || $protocol == 'kaixin001.com' ) {
				$dao->updateByCond(array(
					'sync_time' => time(),
					'result' => 'SYNC_FAILED',
				), array(
					'queue_id' => $queueId
				));
			} 
		
						
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			$_data = Better_DAO_User::getInstance($uid)->get($uid);
			$userInfo['username'] = $_data['username'];
			if ( $protocol == 'renren.com' && $oauth_token=='') {
				Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
					'content' => '您曾经绑定过人人网，但我们注意到您的用户名密码已失效，建议您登录开开网站重新绑定，体验一下更全面的人人网同步吧！',
					'receiver' => $uid
					));
					
				$dao->updateByCond(array(
					'sync_time' => time(),
					'result' => 'SYNC_FAILED',
				), array(
					'queue_id' => $queueId
				));
				
				continue;
			}
							
			$service = Better_Service_PushToOtherSites::factory($protocol, $username, $password, $oauth_token, $oauth_token_secret);
			if ($protocol == 'twitter.com' && !$oauth_token) {
				$token = $service->getToken();
				if ($token['oauth_token']) {
					$oauth_token = $token['oauth_token'];
					$oauth_token_secret = $token['oauth_token_secret'];	
										
					$bus = Better_User_Syncsites::getInstance($uid);
					$bus->delete($protocol);
					$bus->add($protocol, $username, $password, $oauth_token, $oauth_token_secret);
					$service = Better_Service_PushToOtherSites::factory($protocol, $username, $password, $oauth_token, $oauth_token_secret);
				} 
			}
			
	
			$poi = Better_Poi_Info::getInstance($poiId);
			$poiInfo = $poi->getBasic();
			$lang = Better_Language::loadIt($userInfo['language'] ? $userInfo['language'] : 'zh-cn');
			$sync_err_info = str_replace('{SNS}',$protocol,$lang->global->sync->pass_error);	
	
			$upbid = $row['upbid'];
			$_message = Better_DAO_SyncQueue::addUpMsg($upbid, $row['message'], $uid);
			$_message = stripslashes($_message);
			$_message = htmlspecialchars_decode($_message);
			if ( in_array($protocol, array('sina.com', 'qq.com', 'renren.com')) ) $_message = $service->parseBlogAt($_message);
			$row['message'] = str_replace('&apos;', "'", $_message);
			$message = Better_Service_PushToOtherSites::format($row, $userInfo, $poiInfo);
			
			if (trim($message) == '' && $protocol!= 'renren.com') {
				$message = '分享消息';
			}
			
			if ($protocol == 'qqsns.com') {
				$message .= " （来自开开——记录足迹，分享城事  http://k.ai）";
			}
			
			$service->fakeLogin($uid);
			try {
				switch ($type) {
					case 'unbind':
						$r = $service->delete($bid);
						$result = $r ? 'SUCCESS' : 'SYNC_FAILED';
						echo "\nSyncing Protocol: ".$protocol."\n"."Username: ".$username."\n"."Password: ".$password."\n"."unbind: ".$result."\n";
						$dao->updateByCond(array(
							'sync_time' => time(),
							'result' => $result,
							'sent_content' => ''
						), array(
							'queue_id' => $queueId
						));							
						break;
						
					case 'checkin':
						if ($poiInfo['poi_id']) {
							echo "\nSyncing Protocol: ".$protocol."\n"."Username: ".$username."\n"."Password: ".$password."\n"."Message: ".$message."\n";
							if ($service->post($message, $attach, $poiId, $geo)) {
								
								$dao->updateByCond(array(
									'sync_time' => time(),
									'result' => 'SUCCESS',
									'sent_content' => $message
								), array(
									'queue_id' => $queueId
								));	
								
								$third_id = $service->get3rdId();
								if ($third_id ){
									Better_DAO_SyncQueue::addThirdId($uid, $bid, $protocol, $third_id);									
								}
								
								if ($isGetFollowers) {
									if ($protocol == 'follow5.com') {
										sleep(3);
									}
									$followers = $service->getFollowers();
									//增加记录
									$data = array(
									    'uid' => $uid,
									    'protocol'  => $protocol,
									    'followers' => $followers,
									    'dateline' => time(),
									);
									$followers && Better_DAO_FollowersLog::getInstance()->insert($data);
								}
																	
							} else {
								echo "SYNC_CHECKIN_BLOG_FAILED\n";
								$dao->updateByCond(array(
									'sync_time' => time(),
									'result' => 'SYNC_FAILED',
									'sent_content' => $message
								), array(
									'queue_id' => $queueId
								));
								
								$ms = $user->cache()->get('sync_ms');
								if (0 && !$ms) {
									Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
										'content' => $sync_err_info.' <br /><br />'.$lang->global->sync->to_send_message.'<br />'.$message,
										'receiver' => $uid
										));
									//	登录失败，去除用户的绑定
									//Better_User::getInstance($uid)->syncsites()->delete($protocol);
									
									$user->cache()->set('sync_ms', 3600*24);
								}										
							}						
						} else {
							$dao->updateByCond(array(
								'sync_time' => time(),
								'result' => 'SYNC_FAILED',
								'sent_content' => $message
							), array(
								'queue_id' => $queueId
							));
							
							$ms = $user->cache()->get('sync_ms');
							if (0 && !$ms) {
								Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
									'content' => $sync_err_info.' <br /><br />'.$lang->global->sync->to_send_message.'<br />'.$message,
									'receiver' => $uid
									));
								//	登录失败，去除用户的绑定
								//Better_User::getInstance($uid)->syncsites()->delete($protocol);
								
								$user->cache()->set('sync_ms', 3600*24);
							}								
						}
						break;
					case 'normal':
					default:
						echo "\nSyncing Protocol: ".$protocol."\n"."Username: ".$username."\n"."Password: ".$password."\n"."Message: ".$message."\n";
						if ($service->post($message, $attach, $poiId, $geo)) {
							$dao->updateByCond(array(
								'sync_time' => time(),
								'result' => 'SUCCESS',
								'sent_content' => $message
							), array(
								'queue_id' => $queueId
							));
							
							$third_id = $service->get3rdId();
							if ($third_id ){
								Better_DAO_SyncQueue::addThirdId($uid, $bid, $protocol, $third_id);										
							}
		
							if ($isGetFollowers) {
								if ($protocol == 'follow5.com') {
									sleep(3);
								}									
								$followers = $service->getFollowers();
								//增加记录
								$data = array(
								    'uid' => $uid,
								    'protocol'  => $protocol,
								    'followers' => $followers,
								    'dateline' => time(),
								);
								$followers && Better_DAO_FollowersLog::getInstance()->insert($data);
							}
							
						} else {
							echo "SYNC_NORMAL_BLOG_FAILED\n";
							$dao->updateByCond(array(
								'sync_time' => time(),
								'result' => 'SYNC_FAILED',
								'sent_content' => $message								
							), array(
								'queue_id' => $queueId
							));
							
							$ms = $user->cache()->get('sync_ms');
							if (0 && !$ms) {
								Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
									'content' => $sync_err_info.' <br /><br />'.$lang->global->sync->to_send_message.'<br />'.$message,
									'receiver' => $uid
									));
								//	登录失败，去除用户的绑定
								//Better_User::getInstance($uid)->syncsites()->delete($protocol);
								
								$user->cache()->set('sync_ms', 3600*24);
							}								
						}
						break;
				}
			} catch (Exception $ee) {
				//
				Better_Log::getInstance()->logAlert($ee->getTraceAsString(), 'sync_crash_post');
				if ($retries[$row['uid']][$row['protocol']]>3) {
					$dao->updateByCond(array(
						'sync_time' => time(),
						'result' => 'SYNC_FAILED',
						'sent_content' => $message
					), array(
						'queue_id' => $queueId
					));	
					
					$ms = $user->cache()->get('sync_ms');
					if (0 && !$ms) {
						Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
							'content' => $sync_err_info.' <br /><br />'.$lang->global->sync->to_send_message.'<br />'.$message,
							'receiver' => $uid
							));
						//	登录失败，去除用户的绑定
						//Better_User::getInstance($uid)->syncsites()->delete($protocol);
						
						$user->cache()->set('sync_ms', 3600*24);
					}
				} else {
					if ($retries[$row['uid']][$row['protocol']]==1) {
						//sleep(15);
						$dao->updateByCond(array(
							'queue_time' => time() + 15 * 60,
							'sent_content' => $message
						), array(
							'queue_id' => $queueId
						));								
					} else if ($retries[$row['uid']][$row['protocol']]==2) {
						//sleep(30);
						$dao->updateByCond(array(
							'queue_time' => time() + 30 * 60,
							'sent_content' => $message
						), array(
							'queue_id' => $queueId
						));							
					} else if ($retries[$row['uid']][$row['protocol']]==3) {
						//sleep(45);
						$dao->updateByCond(array(
							'queue_time' => time() + 45 * 60,
							'sent_content' => $message
						), array(
							'queue_id' => $queueId
						));							
					} 
					$retries[$row['uid']][$row['protocol']] = $retries[$row['uid']][$row['protocol']]+1;
				}				
			}
		}
	} catch (Exception $ee) {
		Better_Log::getInstance()->logAlert($ee->getTraceAsString(), 'sync_crash');
		
		exit(0);
	}
	
	$row['uid'] && Better_User::destroyInstance($row['uid']);

	touch(THIS_PID);
	if ($protocol == 'zuosa.com') {
		sleep(5);
	} else {
		sleep(1);
	}
	
	$row['uid'] && $last_uid = $row['uid'];
}

exit(0);
