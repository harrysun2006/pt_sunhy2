<?php

/**
 * 挖宝游戏
 * 
 * @package Better.DAO.Game
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Game_Hunting extends Better_DAO_Game_Base
{
	protected static $instance = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}	
	
	
	/**
	 * 获得可以邀请挖宝的用户
	 * 
	 * 用户报到后即可查看附近的(50KM--后端可调)内
	 * 最近(30分钟--后端可调)报到过的用户, 
	 * 应去除任一方向有阻止关系的人及放风次数已经用完的人.
	 * 
	 * @param integer $starterUid
	 * @return array
	 */
	public static function getReadyUsers($starterUid)
	{
		$results = array();
		$ppnsUsers = array();
		$hasPpns = false;
	
		if (APPLICATION_ENV=='home' || APPLICATION_ENV=='home_line1') {
			$puids = array(175676);	
		} else {
			if (BETTER_PPNS_ENABLED) {
				$puids = Better_Ppns::getInstance()->statusCheck();
			}
		}		
		
		//	先获取不能挖宝的人
		$cantUids = Better_DAO_Game_Session::getInstance()->todayCantUids('hunting');

		if (BETTER_PPNS_ENABLED) {
			$user = Better_User::getInstance($starterUid);
			$blockeds = $user->blocks;
			$blockedby = $user->blockedby;
			foreach ($puids as $uid) {
				(!in_array($uid, $cantUids) && !in_array($uid, $blockeds) && !in_array($uid, $blockedby) && $uid!=$starterUid) && $ppnsUsers[] = $uid;
			}
		}
		
		if (!Better_Config::getAppConfig()->game->only_ppns) {
			$starter = Better_User::getInstance($starterUid);
			$starterUserInfo = $starter->getUserInfo();
			$x = $starterUserInfo['x'];
			$y = $starterUserInfo['y'];
			$range = 99999999;
			$notAllowedState = array(
				Better_User_State::BANNED, 
				Better_User_State::MUTE,
				Better_User_State::UPDATE_VALIDATING
				);
			
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
			foreach($sids as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = &$cs['r'];
				
				$sql = "SELECT p.uid
				FROM `".BETTER_DB_TBL_PREFIX."profile` AS p
					LEFT JOIN `".BETTER_DB_TBL_PREFIX."blocking` AS bl
						ON p.uid=bl.uid AND bl.blocking_uid=".$starterUid." AND bl.uid IS NULL
					LEFT JOIN `".BETTER_DB_TBL_PREFIX."blockedby` AS blb
						ON p.uid=blb.uid AND blb.blocked_by_uid=".$starterUid." AND blb.uid IS NULL
					LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_hunting_silent` AS hs
						ON p.uid=hs.uid
				WHERE p.uid!=".$starterUid." AND p.state NOT IN ('".implode("','", $cantUids)."') AND p.lbs_report>=".(time()-1800)." 
					AND (hs.expire_time IS NULL OR hs.expire_time<".time().")
				";
				if (Better_Config::getAppConfig()->game->exclude_web_checkin) {
					$sql .= " AND p.last_checkin_from!='web'";
				}
	
				$rs = self::squery($sql, $rdb);
				$rows = $rs->fetchAll();
				foreach($rows as $v) {
					$results[] = $v['uid'];
				}
			}
		}
				
		if (count($ppnsUsers)>0) {
			$results = array_unique(array_merge($results, $ppnsUsers));
		}
	
		// 免打扰用户
		$silentUids = Better_DAO_User_Hunting_Silent::getAllSilent();
		if (count($silentUids)>0) {
			$results = array_unique(array_diff($results, $silentUids));
		}

		return $results;		
	}
}