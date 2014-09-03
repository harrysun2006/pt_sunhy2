<?php

/**
 * 取用户相关微博的DAO
 * 
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_User_Blog extends Better_DAO_Base
{
	private static $instance = array();

	private $profileTbl = '';
	private $attachTbl = '';

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'blog';
		$this->profileTbl = BETTER_DB_TBL_PREFIX.'profile';
		$this->attachTbl = BETTER_DB_TBL_PREFIX.'attachments';
		$this->priKey = 'bid';
		$this->orderKey = 'dateline';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}	
	
	public function getCount($type)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('type=?', $type);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	public function rangedTips(array $params)
	{
		/**
		* 2011-07-14: 增加优质贴士逻辑：
		* 1. 人气贴士(单POI),全部显示
		*    置顶、优质、普通排序
		*    order='poll' -> 投票并分一周内、一周前
		* 2. 附近贴士(多POI)按置顶、优质、普通排序, 如有优质贴士则显示置顶和优质贴士，否则显示全部贴士(暂时全部显示)
		*    不考虑时间和投票
		*/ 
		$r1 = array();
		$r2 = array();
		$rr = array();

		$uid = $this->identifier;
		$page = (int)$params['page'];
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (float)$params['range'];
		$poiId = (int)$params['poi_id'];
		$order = $params['order'] ? $params['order'] : '';

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		$has_featured = 0;
		$show_all = Better_Config::getAppConfig()->ranged_tips->showall;
		isset($show_all) || $show_all = 1;
		$now = time();

		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from($this->tbl.' AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down', new Zend_Db_Expr('(b.up-b.down) AS poll_result'), 'b.is_top', 'b.featured'
				));
			$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report', 'p.allow_rt', 'p.sync_badge'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));			
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'rtblog_counters AS rtcount', 'b.bid=rtcount.bid', 'rtcount.nums as comments');
			$select->where('b.type=?', 'tips');
			$select->where('b.checked=?', 1);
			
			if ($poiId > 0) {
				$select->where('b.poi_id=?', $poiId);
			} else if ($lon && $lat) {
				list($x, $y) = Better_Functions::LL2XY($lon, $lat);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
		
				$sql = "MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);
			}
			$select->order('b.is_top DESC');
			$select->order('b.featured DESC');
			$select->order('poll_result DESC');		
		
			$select->limit(BETTER_MAX_LIST_ITEMS);
			$rs = self::squery($select, $rdb);
			
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$has_featured += $row['featured'];
				// 排序key：
				// 附近贴士：是否置顶, 是否优质, 时间, bid
				// 人气贴士: 是否置顶, 是否优质, [是否在一周内, 投票数+评论数], 时间, bid
				$key = strval($row['is_top'] ? $row['is_top'] : 0) . strval($row['featured'] ? $row['featured'] : 0);
				if ($poiId > 0) { // 单POI, 人气贴士
					if ($order == 'poll') { // 按投票数排
						$iow = ($now - $row['dateline'] <= 7*24*3600) ? 1 : 0; // 是否在一周内?
						//投票数 跟 评论数效果一致
						$key .= '.' . strval($iow) . strval(10000000+$row['poll_result']+$row['comments']);
					} 

				}
				$key .= '.' . $row['dateline'] . '.' . $row['bid'];
				$r1[$key] = $row;
				if ($row['is_top'] == 1 || $row['featured'] == 1) $r2[$key] = $row; 
			}
		}
		// 如果附近贴士并且有优质并且配置为不显示所有，则仅显示置顶和优质贴士
		if ($poiId <= 0 && $has_featured > 0 && $show_all == 0) {
			$rr = &$r2;
		} else {
			$rr = &$r1;
		}
		
		krsort($rr, SORT_STRING);
		
		if (count($rr) > BETTER_MAX_LIST_ITEMS) {
			$tmp = array_chunk($rr, BETTER_MAX_LIST_ITEMS);
			$rr = $tmp[0];
			unset($tmp);
		}
		return $rr;
	}

	/**
	 * 好友发布的贴士
	 * 
	 * @return array
	 */
	public function friendsTips($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$results = array();
		$uid = $this->identifier;
		
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from($this->tbl.' AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down', new Zend_Db_Expr('(b.up-b.down) AS poll_result')
				));
			$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));			
			$select->join(BETTER_DB_TBL_PREFIX.'friends AS fr', 'fr.uid=b.uid AND fr.friend_uid='.$this->identifier, array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid', array());
			
			$select->where('b.type=?', 'tips');
			$select->where('b.checked=?', 1);
			$select->where('ub.uid is null');
			$select->where('fr.friend_uid=?', $this->identifier);
	
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$key = 1000000+$row['poll_result'].'.';
				$key = '';
				$results[$key.$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}		

		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;				
	}
	
	
	/**
	 * 关注者发布的贴士
	 * 
	 * @return array
	 */
	public function followingsTips($page=1, $pageSize=BETTER_PAGE_SIZE, $lon=0, $lat=0, $range=5000)
	{
		$results = array();
		/*$uid = $this->identifier;
		$isValidLL = Better_LL::isValidLL($lon, $lat);
		
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$selected =  array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down'
				);
			if ($isValidLL) {
				list($x, $y) = Better_Functions::LL2XY($lon, $lat);
				$range || $range = 5000;
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;				
				$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(b.xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
				
				$sql = "MBRWithin(b.xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);				
			}
			$select->from($this->tbl.' AS b',$selected);
			$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));			
			$select->join(BETTER_DB_TBL_PREFIX.'follower AS fr', 'fr.uid=b.uid AND fr.follower_uid='.$this->identifier, array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->where('b.type=?', 'tips');
			$select->where('b.checked=?', 1);		
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				if ($isValidLL) {
					$results[$row['distance'].'.'.$row['bid']] = $row;	
				} else {
					$results[$key.$row['dateline'].'.'.$row['bid']] = $row;
				}
			}			
		}		

		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}*/

		return $results;				
	}
	
	
	public function tips($page=1, $pageSize=BETTER_PAGE_SIZE, $order='poll_result')
	{
		$results = array();
		$uid = $this->identifier;
		
		$followingUids = Better_User::getInstance($uid)->follow()->getFollowings();
		$followingUids[] = $uid;
		$servers = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($followingUids);

		$select = $this->rdb->select();
		$select->from($this->tbl.' AS b', array(
			'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
			'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
			'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down', new Zend_Db_Expr('(b.up-b.down) AS poll_result')
			));
		$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
			'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
			'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
			));
		$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
			'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
			));		
		$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());	

		$select->where('b.uid=?', $uid);
		$select->where('b.type=?', 'tips');
		$select->where('b.checked=?', 1);
		
		$select->order('b.dateline DESC');
		$select->limit(BETTER_MAX_LIST_ITEMS);

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
				$key = $order=='poll_result' ? (1000000+$row['poll_result']).'.' : '';
				$key .= $row['dateline'].'.'.$row['bid'];
				$results[$key] = $row;
		}

		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;		
	}
	
	/**
	 * 改造后的“随便看看�?
	 * 
	 * @return array
	 */
	/*public function publicAll(array $params=array())
	{
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$range = $lon = $lat = 0;
		$sessUid = Better_Registry::get('sess')->getUid();
		$ts = array();
		$max = $page<3 ? BETTER_MAX_LIST_ITEMS_START : BETTER_MAX_LIST_ITEMS;		

		$results = array();
		$uid = (int)$this->identifier;
		$followerTbl = BETTER_DB_TBL_PREFIX.'follower';
		$friendTbl = BETTER_DB_TBL_PREFIX.'friends';
		$blockTbl = BETTER_DB_TBL_PREFIX.'blockedby';

		$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.ip, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
		FROM ".BETTER_DB_TBL_PREFIX."blog_lastestmajor AS l
			INNER JOIN ".BETTER_DB_TBL_PREFIX."blog AS b
				ON b.bid=l.bid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
				ON ub.uid=b.uid AND ub.uid IS NULL		
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
				ON b.uid=fe.uid AND fe.follower_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
				ON b.uid=at.uid AND at.file_id=b.attach	
		";

		$sql1 = " b.uid='".$sessUid."' ";
		$sql2 = " b.checked=1 AND b.priv!='private' ";
		
		$sql .= " WHERE 1";
		
		$sql2 .= " 
			AND (
					(
						(p.priv_blog+p.sys_priv_blog)=0
						AND
						b.priv='public'
					)
					OR
					(
						f.friend_uid IS NOT NULL
						AND b.priv!='private'
					)
				)
		";				

		$sql .= " AND
		(
			(".$sql1.")
			OR
			(".$sql2.")
		)
		".$specSql."
		ORDER BY l.dateline DESC
		LIMIT ".$max."
		";

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;			
	}*/

	/**
	 * 新的方法封装
	 * 
	 * @param array $params
	 * @return array
	 */
	/*public function fuckingAll(array $params)
	{
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$keyword = isset($params['keyword']) ? trim(urldecode($params['keyword'])) : '';
	}*/
	
	/**
	 * 获取某个人的动�?
	 * @param array $params
	 */
	/*public function getSomebody(array $params)
	{
		$specSql = '';
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
		$poi = isset($params['poi']) ? (array)$params['poi'] : array();
		$type = isset($params['type']) ? $params['type'] : '';
		$withoutMe = isset($params['without_me']) ? $params['without_me'] : false;
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$ignoreBlock = isset($params['ignore_block']) ? $params['ignore_block'] : false;
		$bids = isset($params['bids']) ? (array)$params['bids'] : array();
		$forceUidsCheck = isset($params['force_uids_check']) ? $params['force_uids_check'] : false;
		$onlyMajor = isset($params['only_major']) ? (bool)$params['only_major'] : false;
		$range = $lon = $lat = 0;
		$sessUid = Better_Registry::get('sess')->getUid();
		$ts = array();
		$max = $page==1 ? 30 : BETTER_MAX_LIST_ITEMS;
		
		if (isset($params['lon']) && isset($params['lat'])) {
			$lon = (float)$params['lon'];
			$lat = (float)$params['lat'];
			
			$range = isset($params['range']) ? (int)$params['range'] : 5000;
		}

		$results = array();
		$uid = (int)$this->identifier;

		$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
		FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
				ON b.uid=fe.uid AND fe.follower_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$uid."			
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
				ON b.uid=at.uid AND at.file_id=b.attach	
		";

		$sql1 = " 1 ";
		$sql2 = " b.checked=1 AND b.priv!='private' ";
		$sql3 = " 1 ";
		
		if (!$ignoreBlock) {
			$sql .= "
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			";
			
			$sql2 .= " AND bl.uid IS NULL";
		}
		
		$duid = (is_array($uids) && $uids[0]) ? $uids[0] : $uids;
		if (count($uids) && $uids[0]) {
			if (count($uids)==1) {
				$sql2 .= " AND b.uid=".$uids[0];
				
				if (!$ignoreBlock && $uid!=$uids[0]) {
					$sql .= " 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid=".$uids[0]."
					";
					$sql2 .= " AND bl2.uid IS NULL";
				}
			} else {
				$sql2 .= " AND b.uid IN ('".implode("','", $uids)."')";
				
				if (!$ignoreBlock) {
					$sql .= "
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2 
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid IN ('".implode("','", $uids)."')
					";
					
					$sql2 .= " AND bl2.uid IS NULL";
				}
			}
		}
		
		$sql .= " WHERE 1";
		
		if (is_array($type)) {
			if (count($type)<3) {
				$sql3 .= " AND b.`type` IN ('".implode("','", $type)."')";
			}
			$ts = $type;
		} else if ($type && $type!='all') {
			$sql3 .= " AND b.`type`='".$type."'";
			$ts[] = $type;
		} else {
			$sql3 .= " AND b.`type` IN ('normal', 'checkin')";
			$ts[] = 'normal';
			$ts[] = 'checkin';
		}
		
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql3 .= " AND MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";		
		}
		
		$part1 = ' AND b.uid='.$duid;
		$sql1 .= $part1;

		if (!in_array('tips', $ts)) {
			$sql2 .= " 
				AND
				(
					(		
						f.friend_uid IS NOT NULL
						AND 
						(
							b.`priv`='protected'
							OR
							(
								(
									p.priv_blog=1
									OR
									p.sys_priv_blog=1
								)
								AND
								b.`priv`='public'
								AND
								fe.follower_uid IS NOT NULL
							)
						)
					)
					
					OR
					(
						b.priv='public'
						AND
						p.priv_blog=0
						AND 
						p.sys_priv_blog=0
					)
					
					OR
					(
						fe.follower_uid IS NOT NULL
						AND 
						(
							p.priv_blog=1
							OR
							p.sys_priv_blog=1
						)
						AND
						b.priv='public'
					)
				)
			";
		} else {
			$sql2 .= " 
			AND 
			(
				b.`type`='tips'
				OR
				(
					b.`type`!='tips'
					AND
					(
						f.friend_uid IS NOT NULL
						AND
						(
							b.priv='protected'
							OR
							(
								(
									p.priv_blog=1
									OR
									p.sys_priv_blog=1
								)
								AND
								b.priv='public'
								AND
								fe.follower_uid IS NOT NULL
							)
						)
					)
					OR
					(
						b.priv='public'
						AND
						p.priv_blog=0
						AND
						p.sys_priv_blog=0
					)
					OR
					(
						fe.follower_uid IS NOT NULL
						AND
						(
							p.priv_blog=1
							OR
							p.sys_priv_blog=1
						)
						AND
						b.priv='public'
					)
				)
			)
			";				
		}
		
		if ($onlyMajor===true) {
			$specSql .= " AND b.major='1'";
		}

		$sql .= " AND
		(
			".$sql3."
			AND
			(
				(".$sql1.")
				OR
				(".$sql2.")
			)
		)
		".$specSql."
		ORDER BY b.dateline DESC
		LIMIT ".$max."
		";

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;			
	}*/

	/**
	 * api中的好友动�?
	 * @param array $params
	 */
	/*public function publicTimeLine(array $params=array())
	{
		$specSql = '';
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$type = isset($params['type']) ? $params['type'] : '';
		$withoutMe = isset($params['without_me']) ? $params['without_me'] : false;
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$range = $lon = $lat = 0;
		$sessUid = Better_Registry::get('sess')->getUid();
		$ts = array();
		$max = $page==1 ? 30 : BETTER_MAX_LIST_ITEMS;
		
		if (isset($params['lon']) && isset($params['lat'])) {
			$lon = (float)$params['lon'];
			$lat = (float)$params['lat'];
			
			$range = isset($params['range']) ? (int)$params['range'] : 5000;
		}

		$results = array();
		$uid = (int)$this->identifier;
		$followings = 0;
		
		if ($uid) {
			$followings = count((array)Better_User::getInstance($uid)->followings);
		}

		if ($followings>15) {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
					ON b.uid=fe.uid AND fe.follower_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid."			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid AND ub.uid IS NULL
			";
		} else {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM (
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
					JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe ON b.uid=fe.uid
					WHERE fe.follower_uid=".$uid."
				) AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
					ON b.uid=fe.uid AND fe.follower_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid."			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON at.file_id=b.attach AND at.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid AND ub.uid IS NULL
			";			
		}

		$sql1 = " 1 ";
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql3 = " 1 ";
		
		$sql .= "
		LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
			ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
		";
		
		$sql2 .= " AND bl.uid IS NULL";
		
		if (count($uids) && $uids[0]) {
			if (count($uids)==1) {
				$sql2 .= " AND b.uid=".$uids[0];
				
				if (!$ignoreBlock && $uid!=$uids[0]) {
					$sql .= " 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid=".$uids[0]."
					";
					$sql2 .= " AND bl2.uid IS NULL";
				}
			} else {
				$sql2 .= " AND b.uid IN ('".implode("','", $uids)."')";
				
				if (!$ignoreBlock) {
					$sql .= "
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2 
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid IN ('".implode("','", $uids)."')
					";
					
					$sql2 .= " AND bl2.uid IS NULL";
				}
			}
		}
		
		$sql .= " WHERE 1";

		$sql3 .= " AND b.`type` IN ('normal', 'checkin')";
		$ts[] = 'normal';
		$ts[] = 'checkin';
		
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql3 .= " AND MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";		
		}

		$sql1 .= " AND b.uid=".$uid." AND b.`type` IN ('normal', 'checkin')";

		$sql2 .= " 
			AND
			(
				(		
					f.friend_uid IS NOT NULL
					AND 
					(
						b.`priv`='protected'
						OR
						(
							(
								p.priv_blog=1
								OR
								p.sys_priv_blog=1
							)
							AND
							b.`priv`='public'
							AND
							fe.follower_uid IS NOT NULL
						)
					)
				)
				
				OR
				(
					b.priv='public'
					AND
					p.priv_blog=0
					AND 
					p.sys_priv_blog=0
				)
				
				OR
				(
					fe.follower_uid IS NOT NULL
					AND 
					(
						p.priv_blog=1
						OR
						p.sys_priv_blog=1
					)
					AND
					b.priv='public'
				)
			)
		";
		
		$sql .= " AND
		(
			".$sql3."
			AND
			(
				(".$sql1.")
				OR
				(".$sql2.")
			)
		)
		".$specSql."
		ORDER BY b.dateline DESC
		LIMIT ".$max."
		";

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;	
	}	*/
		
	
	/**
	 * 取所有微�?
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */	
	/*public function all(array $params=array())
	{
		$specSql = '';
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
		$poi = isset($params['poi']) ? (array)$params['poi'] : array();
		$type = isset($params['type']) ? $params['type'] : '';
		$withoutMe = isset($params['without_me']) ? $params['without_me'] : false;
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$ignoreBlock = isset($params['ignore_block']) ? $params['ignore_block'] : false;
		$bids = isset($params['bids']) ? (array)$params['bids'] : array();
		$forceUidsCheck = isset($params['force_uids_check']) ? $params['force_uids_check'] : false;
		$karmaLimit = isset($params['karma_limit']) ? (int)$params['karma_limit'] : -9999;
		$isFollowing = isset($params['is_following']) ? (bool)$params['is_following'] : false;
		$onlyMajor = isset($params['only_major']) ? (bool)$params['only_major'] : false;
		$hasAvatar = isset($params['has_avatar']) ? (bool)$params['has_avatar'] : null;
		$hasAttach = isset($params['has_attach']) ? (bool)$params['has_attach'] : null;
		$range = $lon = $lat = 0;
		$sessUid = Better_Registry::get('sess')->getUid();
		$ts = array();
		$max = $page<3 ? BETTER_MAX_LIST_ITEMS_START : BETTER_MAX_LIST_ITEMS;
		$max = $page*$pageSize+1;
		
		if (isset($params['lon']) && isset($params['lat'])) {
			$lon = (float)$params['lon'];
			$lat = (float)$params['lat'];
			
			$range = isset($params['range']) ? (int)$params['range'] : 5000;
		}

		$results = array();
		$uid = (int)$this->identifier;
		$followerTbl = BETTER_DB_TBL_PREFIX.'follower';
		$friendTbl = BETTER_DB_TBL_PREFIX.'friends';
		$blockTbl = BETTER_DB_TBL_PREFIX.'blockedby';
		$followings = 0;
		$vUid = Better_Config::getAppConfig()->user->virtual_user_id;
		
		if ($uid) {
			$followings = count((array)Better_User::getInstance($uid)->followings);
		} else {
			$uid = $vUid;
		}

		if ($followings>15 || !$uid || $uid==$vUid) {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
					ON b.uid=fe.uid AND fe.follower_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid."			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid AND ub.uid IS NULL
			";
		} else {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM (
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
					JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe ON b.uid=fe.uid
					WHERE fe.follower_uid=".$uid."
				) AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
					ON b.uid=fe.uid AND fe.follower_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid."			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON at.file_id=b.attach AND at.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid AND ub.uid IS NULL
			";			
		}

		$sql1 = " 1 ";
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql3 = " 1 ";
		
		if (!$ignoreBlock) {
			$sql .= "
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			";
			
			$sql2 .= " AND bl.uid IS NULL";
		}
		
		if ($isFollowing) {
			$sql3 .= "
				AND (fe.uid IS NOT NULL OR (fe.uid IS NULL AND b.uid=".$uid."))
			";				
		}
		
		if (count($uids) && $uids[0]) {
			if (count($uids)==1) {
				$sql2 .= " AND b.uid=".$uids[0];
				
				if (!$ignoreBlock && $uid!=$uids[0]) {
					$sql .= " 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid=".$uids[0]."
					";
					$sql2 .= " AND bl2.uid IS NULL";
				}
			} else {
				$sql2 .= " AND b.uid IN ('".implode("','", $uids)."')";
				
				if (!$ignoreBlock) {
					$sql .= "
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl2 
						ON bl2.uid=".$uid." AND bl2.blocked_by_uid IN ('".implode("','", $uids)."')
					";
					
					$sql2 .= " AND bl2.uid IS NULL";
				}
			}
		}
		
		$sql .= " WHERE 1";
		
		if (count($bids)>0) {
			$sql .= " AND b.bid IN ('".implode("','", $bids)."')";
		}
		
		if (is_array($type)) {
			if (count($type)<3) {
				$sql3 .= " AND b.`type` IN ('".implode("','", $type)."')";
			}
			$ts = $type;
		} else if ($type && $type!='all') {
			$sql3 .= " AND b.`type`='".$type."'";
			$ts[] = $type;
		} else {
			$sql3 .= " AND b.`type` IN ('normal', 'checkin')";
			$ts[] = 'normal';
			$ts[] = 'checkin';
		}

		if (count($poi) && $poi[0]) {
			$sql3 .= " AND b.poi_id IN ('".implode("','", $poi)."')";
		}
		
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql3 .= " AND MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";		
		}
		
		if (strlen($keyword)) {
			$sql3 .= " AND b.message LIKE '%".addslashes($keyword)."%'";
		}
		
		if ($onlyAvatar===true) {
			$sql3 .= " AND p.avatar!=''";
		}
		
		if ($hasAttach===true) {
			$sql3 .= " AND b.attach!=''";
		} else if ($hasAttach===false) {
			$sql3 .= " AND b.attach=''";
		}

		$part1 = ' AND b.uid='.$uid;
		if ($withoutMe) {
			$part1 .= ' AND b.uid!='.$uid;
		} else {
			$part1 .= in_array('tips', $ts) ? (count(ts)<3 ? " AND b.`type` IN ('".implode("','", $ts)."')" : '') : " AND b.`type` IN ('normal', 'checkin')";
		}
		$sql1 .= $part1;

		if (!in_array('tips', $ts)) {
			$sql2 .= " 
				AND
				(
					(		
						f.friend_uid IS NOT NULL
						AND 
						(
							b.`priv`='protected'
							OR
							(
								(
									p.priv_blog=1
									OR
									p.sys_priv_blog=1
								)
								AND
								b.`priv`='public'
								AND
								fe.follower_uid IS NOT NULL
							)
						)
					)
					
					OR
					(
						b.priv='public'
						AND
						p.priv_blog=0
						AND 
						p.sys_priv_blog=0
					)
					
					OR
					(
						fe.follower_uid IS NOT NULL
						AND 
						(
							p.priv_blog=1
							OR
							p.sys_priv_blog=1
						)
						AND
						b.priv='public'
					)
				)
			";
		} else {
			$sql2 .= " 
			AND 
			(
				b.`type`='tips'
				OR
				(
					b.`type`!='tips'
					AND
					(
						f.friend_uid IS NOT NULL
						AND
						(
							b.priv='protected'
							OR
							(
								(
									p.priv_blog=1
									OR
									p.sys_priv_blog=1
								)
								AND
								b.priv='public'
								AND
								fe.follower_uid IS NOT NULL
							)
						)
					)
					OR
					(
						b.priv='public'
						AND
						p.priv_blog=0
						AND
						p.sys_priv_blog=0
					)
					OR
					(
						fe.follower_uid IS NOT NULL
						AND
						(
							p.priv_blog=1
							OR
							p.sys_priv_blog=1
						)
						AND
						b.priv='public'
					)
				)
			)
			";				
		}
		
		if ($onlyMajor===true) {
			$specSql .= " AND b.major='1'";
		}
		
		if (count($bids)>0 & $bids[0]) {
			$specSql .= " AND b.bid IN ('".implode("','", $bids)."')";
		}

		$sql .= " AND
		(
			".$sql3."
			AND
			(
				(".$sql1.")
				OR
				(".$sql2.")
			)
		)
		".$specSql."
		ORDER BY b.dateline DESC
		LIMIT ".$max."
		";

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;	
	}*/
	
	
	
	/**
	 * 取当前用户好友的微博
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */	
	/*public function friends($page=1, $count=BETTER_PAGE_SIZE, $size=0)
	{
		$results = array();
		$uid = $this->identifier;
		$followerTbl = BETTER_DB_TBL_PREFIX.'follower';
		$friendTbl = BETTER_DB_TBL_PREFIX.'friends';
		
		$friends = Better_User::getInstance($uid)->friends()->getFriends();
		$servers = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($friends);

		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range, b.type, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,
				p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog, p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_x, Y(p.xy) AS user_y, p.lbs_report,
				at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
				FROM ".$this->tbl." AS b USE KEY(dateline)
					INNER JOIN ".$this->profileTbl." AS p
						ON p.uid=b.uid
					LEFT JOIN ".$followerTbl." AS fe
						ON b.uid=fe.uid AND fe.follower_uid=".$uid."
					LEFT JOIN ".$friendTbl." as f
						ON b.uid=f.uid AND f.friend_uid=".$uid."
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
						ON b.uid=at.uid AND at.file_id=b.attach
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
						ON ub.uid=b.uid AND ub.uid IS NULL
				WHERE b.uid=".$uid." 
					OR (b.checked=1
						AND (b.type IN ('normal', 'checkin'))
						AND fe.follower_uid IS NOT NULL AND f.friend_uid IS NOT NULL
						AND (
								(
									f.friend_uid IS NOT NULL
									AND
									(
										b.priv='protected'
										OR
										(
											(
												p.priv_blog=1
												OR
												p.sys_priv_blog=1
											)
											AND
											b.priv='public'
										)
									)
								)
								OR
								(
									b.priv='public'
								)
								OR
								(
									(
										p.priv_blog=1
										OR 
										p.sys_priv_blog=1
									)
									AND
									b.priv='protected'
								)
						)
					)
			";

			$sql .= " ORDER BY b.dateline DESC";
			if($size){
				$sql .= " LIMIT ".$size;
			}else{
				$sql .= " LIMIT ".BETTER_MAX_LIST_ITEMS;
			}
			

			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}
		}

		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;		
	}*/
	
	/**
	 * 取当前用户关注者的微博
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function followings($page=1, $count=BETTER_PAGE_SIZE)
	{
		$results = array();
		/*$uid = $this->identifier;
		$followerTbl = BETTER_DB_TBL_PREFIX.'follower';
		$friendTbl = BETTER_DB_TBL_PREFIX.'friends';
		
		$followingUids = Better_User::getInstance($uid)->follow()->getFollowings();
		$followingUids[] = $uid;
		$servers = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($followingUids);

		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			//own+chk*!ban*(nor+isgeo)*isfer*(isfrn*(lim+lck*pub)+pub+lck*lim)	//化简A
			$select = $rdb->select();
			$select->from($this->tbl.' AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down'
				));
			$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->joinleft($followerTbl.' AS fe', $rdb->quoteInto('b.uid=fe.uid AND fe.follower_uid=?', $uid), array());
			$select->joinleft($friendTbl.' AS f', $rdb->quoteInto('b.uid=f.uid AND f.friend_uid=?', $uid), array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));			
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());

			$part1 = $rdb->quoteInto('b.uid=?', $uid);
			$part1 .= ' AND '.$rdb->quoteInto('b.type IN (?)', array('normal', 'checkin'));
			$select->where($part1);
			
			$where = $rdb->quoteInto('b.checked=?', 1);
			$where .= ' AND '.$rdb->quoteInto('b.uid!=?', $uid);
			$where .= ' AND '.$rdb->quoteInto('b.type IN (?)', array('normal', 'checkin'));
			$where .= ' AND fe.follower_uid IS NOT NULL';
			$where .= ' AND (
						(
							f.friend_uid IS NOT NULL
							AND
							(
								'.$rdb->quoteInto('b.priv=?', 'protected').'
								OR
								(
									(
										'.$rdb->quoteInto('p.priv_blog', 1).'
										OR
										'.$rdb->quoteInto('p.sys_priv_blog', 1).'									
									)
									AND
									'.$rdb->quoteInto('b.priv=?', 'public').'
								)
							)
						)
					OR
						('.$rdb->quoteInto('b.priv=?', 'public').')
					OR
						(
							(
								'.$rdb->quoteInto('p.priv_blog', 1).'
								OR
								'.$rdb->quoteInto('p.sys_priv_blog', 1).'
							)
							AND
							'.$rdb->quoteInto('b.priv=?', 'protected').'
						)
				)';
			
			$select->orWhere($where);
			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}
		}

		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}*/

		return $results;
	}
	
/**
	 * 取所有我的被转发的微�?
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */	
	/*public function rtblogs(array $params=array())
	{
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
		$poi = isset($params['poi']) ? (array)$params['poi'] : array();
		$type = 'normal';
		$withoutMe = isset($params['without_me']) ? $params['without_me'] : false;
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$ignoreBlock = isset($params['ignore_block']) ? $params['ignore_block'] : false;
		$bids = isset($params['bids']) ? (array)$params['bids'] : array();
		$forceUidsCheck = isset($params['force_uids_check']) ? $params['force_uids_check'] : false;
		$karmaLimit = isset($params['karma_limit']) ? (int)$params['karma_limit'] : -9999;
		$range = $lon = $lat = 0;
		$sessUid = Better_Registry::get('sess')->getUid();
		$ts = array();
		
		if (isset($params['lon']) && isset($params['lat'])) {
			$lon = (float)$params['lon'];
			$lat = (float)$params['lat'];
			
			$range = isset($params['range']) ? (int)$params['range'] : 5000;
		}

		$results = array();
		$uid = $this->identifier;
		$followerTbl = BETTER_DB_TBL_PREFIX.'follower';
		$friendTbl = BETTER_DB_TBL_PREFIX.'friends';
		$blockTbl = BETTER_DB_TBL_PREFIX.'blockedby';
		
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			//own + chk * !ban * !isblk * (nor + geo) * (isfrn*(lim + lck*pub) + pub + lck*lim)
			$select = $rdb->select();
			$select->from($this->tbl.' AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.type'
				));
			$select->join($this->profileTbl.' AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->joinleft($followerTbl.' AS fe', $rdb->quoteInto('b.uid=fe.uid AND fe.follower_uid=?', $uid), array());
			$select->joinleft($friendTbl.' AS f', $rdb->quoteInto('b.uid=f.uid AND f.friend_uid=?', $uid), array());
			!$ignoreBlock && $select->joinleft($blockTbl.' AS bl', $rdb->quoteInto('b.uid=bl.uid AND bl.blocked_by_uid=?', $uid), array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));	
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
				
				
			if ($karmaLimit!=-9999) {
				$kWhere = '
				(
					('.$this->rdb->quoteInto('p.uid=?', $sessUid).') 
					OR 
					('.$this->rdb->quoteInto('p.avatar!=?', '').')
					OR
					(
						'.$this->rdb->quoteInto('p.avatar=?', '').' 
						AND 
						(
							'.$this->rdb->quoteInto('p.karma>?', $karmaLimit).'
							OR
							(
								'.$this->rdb->quoteInto('p.karma<=?', $karmaLimit).'
								AND
								('.$this->rdb->quoteInto('b.badge_id>?', 0).' OR '.$this->rdb->quoteInto('b.major>?', 0).')
							)
						)
					)
				)
				';	
				$select->where($kWhere);
			}
			$mustsql = 'b.type='.$type.' AND b.upbid LIKE \''.$sessUid.'.%\' AND b.checked=1 AND b.priv!=\'private\'';
				
			//=========	START OR CONDITION

			$where = $rdb->quoteInto('b.type=?',$type);	
			$where .= " AND b.upbid LIKE '".$sessUid.".%' ";	
			$where .= ' AND '.$rdb->quoteInto('b.priv!=?', 'private');
			$where .= ' AND '.$rdb->quoteInto('b.checked=?', 1);
			
			if (count($uids) && $uids[0]) {
				if (count($uids)==1) {
					$where .= ' AND '.$rdb->quoteInto('b.uid=?', $uids[0]);
					if (!$ignoreBlock) {
						$select->joinleft($blockTbl.' AS bl2', $rdb->quoteInto('bl2.uid='.$uid.' AND bl2.blocked_by_uid=?', $uids[0]), array());
						$where .= ' AND bl2.uid IS NULL';
					}
				} else {
					$where .= ' AND '.$rdb->quoteInto('b.uid IN (?)', $uids);
					
					if (!$ignoreBlock) {
						$select->joinleft($blockTbl.' AS bl2', $rdb->quoteInto('bl2.uid='.$uid.' AND bl2.blocked_by_uid IN (?)', $uids), array());
						$where .= ' AND bl2.uid IS NULL';
					}
				}
			}

			if (!in_array('tips', $ts)) {
				$where .= ' AND (
					(		
						f.friend_uid IS NOT NULL
						AND 
						(
								'.$rdb->quoteInto('b.priv=?', 'protected').'
								OR
								(
									(
										'.$rdb->quoteInto('p.priv_blog', 1).'
										OR
										'.$rdb->quoteInto('p.sys_priv_blog', 1).'									
									)
									AND
									'.$rdb->quoteInto('b.priv=?', 'public').'
									AND
									fe.follower_uid IS NOT NULL
								)
						)
					)
					OR
					(
						 '.$rdb->quoteInto('b.priv=?', 'public').'
						 AND
						 '.$rdb->quoteInto('p.priv_blog=?', '0').'
						 AND
						 '.$rdb->quoteInto('p.sys_priv_blog=?', '0').'
					)
					OR
					(
						fe.follower_uid IS NOT NULL
						AND 
						(
							'.$rdb->quoteInto('p.priv_blog', 1).'
							OR
							'.$rdb->quoteInto('p.sys_priv_blog', 1).'
						)
						AND
						'.$rdb->quoteInto('b.priv=?', 'public').'
					)
				)';
			} else {
				$where .= ' AND (
				'.$rdb->quoteInto('b.type=?', 'tips').' OR ('.$rdb->quoteInto('b.type!=?', 'tips').' AND
					(		
						f.friend_uid IS NOT NULL
						AND 
						(
								'.$rdb->quoteInto('b.priv=?', 'protected').'
								OR
								(
									(
										'.$rdb->quoteInto('p.priv_blog', 1).'
										OR
										'.$rdb->quoteInto('p.sys_priv_blog', 1).'									
									)
									AND
									'.$rdb->quoteInto('b.priv=?', 'public').'
									AND
									fe.follower_uid IS NOT NULL
								)
						)
					)
					OR
					(
						 '.$rdb->quoteInto('b.priv=?', 'public').'
						 AND
						 '.$rdb->quoteInto('p.priv_blog=?', '0').'
						 AND
						 '.$rdb->quoteInto('p.sys_priv_blog=?', '0').'
					)
					OR
					(
						fe.follower_uid IS NOT NULL
						AND 
						(
							'.$rdb->quoteInto('p.priv_blog', 1).'
							OR
							'.$rdb->quoteInto('p.sys_priv_blog', 1).'
						)
						AND
						'.$rdb->quoteInto('b.priv=?', 'public').'
					))
				)';				
			}

			
			if ($lon && $lat) {
				list($x, $y) = Better_Functions::LL2XY($lon, $lat);
				
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
	
				$sql = "MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$where .= ' AND '.$sql;
			}					
			
			if ($karmaLimit!=-9999) {	
				$where .= '
				AND 
				(
					('.$this->rdb->quoteInto('p.uid=?', $sessUid).') 
					OR 
					('.$this->rdb->quoteInto('p.avatar!=?', '').')
					OR
					(
						'.$this->rdb->quoteInto('p.avatar=?', '').' 
						AND 
						(
							'.$this->rdb->quoteInto('p.karma>?', $karmaLimit).'
							OR
							(
								'.$this->rdb->quoteInto('p.karma<=?', $karmaLimit).'
								AND
								('.$this->rdb->quoteInto('b.badge_id>?', 0).' OR '.$this->rdb->quoteInto('b.major>?', 0).')
							)
						)
					)
				)
				';					
			} 
			$select->orWhere($where);

			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);
			//die(nl2br($select));
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}

		return $results;	
	}*/
	
	
	/**
	 * 取所有签到过的POI（带隐私控制的）
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */	
	public function checkinedPois(array $params=array())
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$type = $params['type'] ? $params['type'] : 'checkin';
		$uid = (int)$params['uid'];
		$days = isset($params['days']) ? $params['days'] : 0 ;
		$reg_time = isset($params['reg_time'])? $params['reg_time']: 0;
		
		$thisUid = (int)$this->identifier;

		$sql = "SELECT b.poi_id, max(b.dateline) as checkin_time, count(b.poi_id) as checkin_count
		FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$thisUid."			
			WHERE b.uid='".$uid."' AND b.type='".$type."'
		";
		
		if ($uid!=$thisUid) {
			$sql .= " AND b.checked=1 AND b.priv!='private' AND p.state!='banned' and f.friend_uid=".$thisUid." ";	
		}
		
		if($days){
			$sql .=" AND b.dateline<=".($reg_time + $days*24*3600);
		}
		
		$sql .= "
		Group by b.poi_id
		ORDER BY b.dateline DESC
		";

		//die($sql);
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
					
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		if (count($rows)>0) {
			if (count($rows)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($rows, BETTER_MAX_LIST_ITEMS);
				$rows = $tmp[0];
				unset($tmp);
			}
		}

		return $rows;	
	}
	
	
	public function user_poi_done(array $params=array())
	{
		$specSql = '';	
		$poi = isset($params['poi']) ? (array)$params['poi'] : array();
		$type = isset($params['type']) ? $params['type'] : '';	
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$max = $params['page']<3 ? BETTER_MAX_LIST_ITEMS_START : BETTER_MAX_LIST_ITEMS; 
		$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report FROM ".BETTER_DB_TBL_PREFIX."blog AS b LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid where b.uid IN ('".implode("','", $uids)."') and b.poi_id IN ('".implode("','", $poi)."') ORDER BY b.dateline DESC LIMIT ".$max;

		$sid = Better_DAO_User_Assign::getInstance()->getServerIdByUid($uids[0]);
		$cs = parent::assignDbConnection('user_server_'.$sid);
		$rdb = &$cs['r'];
			
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		if (count($rows)>0) {
			if (count($rows)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($rows, BETTER_MAX_LIST_ITEMS);
				$rows = $tmp[0];
				unset($tmp);
			}
		}
		return $rows;
	}
}