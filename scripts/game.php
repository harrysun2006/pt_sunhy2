<?php

/**
 * 游戏进程处理
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

define('THIS_PID', dirname(__FILE__).'/game.pid');

Better_Session::factory()->init();
$sess = Better_DAO_Game_Session::getInstance();

$lang = Better_Language::load();

$logFile = APPLICATION_PATH.'/../logs/game_daemon.log';
$log = APPLICATION_PATH.'/../logs/game_daemon_backup.log';
file_exists($log) && @unlink($log);
copy($logFile, $log);

while(true) {
	try {
		
		//	Clean invites
		$timeoutInvites = $sess->getTimeoutInvite();
		$sids = array();
		foreach ($timeoutInvites as $row) {
			$starterUid = $row['starter_uid'];
			$coplayerUid = $row['coplayer_uid'];
			
			$starterUser = Better_User::getInstance($starterUid);
			$starterUserInfo = $starterUser->getUserInfo();
			$coplayerUser = Better_User::getInstance($coplayerUid);
			$coplayerUserInfo = $coplayerUser->getUserInfo();
	
			$timeoutContent = $starterUser->getUserLang()->api->game->invite->timeout;
			$coplayerUser->notification()->game()->send(array(
				'type' => 'game_over',
				'receiver' => $starterUid,
				'content' => str_replace('{NICKNAME}', $coplayerUserInfo['nickname'], $timeoutContent),
				'sid' => $row['session_id'],
				));
			$coplayerUser->notification()->game()->updateDelivedBySid($row['session_id']);
			
			Better_Hook::factory(array(
				'Ppns'
			))->invoke('GameInviteTimeout', array(
				'starter_uid' => $starterUid,
				'coplayer_uid' => $coplayerUid,
				'session_id' => $row['session_id']
			));
				
			$sids[] = $row['session_id'];
		}
		
		if (count($sids)>0) {
			$sess->updateByCond(array(
				'expired' => '1',
			), array(
				'session_id' => $sids,
				'ended' => 0,
				'start_time' => 0,
			));
		}
		
		//	Clean Treasure
		$timeoutTreasures = $sess->getTimeoutTreasure();
		$sids = array();
	
		foreach ($timeoutTreasures as $row) {
			$starterUid = $row['starter_uid'];
			$coplayerUid = $row['coplayer_uid'];
			
			$starterUser = Better_User::getInstance($starterUid);
			$starterUserInfo = $starterUser->getUserInfo();
			$coplayerUser = Better_User::getInstance($coplayerUid);
			$coplayerUserInfo = $coplayerUser->getUserInfo();
	
			if ($row['starter_pickup']==0) {
				$content = $starterUser->getUserLang()->api->game->treasure->timeout;
				$coplayerUser->notification()->game()->send(array(
					'type' => 'game_over',
					'receiver' => $starterUid,
					'content' => $content,
					'sid' => $row['session_id'],
					));			
			}
	
			if ($row['coplayer_pickup']==0) {
				$content = $coplayerUser->getUserLang()->api->game->treasure->timeout;
				$starterUser->notification()->game()->send(array(
					'type' => 'game_over',
					'receiver' => $coplayerUid,
					'content' => $content,
					'sid' => $row['session_id'],
					));
			}
				
			$sids[] = $row['session_id'];
			
			Better_Hook::factory(array(
				'Ppns'
			))->invoke('GameTreasureTimeout', array(
				'starter_uid' => $starterUid,
				'coplayer_uid' => $coplayerUid,
				'session_id' => $row['session_id'],
				'sess' => $row
			));			
		}
		
		if (count($sids)>0) {
			$sess->updateByCond(array(
				'expired' => '1',
			), array(
				'session_id' => $sids,
				'ended' => '1',
			));
		}
			
		$sids = array();
		
		$sess->cleanExpired();
		$rows = $sess->runningSessions();
	
		foreach ($rows as $row) {
			$sessId = $row['session_id'];
			$startTime = $row['start_time'];
			$endTime = $row['end_time'];
			
			if (time()>$endTime && !$row['ended']) {
				
				$starterUid = $row['starter_uid'];
				$coplayerUid = $row['coplayer_uid'];
				
				$starterUser = Better_User::getInstance($starterUid);
				$coplayerUser = Better_User::getInstance($coplayerUid);
				
				$gameTreasures = Better_Treasure::randomThrow();
	
				if (count($gameTreasures)>=2) {
					$starterTreasure = $gameTreasures[0];
					$coplayerTreasure = $gameTreasures[1];
					
					$starterTreasure['name'] = Better_Language::loadDbKey('name', $starterTreasure, $starterUser->getUserLanguage());
					$coplayerTreasure['name'] = Better_Language::loadDbKey('name', $coplayerTreasure, $coplayerUser->getUserLanguage());					
					
					if ($starterTreasure['ratio']<=0.001) {
						Better_Email_Alert::send(array(
							'uid' => $starterUid,
							'treasure_id' => $starterTreasure['id'],
							'treasure_name' => $starterTreasure['name']
							));
					}
					
					if ($coplayerTreasure['ratio']<=0.001) {
						Better_Email_Alert::send(array(
							'uid' => $coplayerUid,
							'treasure_id' => $coplayerTreasure['id'],
							'treasure_name' => $coplayerTreasure['name']
							));
					}				
					
					Better_User::getInstance($starterUid)->treasure()->log(array(
						'category' => 'got',
						'co_uid' => $coplayerUid,
						'treasure_id' => $starterTreasure['id'],
						));
						
					Better_User::getInstance($coplayerUid)->treasure()->log(array(
						'category' => 'got',
						'co_uid' => $starterUid,
						'treasure_id' => $coplayerTreasure['id'],
						));
						
					$cLang = $coplayerUser->getUserLang();
					Better_User::getInstance($starterUid)->notification()->game()->send(array(
						'type' => 'game_result',
						'receiver' => $coplayerUid,
						'content' => isset($coplayerTreasure['name']) ? str_replace('{TREASURE_NAME}', $coplayerTreasure['name'], $cLang->api->game->result->title) : $cLang->api->game->result->empty,
						'sid' => $sessId,
						));
					
					$sLang = $starterUser->getUserLang();
					Better_User::getInstance($coplayerUid)->notification()->game()->send(array(
						'type' => 'game_result',
						'receiver' => $starterUid,
						'content' => isset($starterTreasure['name']) ? str_replace('{TREASURE_NAME}', $starterTreasure['name'], $sLang->api->game->result->title) : $sLang->api->game->result->empty,
						'sid' => $sessId,
						));
						
					Better_Hook::factory(array(
						'Ppns'
					))->invoke('GameResult', array(
						'starter_uid' => $starterUid,
						'coplayer_uid' => $coplayerUid,
						'session_id' => $row['session_id'],
						'starter_treasure' => $starterTreasure,
						'coplayer_treasure' => $coplayerTreasure
					));							
				}
		
				$sess->updateByCond(array(
					'ended' => 1,
					'starter_treasure' => $starterTreasure['id'],
					'coplayer_treasure' => $coplayerTreasure['id']
				), array(
					'session_id' => $sessId,
					'ended' => 0,
					));
			}
		}
		
	} catch (Exception $e) {
		
		Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'game_crash', true);
		
		exit(0);
	}
	
	touch(THIS_PID);
	sleep(1);
}
