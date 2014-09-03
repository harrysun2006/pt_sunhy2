<?php

/**
 * POI入住用户
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Poi_Visitors extends Better_DAO_Poi_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->orderKey = 'dateline';
		parent::__construct();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}

	public static function count($poiId, $params=array())
	{
		// 参数'uid': 返回我、好友、其他人来此poi的人数
		$uid = is_array($params) && array_key_exists('uid', $params) ? (int)$params['uid'] : 0;
		$result = array(
			'me' => 0,
			'friend' => 0,
			'other' => 0,
			'total' => 0,
		);

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$sql = "SELECT COUNT(distinct b.uid) ucount";
			if ($uid) $sql .= ", IF(b.uid=$uid,1,0) is_me, IF(bf.uid,1,0) is_friend";
			$sql .= " FROM `".BETTER_DB_TBL_PREFIX."blog` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."account` AS a ON a.uid=b.uid
				LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_banned` AS ub ON ub.uid=b.uid";
			if ($uid) $sql .= " LEFT JOIN `".BETTER_DB_TBL_PREFIX."friends` AS bf ON bf.uid=b.uid AND bf.friend_uid=$uid ";
			$sql .= " WHERE b.type='checkin' AND b.priv!='private' AND ub.uid IS NULL AND b.poi_id='" . $poiId . "'";
			if ($uid) $sql .= " GROUP BY IF(b.uid=$uid,1,0), IF(bf.uid,1,0)";
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				if ($uid) {
					if ($row['is_me'] == 1) $result['me'] += $row['ucount'];
					else if ($row['is_friend'] == 1) $result['friend'] += $row['ucount'];
					else $result['other'] += $row['ucount'];
				}
				$result['total'] += $row['ucount'];
			}
		}
		return $result;		
	}
	
	
	public static function countLog($poiId, $params=array())
	{
		// 参数'uid': 返回我、好友、其他人来此poi的人数
		$uid = is_array($params) && array_key_exists('uid', $params) ? (int)$params['uid'] : 0;
		$result = array(
			'me' => 0,
			'friend' => 0,
			'other' => 0,
			'total' => 0,
		);

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$sql = "SELECT COUNT(distinct b.uid) ucount";
			if ($uid) $sql .= ", IF(b.uid=$uid,1,0) is_me, IF(bf.uid,1,0) is_friend";
			$sql .= " FROM `".BETTER_DB_TBL_PREFIX."user_place_log` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."account` AS a ON a.uid=b.uid
				LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_banned` AS ub ON ub.uid=b.uid";
			if ($uid) $sql .= " LEFT JOIN `".BETTER_DB_TBL_PREFIX."friends` AS bf ON bf.uid=b.uid AND bf.friend_uid=$uid ";
			$sql .= " WHERE ub.uid IS NULL AND b.poi_id='" . $poiId . "'";
			if ($uid) $sql .= " GROUP BY IF(b.uid=$uid,1,0), IF(bf.uid,1,0)";
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				if ($uid) {
					if ($row['is_me'] == 1) $result['me'] += $row['ucount'];
					else if ($row['is_friend'] == 1) $result['friend'] += $row['ucount'];
					else $result['other'] += $row['ucount'];
				}
				$result['total'] += $row['ucount'];
			}
		}
		return $result;		
	}

	public static function search($poiId, $page=1, $count=BETTER_PAGE_SIZE, $avatar=null, $params=array())
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		// 参数'uid': 按好友, 非好友排序
		$uid = is_array($params) && array_key_exists('uid', $params) ? (int)$params['uid'] : 0;
		$type = $params['type']?$params['type']:"checkin";
		$timestart = $params['timestart']?$params['timestart']:0;
		$timeend = $params['timeend']?$params['timeend']:0;
 		$limit = $page*$count+1;
		
		$results = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$sql = "SELECT count(*) as times,b.uid, b.source, MAX(b.dateline) AS checkin_time, a.email, COUNT(b.uid) as checkin_count,
				p.username, p.nickname, p.gender, p.birthday, p.self_intro, p.language, p.tags, p.avatar, p.live_province, p.live_city, p.visits, p.visited, p.priv_profile, 
					p.priv_blog, p.last_active, p.last_bid, p.status, p.address, p.lbs_report, p.city, p.msn, p.gtalk, X(p.xy) AS x, Y(p.xy) AS y, p.range, p.state, p.karma,
					p.last_checkin_poi, p.timezone, p.email4person, p.email4community, p.email4product, p.rp,
				c.followings, c.followers, c.favorites, c.now_posts, c.posts, c.received_msgs, c.sent_msgs, c.new_msgs, c.files, c.friends, c.majors, c.places, c.invites, c.checkins";
			if ($uid) $sql .= ", IF(b.uid=$uid,1,0) is_me, IF(bf.uid,1,0) is_friend";
			$sql .= " FROM `".BETTER_DB_TBL_PREFIX."blog` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."account` AS a ON a.uid=b.uid
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile` AS p ON p.uid=b.uid 
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile_counters` AS c ON c.uid=b.uid
				LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_banned` AS ub ON ub.uid=b.uid";
			if ($uid) $sql .= " LEFT JOIN `".BETTER_DB_TBL_PREFIX."friends` AS bf ON b.uid=bf.uid AND bf.friend_uid=$uid ";
			$sql .= " WHERE b.type='".$type."' AND b.priv!='private' AND ub.uid IS NULL AND b.poi_id='".$poiId."'";
			if ($avatar===true) {
				$sql .= " AND p.avatar!=''";
			}
			if($timeend && $timestart){
				$sql .= " AND b.dateline>$timestart  AND b.dateline<$timeend";
			}
			$sql .= "
			GROUP BY b.uid";
//			if($timeend && $timestart){
//				$sql .= " HAVING checkin_time>$timestart  AND checkin_time<$timeend";
//			}
			if ($uid) $sql .= " ORDER BY IF(b.uid=$uid,1,0) DESC, IF(bf.uid,1,0) DESC, checkin_time DESC";
			else $sql .= " ORDER BY checkin_time DESC";
			$sql .= " LIMIT ".$limit;
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				if ($uid) $key = $v['is_me'].$v['is_friend'].$v['checkin_time'].'.'.(10000000-$v['uid']);
				else $key = $v['times'].$v['checkin_time'].'.'.(10000000-$v['uid']);
				$results[$key] = $v;
			}
		}

		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);

			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}
		return $return;		
	}
		
	

	public static function searchMeeting($page=1, $count=BETTER_PAGE_SIZE)
	{
		$poiId = Better_Config::getAppConfig()->poi->meeting_poi_id;
		$sysUid = Better_Config::getAppConfig()->user->meeting_user_id;
		
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.uid',
				//'b.dateline AS checkin_time',
				new Zend_Db_Expr('MAX(b.dateline) AS checkin_time')
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.tags', 'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog', 
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.msn', 'p.gtalk', 
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.state', 'p.karma',
				'p.last_checkin_poi', 'p.timezone', 'p.email4person', 'p.email4community', 'p.email4product' 
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.now_posts', 'c.posts', 
				'c.received_msgs', 'c.sent_msgs', 'c.new_msgs', 'c.files', 'c.friends', 'c.majors',
				'c.places', 'c.invites', 'c.checkins',
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->join(BETTER_DB_TBL_PREFIX.'friends AS f', 'f.friend_uid='.$sysUid.' AND f.uid=p.uid', array());
			$select->group('b.uid');
			$select->where('b.type=?', 'checkin');
			$select->where('b.priv=?', 'public');
			//$select->where('p.priv_blog=?', 'public');
			$select->where('b.poi_id=?', $poiId);
			
			$select->order('checkin_time DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['checkin_time'].'.'.(10000000-$v['uid'])] = $v;
			}
		}
		
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);

			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}

		return $return;		
	}	
	
	public static function searchcount($poiId, $page=1, $count=BETTER_MERGE_PAGE_SIZE)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS b',array('count(b.id) as checkintimes','b.uid'));	
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.tags', 'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog', 
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.msn', 'p.gtalk', 
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.state', 'p.karma',
				'p.last_checkin_poi', 'p.timezone', 'p.email4person', 'p.email4community', 'p.email4product' 
				));	
			$select->where('b.poi_id=?', $poiId);
	
			$select->order('checkintimes DESC');
			$select->group('b.uid');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['checkintimes'].'.'.$v['uid']] = $v;
			}
		}	
		
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);

			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}	

		return $return;		
	}
	
	public static function searchtimecount($poiId, $page=1, $count=BETTER_MERGE_PAGE_SIZE)
	{

		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS b',array('count(b.uid) as checkintimes','date_format(from_unixtime(b.checkin_time+8*3600),\'%H\') as hour'));		
			$select->where('b.poi_id=?', $poiId);		
			$select->group('date_format(from_unixtime(b.checkin_time),\'%H\')');

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();	
			foreach($rows as $v) {
				$results[$v['hour'].'.'.$sid] = $v;
			}	
			
		}	
		
		if (count($results)>0) {
			$return['total'] = count($results);	
	
			krsort($results);
			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);
			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}	
	
		return $return;		
	}
	public static function searchdatecount($poiId, $page=1, $count=BETTER_MERGE_PAGE_SIZE)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
		
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			
			$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS b',array('count(b.id) as checkintimes','date_format(from_unixtime(b.checkin_time+8*3600),\'%c-%e\') as days'));
			$select->where('TO_DAYS(now())-TO_DAYS(from_unixtime(b.checkin_time+8*3600))<=?', '30');		
			$select->where('b.poi_id=?', $poiId);			
			$select->order('checkin_time DESC');
			$select->group('date_format(from_unixtime(b.checkin_time+8*3600),\'%c %e\')');
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['days'].'.'.$sid] = $v;
			}			
		}
		

		if (count($results)>0) {
			$return['total'] = count($results);			
			krsort($results);
			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);
			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}	
		return $return;		
	}
	
	public static function searchgendercount($poiId, $page=1, $count=BETTER_PAGE_SIZE)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];		
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS b', array(
				'count(DISTINCT(b.uid)) as checkintimes'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array( 'p.gender as gender'));
			$select->where('b.poi_id=?', $poiId);			
			$select->group('p.gender');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['gender'].'.'.$sid] = $v;
			}			
		}		
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);

			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}		
		return $return;		
	}
	
	public static function searchGetitlouder($page=1, $count=BETTER_PAGE_SIZE)
	{
		$poiId = array(Better_Config::getAppConfig()->poi->getitlouder->bj->id,Better_Config::getAppConfig()->poi->getitlouder->sh->id);
		
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.uid',
				//'b.dateline AS checkin_time',
				new Zend_Db_Expr('MAX(b.dateline) AS checkin_time')
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.tags', 'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.msn', 'p.gtalk', 
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.state', 'p.karma',
				'p.last_checkin_poi', 'p.timezone', 'p.email4person', 'p.email4community', 'p.email4product' 
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.now_posts', 'c.posts', 
				'c.received_msgs', 'c.sent_msgs', 'c.new_msgs', 'c.files', 'c.friends', 'c.majors',
				'c.places', 'c.invites', 'c.checkins',
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			
			$select->group('b.uid');
			$select->where('b.type=?', 'checkin');
			$select->where('b.priv=?', 'public');
			//$select->where('p.priv_blog=?', 'public');
			$select->where('b.poi_id IN (?)', $poiId);			
			$select->order('checkin_time DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['checkin_time'].'.'.(10000000-$v['uid'])] = $v;
			}
		}
		
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);

			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}

		return $return;		
	}
	
	
	
	public static function getlotspoiTopcheckin($params)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		$poilist = $params['poilist'];
		$begintm = $params['begintm'];
		$endtm = $params['endtm'];
		$results = array();
		$poi_str = implode(",",$poilist);
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$sql = "select count(l.id) as checkintimes,l.uid,max(l.checkin_time) as checkin_time,f.nickname,f.username from better_user_place_log as l left join better_profile as f on f.uid=l.uid where l.checkin_time>=".$begintm." and l.checkin_time<=".$endtm." and checkin_score>0 and l.poi_id in (".$poi_str.") GROUP BY l.uid order by count(l.id) desc limit 0,10";
				
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['checkintimes'].'.'.$v['uid']] = $v;
			}
		}	
	
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);			
			krsort($results);
			$data = array_chunk($results, 10);
			
			unset($results);
			$ps = array_chunk($data[0], 10);
			if (isset($ps[0])) {
				$return['rows'] = &$ps[0];
			}
		}
		
		return $return;		
	}
	
	public static function getlotspoicheckin($params)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		$poilist = $params['poilist'];
		$begintm = $params['begintm'];
		$endtm = $params['endtm'];
		$results = array();
		$poi_str = implode(",",$poilist);
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$sql = "select l.id as bid,l.uid,l.checkin_time,f.nickname,f.username,l.poi_id,l.checkin_time as dateline from better_user_place_log as l left join better_profile as f on f.uid=l.uid where l.checkin_time>=".$begintm." and l.checkin_time<=".$endtm." and checkin_score>0 and l.poi_id in (".$poi_str.") order by l.checkin_time desc limit 0,10";				
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();			
			foreach($rows as $v) {
				$results[$v['checkin_time'].'.'.$v['uid']] = $v;
				$tempuserinfo = Better_User::getInstance($v['uid'])->getUserInfo(); 
				$results[$v['checkin_time'].'.'.$v['uid']]['avatar_url']= $tempuserinfo['avatar_url'];
				$results[$v['checkin_time'].'.'.$v['uid']]['poi']= Better_Poi_Info::getInstance($v['poi_id'])->getBasic();			
				
			}
		}	
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);			
			krsort($results);
			$data = array_chunk($results, 10);			
			unset($results);
			$ps = array_chunk($data[0], 10);
			if (isset($ps[0])) {
				$return['rows'] = &$ps[0];
				$return['count'] = count($return['rows']);
			}
		}		
		return $return;		
	}
}