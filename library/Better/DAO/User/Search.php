<?php

/**
 * 用户搜索数据操作
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_Search extends Better_DAO_User
{
	private static $instance = array();
	private static $sysuser = '10000,168671';
	public function __construct($identifier=0)
	{
		parent::__construct($identifier);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	

	/**
	 *	根据指定的uids获取用户信息
	 *
	 * @param $uids
	 * @return array
	 */
	public function getUsersByUidsWithRange($uids, $page=1, $pageSize=BETTER_PAGE_SIZE, $lon=0, $lat=0, $range=50000)
	{
		$pageSize==0 && $pageSize = self::$usersPerpage;
		$dua = Better_DAO_User_Assign::getInstance();
		$rows = $dua->fetchAll($dua->getAdapter()->quoteInto('uid IN (?)', $uids));

		$servers = array();
		foreach($rows as $row) {
			$servers[$row['sid']][] = $row['uid'];
		}
		unset($rows);

		$results = array();
		foreach($servers as $_sid=>$_uids) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			
			$selected = array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma' , 
				'p.last_checkin_poi', 'p.timezone', 'p.rp'
				);
			$select->join($this->counterTbl.' AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
				
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
				
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql = "MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			$expr = new Zend_Db_Expr($sql);
			$select->where($expr);								
			
			$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(`xy`, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			$select->order('distance ASC');				
			
			$select->from($this->profileTbl.' AS p', $selected);
			$select->where('p.uid IN(?)', $_uids);
			$select->where('p.state!=?', 'banned');
			$select->order('p.last_active DESC');
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['distance'].'_'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => count($data),
						'count' => count($rows),
						'total' => count($results),
						'rows' => $rows
						);
	}	

	/**
	 * 搜索用户
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function search($keyword, $excludeUids=array(), $params=array())
	{
		$results = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$selected = array(
				'uid', 'karma', 'rp'
				);
			
			if (is_array($keyword)) {
				$select->where('uid IN (?)', $keyword);
			} else if (!is_array($keyword) && strlen($keyword)) {
				if ($keyword=='REC_UIDS') {
					$select->where('last_active>?', time()-7*3600*24);
				} else {
					$where = $rdb->quoteInto('username LIKE ?', '%'.$keyword.'%').
										$rdb->quoteInto(' OR nickname LIKE ?', '%'.$keyword.'%');
					$select->where($where);
				}
			}
			count($excludeUids)>0 && $select->where(' uid NOT IN(?)', $excludeUids);
			
			if ($params['lon'] && $params['lat']) {
				$range = isset($params['range']) ? (int)$params['range'] : 5000;
					
				list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
	
				$sql = "MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);				
				
				$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(`xy`, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			}
			
			if ($params['has_avatar']) {
				$select->where(new Zend_Db_Expr("avatar!='' AND avatar IS NOT NULL"));
			}
			
			$select->from($this->profileTbl, $selected);

			if (isset($params['public'])) {
				if ($params['public']) {
					//$select->where('priv_blog!=?', 1);
					//$select->where('sys_priv_blog!=?', 1);
				} else {
					//$select->where(new Zend_Db_Expr('priv_blog=1 OR sys_priv_blog=1'));
				}
			}
			
			if (isset($params['has_avatar'])) {
				$hasAvatar = (bool)$params['has_avatar'];
				$hasAvatar ? $select->where('avatar!=?', '') : $select->where('avatar=?', '');
			}
			
			if ($params['page'] && $params['count']) {
				$limit = (int)($params['page']*$params['count']) + 1;
				$select->limit($limit);
				if ($params['order_key']) {
					$select->order($params['order_key'].' DESC');
				}
			}
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();

			foreach($rows as $v) {
				$v['distance'] = (float)$v['distance'];
				$v['distance'] || $v['distance'] = 99999999;
				$results[$v['distance'].'.'.$v['uid']] = $v;
			}
		}
		
		return $results;
	}
	
	public function searchByKarma($keyword, $excludeUids=array(), $params=array())
	{
		$results = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$selected = array(
				'uid', 'karma', 'rp'
				);
			
			if (is_array($keyword)) {
				$select->where('uid IN (?)', $keyword);
			} else if (!is_array($keyword) && strlen($keyword)) {
				if ($keyword=='REC_UIDS') {
					$select->where('last_active>?', time()-7*3600*24);
				} else {
					$where = $rdb->quoteInto('username LIKE ?', '%'.$keyword.'%').
										$rdb->quoteInto(' OR nickname LIKE ?', '%'.$keyword.'%');
					$select->where($where);
				}
			}
			count($excludeUids)>0 && $select->where(' uid NOT IN(?)', $excludeUids);
			
			if ($params['lon'] && $params['lat']) {
				$range = isset($params['range']) ? (int)$params['range'] : 5000;
					
				list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
	
				$sql = "MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);				
				
				$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(`xy`, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			}
			
			if ($params['has_avatar']) {
				$select->where(new Zend_Db_Expr("avatar!='' AND avatar IS NOT NULL"));
			}
			
			$select->from($this->profileTbl, $selected);

			if (isset($params['public'])) {
				if ($params['public']) {
					//$select->where('priv_blog!=?', 1);
					//$select->where('sys_priv_blog!=?', 1);
				} else {
					//$select->where(new Zend_Db_Expr('priv_blog=1 OR sys_priv_blog=1'));
				}
			}
			
			if ($params['page'] && $params['count']) {
				$limit = (int)($params['page']*$params['count']) + 1;
				$select->limit($limit);
			}

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();

			foreach($rows as $v) {
				$v['distance'] = (float)$v['distance'];
				$v['distance'] || $v['distance'] = 99999999;
				$results[intval($v['rp']).'.'.$v['uid']] = $v;
			}
		}
		
		return $results;
	}	
	
	
	/**
	 * 根据Email查找用户
	 *
	 */
	public function searchByEmail($email, $excludeUids=array(), $params=array())
	{
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from($this->tbl.' AS c', array(
				'uid' 
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=c.uid', array());

			is_array($email) ? $select->where('c.email IN (?)', $email) : $select->where('c.email=?', $email);
			count($excludeUids)>0 && $select->where(' c.uid NOT IN(?)', $excludeUids);

			if ($params['lon'] && $params['lat']) {
				$range = isset($params['range']) ? (int)$params['range'] : 5000;
					
				list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
	
				$sql = "MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);				
			}
						
			$rs = self::squery($select, $rdb);

			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[] = $v;
			}
		}
		
		return $results;
	}
	
	/**
	 * 根据手机号码搜索用户
	 * 
	 * @param $cell
	 * @param $excludeUids
	 * @return array
	 */
	public function searchByCell($cell, $excludeUids=array(), $params=array())
	{
		$results = array();
		$cell = (array)$cell;
		
		if (count($cell)>0) {
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
			foreach ($sids as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = &$cs['r'];
				
				$select = $rdb->select();
				$select->from($this->tbl.' AS c', 'uid');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=c.uid', array(
					'p.karma'
					));
				
				$select->where('c.cell_no IN (?)', $cell);
				count($excludeUids)>0 && $select->where(' c.uid NOT IN (?)', $excludeUids);
	
				if ($params['lon'] && $params['lat']) {
					$range = isset($params['range']) ? (int)$params['range'] : 5000;
						
					list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
					$x1 = $x-$range/2;
					$y1 = $y+$range/2;
					$x2 = $x+$range/2;
					$y2 = $y-$range/2;
		
					$sql = "MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
					$expr = new Zend_Db_Expr($sql);
					$select->where($expr);				
				}
							
				$rs = self::squery($select, $rdb);
				$rows = $rs->fetchAll();
				
				foreach ($rows as $row) {
					$results[$row['karma'].'.'.$row['uid']] = $row;
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * 根据坐标范围查找用户
	 *
	 * @return unknown_type
	 */
	public function searchByRange()
	{
		$results = array();
		
		return $results;
	}
	
	public function searchByTop20Karma()
	{
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();
			$select = $rdb->select();

			$sql = 'select p.* from '.BETTER_DB_TBL_PREFIX.'profile AS p where p.state="enabled"  and  p.avatar!=""    and p.uid NOT
IN ('.self::$sysuser.') order by p.karma DESC LIMIT 20';	
			$rs = self::squery($sql, $rdb);

			$rows = $rs->fetchAll();
				
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}			
		}		
		return $results;
	}
	
	public function searchByTop20Followers()
	{
		$results = array();
		/*$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();

			$sql = 'select p.*,pc.followers from '.BETTER_DB_TBL_PREFIX.'profile AS p'.','.BETTER_DB_TBL_PREFIX.'profile_counters AS pc where p.state="enabled" and (p.priv_blog=0 or p.sys_priv_blog=0) and  p.avatar!="" and p.uid=pc.uid   and p.uid NOT
IN ('.self::$sysuser.') order by pc.followers DESC LIMIT 20';
			
			$rs = self::squery($sql, $rdb);

			$rows = $rs->fetchAll();
				
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}			
		}	*/
		return $results;
	}
	
	public function searchByTop20Friends()
	{
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();

			$sql = 'select p.*,pc.friends from '.BETTER_DB_TBL_PREFIX.'profile AS p'.','.BETTER_DB_TBL_PREFIX.'profile_counters AS pc where p.state="enabled" and p.avatar!="" and p.uid=pc.uid  and p.uid NOT
IN ('.self::$sysuser.') order by pc.friends DESC LIMIT 20';
			
			$rs = self::squery($sql, $rdb);

			$rows = $rs->fetchAll();
				
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}					
		}	
		
		return $results;
	}
	
	public function searchByTop20Blogs()
	{
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();

			$sql = 'select p.*,(pc.posts-pc.checkins) as blogsnum from '.BETTER_DB_TBL_PREFIX.'profile AS p'.','.BETTER_DB_TBL_PREFIX.'profile_counters AS pc where p.state="enabled"  and p.avatar!="" and p.uid=pc.uid and p.uid NOT
IN ('.self::$sysuser.') order by blogsnum DESC LIMIT 20';
			$rs = self::squery($sql, $rdb);

			$rows = $rs->fetchAll();			
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}			
		}		
		return $results;
	}
	
	public function searchNewUser()
	{
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$select = $rdb->select();
			$sql = 'select p.*,a.regtime from '.BETTER_DB_TBL_PREFIX.'profile AS p'.','.BETTER_DB_TBL_PREFIX.'account AS a where p.state="enabled" and p.avatar!="" and p.uid=a.uid order by a.regtime DESC LIMIT 50';
			$rs = self::squery($sql, $rdb);
			//Better_Log::getInstance()->logInfo($sql,'xxxaaa');
			$rows = $rs->fetchAll();			
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}			
		}		
		return $results;
	}
					
}