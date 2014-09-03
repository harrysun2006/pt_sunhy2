<?php

/**
 * 用户到过的地方
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_PlaceLog extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_place_log';
    	$this->priKey = 'id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection(true);
	}
	
  	public static function getInstance($identifier=0)
	{
		//if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			//self::$instance[$identifier] = new self($identifier);
		//}
		
		//return self::$instance[$identifier];
		return new self($identifier);
	}
	
	public function insert($data)
	{
		return $this->_insertXY($data);
	}

	public function getCheckinCount($poiId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', intval($poiId));
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	public function getMyCheckinCount($poiId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total'),
			new Zend_Db_Expr('MAX(checkin_time) AS checkin_time')
			));
		$select->where('poi_id=?', intval($poiId));
		$select->where('uid=?', $this->identifier);		
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();		
		return $row;
	}	
	
	public function getMyValidCheckinCount($poiId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')			
			));
		$select->where('poi_id=?', intval($poiId));
		$select->where('uid=?', $this->identifier);
		$select->where('checkin_score>?', 0);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}		
	
	/**
	 * 获得在某个poi的有效checkin次数
	 * 
	 * @return integer
	 */
	public function getTodayValidCheckinCount($poiId=0)
	{
		$now = time();
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
							
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$poiId>0 && $select->where('poi_id=?', intval($poiId));
		$select->where('checkin_score>?', 0);
		$select->where('checkin_time>?', $dayStart);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	/**
	 * 取得某一POI的签到排行
	 */
	public function getValidCheckinRang($poiId,$count)
	{
	 	$return = array(
	 					'rows' => array()
	 					);
		$now = time();
		$dayStart = $now-60*24*3600;
		$page=1;
		$limit = $page*$count+1;
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		$return = array('total' => 0, 'rows' => array());
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$sql = "select count(*) as times,max(pl.checkin_time) as checkin_time,pl.uid,p.nickname,p.username,p.avatar  from ".BETTER_DB_TBL_PREFIX.'user_place_log pl';
			$sql .= " left join ".BETTER_DB_TBL_PREFIX."profile p on p.uid=pl.uid ";
			$sql .= "where 1 AND poi_id=".$poiId;
			$sql .= " AND pl.checkin_time>".$dayStart;
			$sql .= " AND pl.checkin_score>0";
			$sql .= " group by uid" ;
			$sql .= " ORDER BY times DESC
			LIMIT ".$limit;
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$key = $v['times'].$v['checkin_time'].'.'.(10000000-$v['uid']);
				$results[$key] = $v;
			}
		}
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, $count);
			unset($results);
			if (isset($data[0])) {
				$return['rows'] = $data[0];
			}
		}
		return $return;	
	}
	
	/**
	 * 取得用户5分钟内的签到次数
	 * 
	 * @return integer
	 */
	public function getFiveMinutesCheckinCount()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('checkin_time>?', time()-300);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	/**
	 * 取得用户1小时内的签到次数
	 * 
	 * @return integer
	 */
	public function getOneHourCheckinCount()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('checkin_time>?', time()-3600);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}

	
	/**
	 * 取指定时间的checkin 次数
	 */
	public function getCheckinCountByDay($poiId, $day=8, $valid=false) 
	{
		$select = $this->rdb->select();

		if ($day) {
			$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT(*) AS `total`')
				));			
		} else {
			$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT( DISTINCT (CONCAT( YEAR( FROM_UNIXTIME( `checkin_time` ) ) , MONTH( FROM_UNIXTIME( `checkin_time` ) ) , DAY( FROM_UNIXTIME( `checkin_time` ) ) ) )) AS `total`')
				));
		}

		$select->where('poi_id=?', intval($poiId));
		if ($valid!==null) {
			$valid==true ? $select->where('checkin_score>?', 0) : $select->where('checkin_score=?', 0);
		}
		$select->where('checkin_time>?', time() - $day * 3600 * 24);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);	
		$row = $rs->fetch();
		return (int)$row['total'];		
	} 
	
	/**
	 * 取得两个月来checkin次数
	 * 
	 * @return integer
	 */
	public function getTowMonthCheckinCount($poiId, $valid=false)
	{
		$select = $this->rdb->select();
		
		$polo_pois = Better_Market_Polo::getInstance()->shopAll;
		$inPolo = in_array($poiId, $polo_pois) ? true : false;
		/*if ($inPolo) {
			$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT(*) AS `total`')
				));			
		} else {
			$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT( DISTINCT (CONCAT( YEAR( FROM_UNIXTIME( `checkin_time` ) ) , MONTH( FROM_UNIXTIME( `checkin_time` ) ) , DAY( FROM_UNIXTIME( `checkin_time` ) ) ) )) AS `total`')
				));
		}*/
		//每天只算一次去掉
		$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT(*) AS `total`')
		));	

		$select->where('poi_id=?', intval($poiId));
		if ($valid!==null) {
			$valid==true ? $select->where('checkin_score>?', 0) : $select->where('checkin_score=?', 0);
		}
		$select->where('checkin_time>?', time()-60*3600*24);
		$select->where('uid=?', $this->identifier);
		$rs = self::squery($select, $this->rdb);	
		$row = $rs->fetch();
		return (int)$row['total'];
	}
	
	public function getOfterCheckedPoiIds(array $params)
	{
		$poiIds = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['count'] ? (int)$params['count'] : BETTER_PAGE_SIZE;
		$lon = $params['lon'] ? (float)$params['lon'] : 0;
		$lat = $params['lat'] ? (float)$params['lat'] : 0;
		$range = $params['range'] ? (int)$params['range'] : 5000;
		$st = $page*$pageSize - $pageSize;
		$llSql = "";
		
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$llSql .= " AND MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}			
				
		$sql = "SELECT poi_id, COUNT(poi_id) AS count,MAX(checkin_time) AS checkin_time
			FROM `".$this->tbl."`
			WHERE uid='".$this->identifier."' ".$llSql." AND checkin_score>0
			GROUP BY poi_id
			HAVING(count)>3
			ORDER BY count DESC
			LIMIT ".$st.",".$pageSize."
			";
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		
		foreach ($rows as $row) {
			$poiIds[$row['poi_id']] = $row;
		}
		
		return $poiIds;
	}
	
	/**
	 * 取出所有checkin过的poi
	 * 
	 * @return array
	 */
	public function getCheckinedPois($page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);
		
		$result['total'] = $this->getCheckinedPoisCount();
		
		if ($result['total']>0) {
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				'poi_id', new Zend_Db_Expr('MAX(checkin_time) AS checkin_time')
				));
			$select->where('uid=?', $this->identifier);
			$select->group('poi_id');
			$select->order('checkin_time DESC');
			$select->limitPage($page, $count);
			
			$rs = self::squery($select, $this->rdb);
			$result['rows'] = $rs->fetchAll();
		}
		
		return $result;
	}
	
	
	/**
	 * 取出多少天内checkin过的poi
	 * 
	 * @return array
	 */
	public function getSomeDaysCheckinedPois($params)
	{
		$result = array();
		$days = $params['days'] ? $params['days']: 30;
		//$page = $params['page']? $params['page']:1;
		//$pagecount = $params['pagecount']? $params['pagecount'] :12;
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'poi_id', new Zend_Db_Expr('MAX(checkin_time) AS checkin_time'),  new Zend_Db_Expr('COUNT(id) AS checkin_count')
			));
		$select->where('uid=?', $this->identifier);
		//$select->where('checkin_time>=?', time()-$days*24*3600);
		$select->group('poi_id');
		$select->order('checkin_time DESC');
		//$select->limitPage($page, $pagecount);
		
		$rs = self::squery($select, $this->rdb);
		$result = $rs->fetchAll();
		
		return $result;
	}
	
	
	/**
	 * 获取今晚签到过的poiids
	 * 
	 * @return array
	 */
	public function getTonightCheckinedPoiIds($from, $to)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('DISTINCT(poi_id) AS poi_id'),
			));
		$select->where('uid=?', $this->identifier);
		$select->where('checkin_time>=?', $from);
		$select->where('checkin_time<=?', $to);
		$select->where('checkin_score>?', 0);
		
		$rs = self::squery($select, $this->rdb);
		$ids = array();
		$tmp = $rs->fetchAll();
		
		foreach ($tmp as $row) {
			$ids[] = $row['poi_id'];
		}
		
		return $ids;		
	}

	/**
	 * 取得签到过的poiids
	 * 
	 * @return array
	 */
	public function getCheckinedPoiIds()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('DISTINCT(poi_id) AS poi_id'),
			));
		$select->where('uid=?', $this->identifier);
		$select->where('checkin_score>?', 0);
		
		$rs = self::squery($select, $this->rdb);
		$ids = array();
		$tmp = $rs->fetchAll();
		
		foreach ($tmp as $row) {
			$ids[] = $row['poi_id'];
		}
		
		return $ids;
	}
	
	/**
	 * 是否第一次来某个Poi
	 * 
	 * @return bool
	 */
	public function isFirstCheckin($poiId, $mode=1)
	{
		$flag = false;
		
		if ($poiId) {
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('poi_id=?', $poiId);
			$select->where('uid=?', $this->identifier);
			
			$rs = self::squery($select, $this->rdb);
			$row = $rs->fetch();
			
			$flag = $row['total']==$mode ? true : false;
		}	
		
		return $flag;
	}

	/**
	 * 取得所有checkin过的poi的数量
	 * 
	 * @return integer
	 */
	public function getCheckinedPoisCount()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(DISTINCT(poi_id)) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];			
	}
	
	/**
	 * 取得在某个poi的最后签到时间
	 * 
	 * @return
	 */
	public function getLastCheckinedAtPoi($poiId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'checkin_time'
			));
		$select->where('uid=?', $this->identifier);
		$select->where('poi_id=?', $poiId);
		$select->order('checkin_time DESC');
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (float)$row['checkin_time'];
		
	}
	
	/**
	 * 
	 * 取得某天在某个poi签到次数（不算是否有效签到）
	 * @param unknown_type $poiId
	 */
	public function getTodayCheckinCount($poiId=0)
	{
		$now = time();
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
							
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		
		$poiId>0 && $select->where('poi_id=?', intval($poiId));
		$select->where('checkin_time>?', $dayStart);
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];		
	}
	
	public static function sGetTodayCheckinCount($poiId)
	{
		$total = 0;
		
		$now = time();
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
							
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = self::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			
			$select->where('poi_id=?', intval($poiId));
			$select->where('checkin_time>?', $dayStart);
			
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();	

			$total += (int)$row['total'];
		}

		
		return $total;
	}
	
	/**
	 * 取得某一POI的好友签到
	 */
	public function getFriendsCheckin($poiId,$page=1,$count,$uid)
	{
		$return = array(
			'rows' => array(),
			'total' => 0
		);
		$max = $page*$count+1;
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$sql = "select count(*) as times,max(pl.checkin_time) as checkin_time,pl.uid,p.nickname,p.username,p.avatar  from ".BETTER_DB_TBL_PREFIX.'user_place_log pl';
			$sql .= " left join ".BETTER_DB_TBL_PREFIX."profile p on p.uid=pl.uid ";
			$sql .= " left join ".BETTER_DB_TBL_PREFIX."friends f on pl.uid=f.uid";
			$sql .= " where 1 AND f.friend_uid=".$uid." AND poi_id=".$poiId;

			$sql .= " group by uid" ;
			$sql .= " ORDER BY checkin_time desc,times DESC";
			$sql .= " LIMIT ".$max;

			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$key =$v['checkin_time'].'.'.(10000000-$v['uid']). $v['times'];
				$results[$key] = $v;
			}
		}
		if (count($results)>0) {
			$return['total'] = count($results);
			
			krsort($results);

			$data = array_chunk($results, $count);
			unset($results);
			if (isset($data[$page-1])) {
				$return['rows'] = $data[$page-1];
			}
		}
		return $return;	
	}
	
}