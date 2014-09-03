<?php

class Better_DAO_Admin_Usermanage extends Better_DAO_Admin_Base
{
	
	public static function getAllUsers($params)
	{
		$results = $data = array();
		
		$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
		$cacheKey = self::$cachePrefix.$cacheKey;
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$state = $params['state'] ? trim($params['state']) : '';
		$avatar = isset($params['avatar']) ? intval($params['avatar']) : -1;
		$reload = $params['reload'] ? 1 : 0;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$bedo_no = $params['bedo_no'] ? (int) $params['bedo_no'] : 0;
		$pagesize = $params['pagesize']?$params['pagesize']:BETTER_MAX_LIST_ITEMS;
		$limit = $page*$pagesize;
		//Better_Cache_Lock::getInstance()->wait($cacheKey);

		if (!parent::getDbCacher()->test($cacheKey) || $reload==1) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			$serverIds = parent::getServerIds();
			$count = 0;
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'account AS a', '*');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array(
					'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
					'p.tags', 'p.avatar', 'p.favorites', 'p.live_province', 'p.live_city',
					'p.now_posts', 'p.posts', 'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
					'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn', 'p.gtalk', 'p.received_msgs', 'p.sent_msgs', 'p.new_msgs', 'p.files', 'p.last_checkin_poi',
					'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.state', 'p.sys_priv_blog','p.last_update', 'p.rp','p.karma',
					));
				if($bedo_no>0){
					$select->join(BETTER_DB_TBL_PREFIX.'account_bedosync AS bedo', 'bedo.uid=a.uid',array());
					$select->where('bedo.jid=?', $bedo_no);
				}
				
				if ($keyword!='') {
					$select->where($rdb->quoteInto('p.birthday LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.cell_no LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.self_intro LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_city LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_province LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.gender LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.email LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.uid LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.username  LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.nickname  LIKE ?', '%'.$keyword.'%'));
				}
				
				if($state!=''){
					$select->where($rdb->quoteInto('p.state LIKE ?', '%'.$state.'%'));
				}
				
				if ($from>0) {
					$select->where('p.last_update>=?', $from);
				}
				
				if ($to>0) {
					$select->where('p.last_update<=?', $to);
				}
				
				if ($avatar>=0) {
					if ($avatar==0) {
						$select->where('p.avatar=\'\' OR p.avatar IS NULL');
					} else {
						$select->where('p.avatar!=\'\' AND p.avatar IS NOT NULL');
					}
				}
				
				$select->order('p.last_update DESC');
				
				$select->limit($limit);
				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();				
				foreach($rows as $row) {
					$data[$row['last_update'].(1000000+intval($row['uid']))] = $row;
				}					
			}
			$count = self::getUserCount($params);
			krsort($data);
			//$tmp = array_chunk($data, BETTER_MAX_LIST_ITEMS);
			$results['rows'] = &$data;
			unset($data);
			$results['count'] = $count;
			parent::getDbCacher()->set($cacheKey, $results, 300);
			
			Better_Cache_Lock::getInstance()->release($cacheKey);
		} else {
			$results = parent::getDbCacher()->get($cacheKey);
		}

		return $results;
	}
	public  static function getUserCount($params){
		$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
		$cacheKey = self::$cachePrefix.$cacheKey.'_usercount';
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$state = $params['state'] ? trim($params['state']) : '';
		$avatar = isset($params['avatar']) ? intval($params['avatar']) : -1;
		$reload = $params['reload'] ? 1 : 0;
		$bedo_no = $params['bedo_no'] ? (int) $params['bedo_no'] : 0;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		if (!parent::getDbCacher()->test($cacheKey) || $reload==1) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			$serverIds = parent::getServerIds();
			$count = 0;
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'account AS a', 'count(*) AS ucount');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array());		
				if($bedo_no>0){
					$select->join(BETTER_DB_TBL_PREFIX.'account_bedosync AS bedo', 'bedo.uid=a.uid',array());
					$select->where('bedo.jid=?', $bedo_no);
				}
						
				if ($keyword!='') {
					$select->where($rdb->quoteInto('p.birthday LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.cell_no LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.self_intro LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_city LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_province LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.gender LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.email LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.uid LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.username  LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.nickname  LIKE ?', '%'.$keyword.'%'));
				}
				
				if($state!=''){
					$select->where($rdb->quoteInto('p.state LIKE ?', '%'.$state.'%'));
				}
				
				if ($from>0) {
					$select->where('p.last_update>=?', $from);
				}
				
				if ($to>0) {
					$select->where('p.last_update<=?', $to);
				}
				
				if ($avatar>=0) {
					if ($avatar==0) {
						$select->where('p.avatar=\'\' OR p.avatar IS NULL');
					} else {
						$select->where('p.avatar!=\'\' AND p.avatar IS NOT NULL');
					}
				}
				$rs = parent::squery($select, $rdb);
				$result = $rs->fetch();
				$count += (int)$result['ucount'];
			}
			
			parent::getDbCacher()->set($cacheKey, $count);
			
			Better_Cache_Lock::getInstance()->release($cacheKey);
		} else {
			$count = parent::getDbCacher()->get($cacheKey);
		}

		return $count;
		
	}
	
	
	public static function banAccount($params){
		
		$result = 0;
		
		$uid = $params['uid']? $params['uid'] : '';
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		$old_state = $params['old_state']? $params['old_state']: '';
		$now_state = Better_User_State::BANNED;
		$dateline = time();
		$act_type = $params['act_type']? $params['act_type']:'';
		$reason = $params['reason']? $params['reason']: '';
		$resetinfo = $params['resetinfo']?$params['resetinfo']:'';
		if($uid&&$resetinfo){
			$data = array('username'=>'kai'.$uid,'nickname'=>'kai'.$uid,'self_intro'=>'','state'=>$now_state);
		}else{
			$data = array('state'=>$now_state);
		}
		
		$result = Better_User::getInstance($uid)->updateUser($data);
		
		if($result){
			
			Better_DAO_Admin_Banaccountlog::getInstance()->insert(
				array('admin_uid'=>$admin_uid,
					'uid'=>$uid,
					'old_state'=>$old_state,
					'now_state'=>$now_state,
					'dateline'=>$dateline,
					'act_type'=>$act_type,
					'reason'=>$reason
				)
			) && $result = 1;
		
			if ($result) {
				Better_DAO_User_Banned::getInstance($uid)->save();
			}
		}
		
		return $result;
		
	}
	
	
	public static function unbanAccount($params){
		
		$result = 0;
		
		$uid = $params['uid']? $params['uid'] : '';
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		$old_state = Better_User_State::BANNED;
		$now_state = Better_DAO_Admin_Banaccountlog::getInstance()->getOldState($uid);
		$now_state = $now_state? $now_state: 'enabled';
		$dateline = time();
		$act_type = $params['act_type']? $params['act_type']:'';
		$reason = $params['reason']? $params['reason']: '';
		
		$result = Better_User::getInstance($uid)->updateUser(array('state'=>$now_state));
		
		if($result){
			
			Better_DAO_Admin_Banaccountlog::getInstance()->insert(
				array('admin_uid'=>$admin_uid,
					'uid'=>$uid,
					'old_state'=>$old_state,
					'now_state'=>$now_state,
					'dateline'=>$dateline,
					'act_type'=>$act_type,
					'reason'=>$reason
				)
			) && $result = 1;
		
			if ($result) {
				Better_DAO_User_Banned::getInstance($uid)->clean();
			}
		}
		
		return $result;
		
	}
	
	public static function lockAccount($params){
		
		$result = 0;
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		$uid = $params['uid']? $params['uid'] : '';
		if($uid){
			$user = Better_User::getInstance($uid)->getUserInfo();
			Better_Log::getInstance()->logInfo('==='.$uid.'===='.$user['uid'].'==='.$user['sys_priv_blog'].'=====','testdebug');
			if($user['state']!=Better_User_State::BANNED && $user['sys_priv_blog']!=1){
				
				$result = Better_User::getInstance($uid)->updateUser(array('sys_priv_blog'=>1));
				
				
				if($result){
					
					Better_DAO_Admin_Banaccountlog::getInstance()->insert(
						array('admin_uid'=>$admin_uid,
							'uid'=>$uid,
							'old_state'=>'',
							'now_state'=>'',
							'dateline'=>time(),
							'act_type'=> 'lock_account',
							'reason'=>''
						)
					);
					
					
					Better_Hook::factory(array(
				 	 'Admin_DirectMessage',
					))->invoke('UserLocked', array(
					'userInfo' => $user
					));
				}
			}
		}
	
		return $result;	
	}
	
	public static function unlockAccount($params){
		
		$result = 0;
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		$uid = $params['uid']? $params['uid'] : '';
		if($uid){
			$user = Better_User::getInstance($uid)->getUserInfo();
			if($user['state']!=Better_User_State::BANNED && $user['sys_priv_blog']!=0){
				$result = Better_User::getInstance($uid)->updateUser(array('sys_priv_blog'=>0));
				
				if($result){
					
					Better_DAO_Admin_Banaccountlog::getInstance()->insert(
						array('admin_uid'=>$admin_uid,
							'uid'=>$uid,
							'old_state'=>'',
							'now_state'=>'',
							'dateline'=>time(),
							'act_type'=> 'unlock_account',
							'reason'=>''
						)
					);
					
					Better_Hook::factory(array(
				 	 'Admin_DirectMessage',
					))->invoke('UserUnlocked', array(
					'userInfo' => $user
					));
				}
			}
		}
	
		return $result;	
	}
	
	
	public static function muteAccount($uid){
		$result = 0;
		$state = Better_User_State::MUTE;
		$karma = -60;
		
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		
		if($uid){
			$user = Better_User::getInstance($uid)->getUserInfo();
			$old_state = $user['state'];
			$now_state = $state;
			$dateline = time();
			$act_type = 'mute_account';
			$reason = '';
			
			if($user['state']!=$state && $user['state']!=Better_User_State::BANNED){
				$result = Better_User::getInstance($uid)->updateUser(array('state'=>$state), true, $admin_uid);
			}
			
		if($result){
			Better_DAO_Admin_Banaccountlog::getInstance()->insert(
				array('admin_uid'=>$admin_uid,
					'uid'=>$uid,
					'old_state'=>$old_state,
					'now_state'=>$now_state,
					'dateline'=>$dateline,
					'act_type'=>$act_type,
					'reason'=>$reason
				)
			);
			
				Better_Hook::factory(array(
				 'Admin_DirectMessage',
				))->invoke('UserMuted', array(
				'userInfo' => $user
				));

			}
		
		}
		
		return $result;
	}
	
	
	public static function unmuteAccount($uid){
		$result = 0;
		$state = Better_User_State::ENABLED;
		$karma = 1;
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		if($uid){
			$user = Better_User::getInstance($uid)->getUserInfo();
			$old_state = Better_User_State::MUTE;
			$now_state = Better_DAO_Admin_Banaccountlog::getInstance()->getOldState($uid, Better_User_State::MUTE);
			$now_state = $now_state? $now_state: 'enabled';
			$dateline = time();
			$act_type = 'unmute_account';
			$reason = '';
			
			if($user['state']!=$now_state && $user['state']!=Better_User_State::BANNED){
				$result = Better_User::getInstance($uid)->updateUser(array('state'=>$now_state), true, $admin_uid);
			}
			
			if($result){
			Better_DAO_Admin_Banaccountlog::getInstance()->insert(
				array('admin_uid'=>$admin_uid,
					'uid'=>$uid,
					'old_state'=>$old_state,
					'now_state'=>$now_state,
					'dateline'=>$dateline,
					'act_type'=>$act_type,
					'reason'=>$reason
				)
			);
			
			Better_Hook::factory(array(
				 'Admin_DirectMessage',
				))->invoke('UserUnmuted', array(
				'userInfo' => $user
			));
		
			}
			
		}
		
		return $result;
	}

}