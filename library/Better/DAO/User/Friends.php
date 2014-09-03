<?php

/**
 * 用户好友
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Friends extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'friends';
    	
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
	
	public function &rightbar()
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  
				'p.avatar', 'p.karma', 'p.rp'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'friends AS f', 'f.uid=p.uid AND f.friend_uid='.$this->identifier, array('f.dateline'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->order('p.rp DESC');
			$select->limit(180);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['rp'].'.'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, 180);
		
		$rows = isset($data[0]) ? $data[0] : array();

		return $rows;	
	}
	
	public function nearByCount($lon, $lat, $range=5000)
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		$total = 0;
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x-$range/2;
		$y1 = $y+$range/2;
		$x2 = $x+$range/2;
		$y2 = $y-$range/2;
		
		$sql = "SELECT COUNT(*) AS total
					FROM ".BETTER_DB_TBL_PREFIX."profile AS p
						INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
							ON f.uid=p.uid AND f.friend_uid=".$this->identifier."
					WHERE p.state!='banned' AND MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))
		";

		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);

			$rs = self::squery($sql, $cs['r']);
			$row = $rs->fetch();
			$total += (int)$row['total'];
		}		
		
		return $total;
	}
	
	public function nearBy($lon, $lat, $range=5000, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x-$range/2;
		$y1 = $y+$range/2;
		$x2 = $x+$range/2;
		$y2 = $y-$range/2;
		
		$sql = "SELECT p.uid, p.username, p.nickname, p.gender, p.birthday, p.self_intro, p.language, p.avatar, p.live_province, p.live_city, p.visits, p.visited, p.priv_profile
									,p.priv_blog, p.last_active, p.last_bid, p.status, p.address, p.lbs_report, p.city, p.places, p.msn, X(p.xy) AS x, Y(p.xy) AS y, p.receive_msn_notify
									,p.state, p.karma, p.rp
								,c.followings, c.followers, c.favorites, c.posts AS posts, c.received_msgs, c.friends, c.majors, c.places, c.checkins, c.invites, c.badges, c.treasures
								,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
					FROM ".BETTER_DB_TBL_PREFIX."profile AS p
						INNER JOIN ".BETTER_DB_TBL_PREFIX."profile_counters AS c
							ON p.uid=c.uid
						INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
							ON f.uid=p.uid AND f.friend_uid=".$this->identifier."
						LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
							ON at.uid=p.uid AND at.file_id=p.avatar
					WHERE p.state!='banned' AND MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))
					ORDER BY p.rp DESC
					LIMIT ".($page*$pageSize+1)."
		";

		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];

			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['rp'].'.'.$v['uid']] = $v;
			}
		}
		
		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => count($data),
						'count' => count($rows),
						'total' => count($results),
						'rows' => (array)$rows
						);				
	}	
	
	public function commonFriendsWithCount($uid)
	{
		$count = 0;
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			
			$sql = "SELECT COUNT(*) AS count
				FROM ".BETTER_DB_TBL_PREFIX."profile AS p
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f1
						ON f1.uid=p.uid AND f1.friend_uid=".$this->identifier."
					INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f2
						ON f2.uid=p.uid AND f2.friend_uid=".$uid."
				WHERE p.state!='banned'				
				";
			
			$rs = self::squery($sql, $rdb);
			$row = $rs->fetch();
			
			$count += (int)$row['count'];
		}
		
		return $count;		
	}	

	public function commonFriendsWith($uid, $page, $pageSize=BETTER_PAGE_SIZE)
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			
			$sql = "SELECT p.uid, p.username, p.nickname, p.gender, p.birthday, p.self_intro, p.language, p.avatar, p.live_province, p.live_city, p.visits, p.visited, p.priv_profile
										,p.priv_blog, p.last_active, p.last_bid, p.status, p.address, p.lbs_report, p.city, p.places, p.msn, X(p.xy) AS x, Y(p.xy) AS y, p.receive_msn_notify
										,p.state, p.karma, p.rp
									,c.followings, c.followers, c.favorites, c.posts AS posts, c.received_msgs, c.friends, c.majors, c.places, c.checkins, c.invites, c.badges, c.treasures
									,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
						FROM ".BETTER_DB_TBL_PREFIX."profile AS p
							INNER JOIN ".BETTER_DB_TBL_PREFIX."profile_counters AS c
								ON p.uid=c.uid
							INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f1
								ON f1.uid=p.uid AND f1.friend_uid=".$this->identifier."
							INNER JOIN ".BETTER_DB_TBL_PREFIX."friends AS f2
								ON f2.uid=p.uid AND f2.friend_uid=".$uid."
							LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
								ON at.uid=p.uid AND at.file_id=p.avatar
						WHERE p.state!='banned'
						ORDER BY p.rp DESC
						LIMIT ".($page*$pageSize+1)."
			";
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['rp'].'.'.$v['uid']] = $v;
			}
		}
		
		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => count($data),
						'count' => count($rows),
						'total' => count($results),
						'rows' => (array)$rows
						);				
	}

	public function getAllFriends($page, $pageSize=BETTER_PAGE_SIZE)
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma', 
				'p.last_checkin_poi', 'p.timezone', 'p.rp'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->join(BETTER_DB_TBL_PREFIX.'friends AS f', 'f.uid=p.uid AND f.friend_uid='.$this->identifier, array('f.dateline', 'f.home_show'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->order('p.rp DESC');
			//$select->limit($page*$pageSize+1);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['rp'].'.'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		
		$data = array_chunk($results, $pageSize);
		if($pageSize==3*16){//hardcode
			$pages = count(array_chunk($results, 16));
		}else{
			$pages = count($data);
		}
		
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => $pages,
						'count' => count($rows),
						'total' => count($results),
						'rows' => (array)$rows
						);		
	}	
	
	
	
	public function getAllFriendsByKeywords($page, $pageSize=BETTER_PAGE_SIZE,$keywords="")
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma', 
				'p.last_checkin_poi', 'p.timezone', 'p.rp'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->join(BETTER_DB_TBL_PREFIX.'friends AS f', 'f.uid=p.uid AND f.friend_uid='.$this->identifier, array('f.dateline', 'f.home_show'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->where("p.nickname like '%".$keywords."%'");
			$select->order('p.rp DESC');
		//	$select->limit($page*$pageSize+1);//如果做分页的话，那么最终的结果中的结果总数量就会出错
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['rp'].'.'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$pages = count(array_chunk($results, 16));
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => $pages,
						'count' => count($rows),
						'total' => count($results),
						'rows' => (array)$rows
						);		
	}	
	
	
}
