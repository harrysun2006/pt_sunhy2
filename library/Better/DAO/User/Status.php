<?php

/**
 * 取用户相关微博的DAO
 * 
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_User_Status extends Better_DAO_Base
{
	private static $instance = array();

	private $profileTbl = '';
	private $attachTbl = '';

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
	

	/**
	 * 
	 * 网站“我关注的”
	 * @param array $params
	 * @return array
	 */
	/*public function &webFollowings(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		
		$max = $page*$pageSize+1;
		$followings = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$followings = count((array)Better_User::getInstance($this->identifier)->followings);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($followings>=$of) {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			";
		} else {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM (
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				 	WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
					INNER JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe ON b.uid=fe.uid
					WHERE fe.follower_uid=".$uid."
				) AS b
			";			
		}
		
		$sql .= "
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
			WHERE 1
		";
			
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql2 .= " AND bl.uid IS NULL";
		$sql2 .= " AND fe.follower_uid='".$uid."'";
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
					OR
					(
						b.type='tips'
					)
				)
		";
		
		if ($withoutKai) {
			$sql .= " AND b.uid!='10000' ";
		}
		
		$sql .= " AND
		(
			(
				b.uid=".$uid." 
			)
			OR
			(".$sql2.")
		)
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
	 * 新版网站‘好友动态’
	 * @param array $params
	 * @return array
	 */
	public function &webFollowings(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		
		$max = $page*$pageSize+1;
		$friends = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$friends = count((array)Better_User::getInstance($this->identifier)->friends);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($friends>=$of) {
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.type, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			";
		} else {
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.type, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM (
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				 	WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				) AS b
			";			
		}
		
		$sql .= "
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid." AND f.home_show=1
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
			WHERE 1
		";
			
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql2 .= " AND bl.uid IS NULL";
		$sql2 .= " AND f.friend_uid='".$uid."' AND f.home_show=1";
		$sql2 .= " and ub.uid is null";
		
		if ($withoutKai) {
			$sql .= " AND b.uid!='10000' ";
		}
		
		$sql .= " AND
		(
			(
				b.uid=".$uid." 
			)
			OR
			(".$sql2.")
		)
		ORDER BY b.dateline DESC
		LIMIT ".$max."
		";
//die($sql);
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
	}

	/**
	 * 
	 * 网站“我关注的”
	 * @param array $params
	 * @return array
	 */
	/*public function &tinyWebFollowings(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$dateline = isset($params['dateline']) ? $params['dateline'] : 0;
		$withSelf = isset($params['with_self']) ? (bool)$params['with_self'] : false;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		
		$max = $page*$pageSize+1;
		$followings = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$followings = count((array)Better_User::getInstance($this->identifier)->followings);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($followings>=$of) {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.dateline
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b USE KEY(dateline)
			";
		} else {
			$sql = "SELECT fe.uid AS feuid, b.bid,b.dateline
			FROM (
				SELECT b.bid, b.dateline,b.checked, b.priv,b.uid,b.type FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				 	WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.dateline,b.checked, b.priv,b.uid,b.type FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				UNION 
				SELECT b.bid, b.dateline,b.checked, b.priv,b.uid,b.type FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
					INNER JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe ON b.uid=fe.uid
					WHERE fe.follower_uid=".$uid."
				) AS b
			";			
		}
		
		$sql .= "
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
			WHERE 
		";

		$sql .= " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " AND fe.follower_uid='".$uid."'";
		$sql .= " 
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
					OR
					(
						b.type='tips'
					)
				)
		";
		
		if ($withoutKai) {
			$sql .= " AND b.uid!='10000'";
		}
		
		if ($dateline) {
			$sql .= " AND b.dateline>".($dateline);
		}
		
		if ($withSelf) {
			$sql .= " OR b.uid='".$this->identifier."' ";
		}
		
		$sql .= "
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
	 * 
	 * 新版网站“好友动态”
	 * @param array $params
	 * @return array
	 */
	public function &tinyWebFollowings(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$dateline = isset($params['dateline']) ? $params['dateline'] : 0;
		$withSelf = isset($params['with_self']) ? (bool)$params['with_self'] : false;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		
		$max = $page*$pageSize+1;
		$friends = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$friends = count((array)Better_User::getInstance($this->identifier)->friends);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($friends>=$of) {
			$sql = "SELECT f.uid AS feuid, b.bid, b.dateline
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			";
		} else {
			$sql = "SELECT f.uid AS feuid, b.bid,b.dateline
			FROM (
				SELECT b.bid, b.dateline,b.checked, b.priv,b.uid,b.type FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				 	WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.dateline,b.checked, b.priv,b.uid,b.type FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				) AS b
			";			
		}
		
		$sql .= "
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid		
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid." AND f.home_show=1
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			WHERE 
		";

		$sql .= " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " AND f.friend_uid='".$uid."' AND f.home_show=1";
		$sql .= " and ub.uid is null";
		
		if ($withoutKai) {
			$sql .= " AND b.uid!='10000'";
		}
		
		if ($dateline) {
			$sql .= " AND b.dateline>".($dateline);
		}
		
		if ($withSelf) {
			$sql .= " OR b.uid='".$uid."' ";
		}
		
		$sql .= "
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
	}
	
	
	
	/**
	 * 转发我的
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &rtMine(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$friends = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$friends = count((array)Better_User::getInstance($this->identifier)->friends);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		
		if ($friends>=$of) {
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			";
		} else {
			$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM (
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b WHERE b.uid=".$uid."
				UNION 
				SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down,b.xy FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid
					WHERE f.friend_uid=".$uid."
				) AS b
			";			
		}
		
		$sql .= "
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
			WHERE 
		";
			
		$sql .= " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND b.upbid LIKE '".$uid.".%' ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " and ub.uid is null";
		
		$sql .= " 
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
	}
	
	
	/**
	 * 简单的转发我的
	 * 
	 * @param array $params
	 * @return array
	 */
	public function simpleRtMine(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$uid = (int)$this->identifier;
		
		
		$sql = "SELECT b.bid, b.dateline FROM ".BETTER_DB_TBL_PREFIX."blog AS b
		";
		
		$sql .= "
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			WHERE 
		";
			
		$sql .= " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND b.upbid LIKE '".$uid.".%' ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " and ub.uid is null";
		
		$sql .= " 
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
	}
	
	/**
	 * 
	 * 某人周围的
	 * @param array $params
	 * @return array
	 */
	/*public function &aroundSomebody(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$uid = (int)$this->identifier;
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		$range = 50000;
		$x1 = $x-$range/2;
		$y1 = $y+$range/2;
		$x2 = $x+$range/2;
		$y2 = $y-$range/2;
		
		$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
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
			WHERE MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";		
	
					
		$sql .= " AND b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " 
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
					OR
					(
						b.type='tips'
					)
				)
		";
		
		$sql .= "
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
	 * 
	 * 新版某人周围的
	 * @param array $params
	 * @return array
	 */
	public function &aroundSomebody(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$uid = (int)$this->identifier;
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		$range = 50000;
		$x1 = $x-$range/2;
		$y1 = $y+$range/2;
		$x2 = $x+$range/2;
		$y2 = $y-$range/2;
		
		$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid				
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
			WHERE MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";		
	
					
		$sql .= " AND b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql .= " AND bl.uid IS NULL";
		$sql .= " AND ub.uid IS NULL";
		
		$sql .= "
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
	}

	/**
	 * 附近的好友签到
	 * page: 第几页, 缺省1
	 * page_size: 页大小, 缺省1
	 * range: 范围, 缺省500
	 * checkin_time: 签到时间
	 * ahead: 之前多少秒, 缺省3600
	 * lon: 经度, 必须
	 * lat: 纬度, 必须
	 * @param array $params
	 * @return array
	 */
	public function &friendBlogAroundMe(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page <= 0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize <= 0 && $pageSize = BETTER_PAGE_SIZE;
		$range = $params['range'] ? (int)$params['range'] : 707;
		$range <= 0 && $range = 707;
		$checkin_time = $params['checkin_time'] ? $params['checkin_time'] : time();
		$ahead = $params['ahead'] ? (int)$params['ahead'] : 3600;
		$ahead <= 0 && $ahead = 3600;

		$max = $page*$pageSize;
		$x = (int)$params['poi_x'];
		$y = (int)$params['poi_y'];
		$uid = (int)$this->identifier;

		$sql = "SELECT f.uid AS fuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS _x, Y(b.xy) AS _y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down, SQRT(POW(ABS(X(b.xy)-{$x}),2)+POW(ABS(Y(b.xy)-{$y}),2))/1.414 AS distance
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_x, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub ON ub.uid=b.uid				
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f ON b.uid=f.uid 
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at ON b.uid=at.uid AND at.file_id=b.attach
			WHERE b.checked=1 AND b.priv<>'private' AND b.uid<>".$uid." AND b.badge_id=0 AND `type`='checkin'
                AND bl.uid IS NULL AND ub.uid IS NULL AND f.friend_uid=".$uid." 
                AND b.dateline>=".($checkin_time-$ahead)." AND b.dateline<{$checkin_time}
                AND SQRT(POW(ABS(X(b.xy)-{$x}),2)+POW(ABS(Y(b.xy)-{$y}),2))<{$range}
            ORDER BY distance ASC, b.dateline DESC LIMIT {$max}";		
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$k = sprintf('%08.2f:%012d', $row['distance'], $checkin_time+300-$row['dateline']);
				//Better_Log::getInstance()->logInfo("\n$sid.\$row($k): " . print_r($row, true), 'status');
				$results[$k] = $row;
			}
		}
		if (count($results) > 0) {
			ksort($results);
			//Better_Log::getInstance()->logInfo("\n\$results: " . print_r($results, true), 'status');
			if (count($results) > $pageSize) {
				$tmp = array_chunk($results, $pageSize);
				$results = $tmp[0];
				unset($tmp);
			}
		}
		//Better_Log::getInstance()->logInfo("\n\$results: " . print_r($results, true), 'status');
		return $results;
	}

	/**
	 * 客户端用户公共空间
	 * 
	 * @param array $params
	 * @return array
	 */
	/*public function &apiPublicTimeline(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$followings = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$followings = count((array)Better_User::getInstance($this->identifier)->followings);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($followings>$of) {
			$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
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
			";			
		}
		
		$sql .= "
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
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			WHERE 1
		";
			
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql2 .= " AND bl.uid IS NULL";
		$sql2 .= " AND fe.follower_uid='".$uid."'";
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
					f.friend_uid IS NOT NULL
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
			b.`type` IN ('normal', 'checkin', 'tips')
			AND
			(
				(
					b.uid=".$uid." 
				)
				OR
				(".$sql2.")
			)
		)
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
	 * 新版客户端用户公共空间
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &apiPublicTimeline(array $params)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$friends = 0;
		$uid = (int)$this->identifier;
		
		if ($uid) {
			$friends = count((array)Better_User::getInstance($this->identifier)->friends);
		}
		
		$of = (int)Better_Config::getAppConfig()->dblist_offset;
		if ($friends>$of) {
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
				 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
				 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
				 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
			FROM ".BETTER_DB_TBL_PREFIX."blog AS b
			";
		} else {
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
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
				) AS b
			";			
		}
		
		$sql .= "
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
					ON b.uid=f.uid AND f.friend_uid=".$uid." AND f.home_show=1			
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach	
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
					ON ub.uid=b.uid		
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
					ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			WHERE 1
		";
			
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql2 .= " AND bl.uid IS NULL";
		$sql2 .= " AND ub.uid IS NULL";
		$sql2 .= " AND f.friend_uid='".$uid."' AND f.home_show=1";
		
		$sql .= " AND
			(
				(
					b.uid=".$uid." 
				)
				OR
				(".$sql2.")
			)
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
	}	
	
	
	/**
	 * 
	 * 取得某个poi上的动态
	 * @param array $params
	 * @return array
	 */
	public function &getSomePoi(array $params)
	{
		$type = isset($params['type']) ? $params['type'] : 'normal';
		$poiId = (int)$params['poi'];
		
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		$max = $page*$pageSize;
		$uid = (int)$this->identifier;

		$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
		FROM ".BETTER_DB_TBL_PREFIX."blog AS b USE KEY(poi_id)
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
				ON ub.uid=b.uid	
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$uid." AND f.home_show=1
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
				ON b.uid=at.uid AND at.file_id=b.attach	
		WHERE b.poi_id='".$poiId."'
		";
			
		$sql2 = " b.checked=1 AND b.priv!='private' AND b.uid!=".$uid." ";
		$sql2 .= " AND bl.uid IS NULL";
		$sql2 .= " AND ub.uid IS NULL";
		if(Better_Config::getAppConfig()->poistatus->allshow){
			$sql2 .= " AND f.friend_uid=".$uid." AND f.home_show=1";
		}
		if(is_array($type)){
			$sql .= " AND b.type IN ('".implode("','", $type)."') AND ( b.uid=".$uid." OR (".$sql2.") )";
		}else{
			if ($type=='tips') {
				$sql .= " AND
				(
					b.`type`='tips'
				)
				";			
			} else {
				$sql .= " AND
				(
					b.`type`='".$type."'
					AND
					(
						(
							b.uid=".$uid." 
						)
						OR
						(".$sql2.")
					)
				)
				";
			}
		}
		
		$sql .= "
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
	}
	
	
	/**
	 * 
	 * 取得某一个地点想来的好友
	 * @param array $params
	 * @return array
	 */
	public function &getFriendstodo(array $params)
	{		
		$poiId = (int)$params['poi'];		
		$type = trim($params['type']);
		$results = array();		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;		
		$max = $page*$pageSize;
		$uid = (int)$this->identifier;
		$conditions = array('uid'=>$uid,'home_show'=>1);
		$friends_uid = Better_DAO_Friends::getInstance($uid)->getAll($conditions);
		$fuids=array();
		foreach ($friends_uid as $f_uid){
			$fuids[]=$f_uid['friend_uid'];
		}
		unset($friends_uid);
		$fuids = implode (',', $fuids);
		$table = $type=="todo"?"todo":"todo_log";
		$sql = "SELECT p.nickname,p.username,p.avatar,b.bid,MAX(b.dateline) as dateline,b.uid,b.message,b.poi_id FROM ".BETTER_DB_TBL_PREFIX.$table." AS b 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p on p.uid=b.uid
					WHERE  b.poi_id="."'$poiId'";
		if(!empty($fuids)){
			$sql.=" and b.uid in(".$fuids.")";
		}
		$sql .= "
					GROUP BY b.uid
					ORDER BY dateline DESC
					 LIMIT ".$max;
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
			if (count($results)>$max) {
				$tmp = array_chunk($results, $max);
				$results = $tmp[0];
				unset($tmp);
			}
		}		
		return $results;			
	}
	
	
	/**
	 * 
	 * @param $fids
	 * @return unknown_type
	 */
	public function getFriendStatus($fids)
	{
		$results = array();
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			foreach ($fids as $fid) {
				$sql = <<<EOT
SELECT b.uid, b.bid, b.dateline
FROM better_blog AS b
INNER JOIN better_profile AS p ON p.uid = b.uid
WHERE b.uid='$fid'
AND b.checked =1
AND b.priv != 'private'
AND p.state != 'banned'
ORDER BY b.dateline DESC
LIMIT 1
EOT;

				$rs = self::squery($sql, $rdb);
				$row = $rs->fetch();
		
				if ($row) {
					$results[$row['dateline'].'.'.$row['bid']] = $row;
				}
			}	
		}		
		
		krsort($results);
		$results = array_slice($results, 0, 300);
		return $results;
	}
	
	
	/**
	 * 获取某人的动态
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &getSomebody(array $params, $returnCnt = false)
	{
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$type = (array)$params['type'];
		$uid = (int)$params['uid'];
		
		$thisUid = (int)$this->identifier;
		
		$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down, b.is_top, b.featured
			 ,p.nickname, p.username, p.gender, p.last_checkin_poi, p.avatar, p.priv_blog, p.state
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
		FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				INNER JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach
	    WHERE b.uid='".$uid."'
		";
		
		$sql_cnt = "SELECT COUNT(*) cnt
		FROM ".BETTER_DB_TBL_PREFIX."blog AS b 
				INNER JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
					ON p.uid=b.uid
				LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
					ON b.uid=at.uid AND at.file_id=b.attach
	    WHERE b.uid='".$uid."'
		";
		
		if (count($type)<3 && count($type)>0) {
			$sql .= " AND b.type IN ('".implode("','", $type)."')";
			$sql_cnt .= " AND b.type IN ('".implode("','", $type)."')";
		}
		
		if ($uid!=$thisUid && $uid!=BETTER_SYS_UID) {
			$sql .= " AND b.checked=1 AND b.priv!='private' AND p.state!='banned'";	
			$sql_cnt .= " AND b.checked=1 AND b.priv!='private' AND p.state!='banned'";	
		}

		$sql .= "
		ORDER BY b.dateline DESC
		LIMIT ".($page-1)*$pageSize.",".($pageSize+1);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		if ($returnCnt) {
			$rs = self::squery($sql_cnt, $rdb);
			$row = $rs->fetch();
			$cnt = $row['cnt'];
			
			$rows['cnt'] = $cnt;			
		}

		return $rows;
	}
	
	/**
	 * 获取某人想要去的地点
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &getSomeTodo(array $params,$currentuid)
	{
		$results = array();
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page <= 0 && $page = 1;
		$pageSize = $params['page_size'] ? (int) $params['page_size'] : 1;
		$pageSize <= 0 && $pageSize = BETTER_PAGE_SIZE;
		$type = (array) $params['type'];
		$uid = key_exists('uid', $params) ? (int) $params['uid'] : $currentuid;
		$poiId = key_exists('poi', $params) ? (int) $params['poi'] : 0;
		$order = isset($params['order']) ? $params['order'] : '';

		$max = $page*$pageSize+1;
		$followings = 0;
		$thisUid = (int)$this->identifier;
		
		$sql = 'SELECT b.bid, b.uid, max(b.dateline) as dateline,b.message,b.poi_id,
			 p.nickname, p.username, p.gender, p.last_checkin_poi, p.avatar, p.priv_blog, p.state
			 , p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_x, Y(p.xy) AS user_y
			 , p.lbs_report, p.allow_rt, p.sync_badge';
		if ($order == 'distance_asc') {
			$sql .= ', X(b0.xy) AS poi_x, Y(b0.xy) AS poi_y, SQRT((X(b0.xy)-X(p.xy))*(X(b0.xy)-X(p.xy))+(Y(b0.xy)-Y(p.xy))*(Y(b0.xy)-Y(p.xy))) AS _distance';
		}
		$sql .= ' FROM ' . BETTER_DB_TBL_PREFIX . 'todo AS b 
			INNER JOIN ' . BETTER_DB_TBL_PREFIX . 'profile AS p ON p.uid=b.uid';
		if ($order == 'distance_asc') {
			$sql .= ' INNER JOIN ' . BETTER_DB_TBL_PREFIX . 'blog AS b0 ON b0.bid=b.bid';
		}
		$sql .= ' WHERE  b.uid=' . $uid;
		if (poiId > 0) $sql .= ' AND b.poi_id=' . $poiId;
		$sql .= " GROUP BY b.poi_id";
		if ($order == 'distance_asc') {
			$sql .= " ORDER BY _distance ASC";
		} else {
			$sql .= " ORDER BY b.dateline DESC";
		}
		$sql .= " LIMIT ".$max;
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$row['inmytodo']=false;	
			if( !empty($row['poi_id']) && $row['poi_id'] != 0 ){
				//判断是否为我想去的地点, 功能已关闭
				/*					
				if( Better_DAO_Blog::isTodoPoi($currentuid,$row['poi_id'])>0){
					$row['inmytodo']=true; 
				}
				*/
				$row['type']='todo';
				$row['inlist'] = true;
			}	
			if ($order == 'distance_asc') {
				$key = $row['_distance'];
			} else {
				$key = $row['dateline'].'.'.$row['bid'];
			}
			$results[$key] = $row;
		}
		if (count($results) > 0) {
			if ($order == 'distance_asc') {
				ksort($results, SORT_NUMERIC);
			} else {
				krsort($results);
			}
			if (count($results) > BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}
		return $results;
	}
	
	/**
	 * 
	 * 根据bid获取数据
	 * @param unknown_type $bid
	 */
	public static function &byBid($bid)
	{
		list($uid, $foobar) = explode('.', $bid);
		$result = array();
		
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
					 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
					 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
					 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
					 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
				FROM `".BETTER_DB_TBL_PREFIX."blog` AS b
					INNER JOIN `".BETTER_DB_TBL_PREFIX."profile` AS p
						ON p.uid=b.uid
					LEFT JOIN `".BETTER_DB_TBL_PREFIX."attachments` AS at
						ON  b.uid=at.uid AND at.file_id=b.attach	
				WHERE bid='".$bid."'
			 ";
		
		$rs = self::squery($sql, $rdb);
		$result = $rs->fetch();
		
		return $result;
	}
	
	/**
	 * 
	 * 简化的根据bid获取数据，只从blog表取
	 * @param unknown_type $bid
	 */
	public static function &simpleByBid($bid)
	{
		list($uid, $foobar) = explode('.', $bid);
		$result = array();
		
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
					 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
				FROM `".BETTER_DB_TBL_PREFIX."blog` AS b
				WHERE bid='".$bid."'
			 ";
		
		$rs = self::squery($sql, $rdb);
		$result = $rs->fetch();
		
		return $result;		
	}
		
	/**
	 * 
	 * 指定一些bid来获得数据
	 * 主要用在获取转发的吼吼、签到等
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &byBids(array $params)
	{
		$results = array();
		
		$bids = (array)$params['bids'];
		if (count($bids)==0) {
			return $results;
		}
		
		$uid = (int)$this->identifier;
		
		$cacher = Better_Cache::remote();
		$cachedBids = array();
		foreach ($bids as $bid) {
			$cacheKey = 'blog_bid_'.md5($bid);
			$row = $cacher->get($cacheKey);
			if ($row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
				$cachedBids[] = $row['bid'];
			}
		}
		
		if (count($cachedBids)<=count($bids)) {
			$bids = array_diff($bids, $cachedBids);
		
			$sql = "SELECT f.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
					 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
					 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
					 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report, p.allow_rt, p.sync_badge
					 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
				FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
						ON ub.uid=b.uid	
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
						ON p.uid=b.uid
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
						ON b.uid=f.uid AND f.friend_uid=".$uid."
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
						ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
						ON b.uid=at.uid AND at.file_id=b.attach	
				WHERE 
					b.checked=1 AND b.priv!='private' AND bl.uid IS NULL AND ub.uid IS NULL
					AND 
						b.bid IN ('".implode("','", $bids)."')
			";
	
			$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
			foreach ($servers as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = &$cs['r'];
				
				$rs = self::squery($sql, $rdb);
				$rows = $rs->fetchAll();
				foreach ($rows as $row) {
					$results[$row['dateline'].'.'.$row['bid']] = $row;
					$cacher->set('blog_bid_'.md5($row['bid']), $row);
				}			
			}
		}

		return $results;				
	}
	
	
	/**
	 * 获得某人的bids  清某人bids缓存时用
	 */
	public function getBidsByuid($uid){
		$sql = "select bid from ".BETTER_DB_TBL_PREFIX."blog where uid=".$uid." order by dateline desc limit ".BETTER_MAX_LIST_ITEMS;
		
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
}