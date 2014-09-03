<?php

/**
 * 用户的勋章
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Badge extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_badges';
    	$this->priKey = 'uid';
    	$this->orderKey = &$this->priKey;
    	
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
	
	protected function _calculateMix($sql, array $params=array())
	{
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$range = trim($params['range']);
		
		$result = false;
		
		if ($range!='') {
			$poiIds = (array)Better_Registry::get('_poi_ids');
			if (count($poiIds)==0) {
				$poiIds = Better_DAO_User_PlaceLog::getInstance($this->identifier)->getCheckinedPoiIds();
				Better_Registry::set('_poi_ids', $poiIds);	
			}
			$poiIds[] = 0;
			
			$params['poi_ids'] = &$poiIds;
			foreach ($params as $k=>$v) {
				$sql = str_replace('{'.strtoupper($k).'}', $v, $sql);
			}
			
			if ($x && $y) {						
				$ranges = explode('|', $range);
				
				$db = parent::registerDbConnection('poi_server');

				foreach ($ranges as $v) {
					list($x1, $y1, $x2, $y2) = explode(',', $v);
					$rgs[] = "(MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
				}
				
				$sql .= ' AND ('.implode(' OR ', $rgs).')';

				$rs = self::squery($sql, $db);
				$rows = $rs->fetchAll();

				if (is_array($rows) && isset($rows[0]) && isset($rows[0]['total']) && $rows[0]['total']) {
					$result = true;
				}
			} else {
				return $result;
			}			
		}
		
		return $result;
	}
	
	/**
	 * 执行一条计算勋章条件的sql
	 * 
	 * @param $sql
	 * @return bool
	 */
	public function calculate($sql, array $params=array())
	{
		$result = false;
		
		if ($params['sql2']!='') {
			return $this->_calculate($sql, $params);
		}
		
		foreach ($params as $k=>$v) {
			$sql = str_replace('{'.strtoupper($k).'}', $v, $sql);
		}

		$simple = (int)$params['simple'];
		$checkPoi = (bool)$params['check_poi'];
		$range = trim($params['range']);

		if ($checkPoi) {
			$poiDb = parent::registerDbConnection('poi_server');
			$db = &$poiDb;
			$ids = Better_DAO_User_PlaceLog::getInstance($this->identifier)->getCheckinedPoiIds();
			$sql = str_replace('{POI_IDS}', implode(',', $ids), $sql);
			
			if (isset($params['x']) && isset($params['y'])) {
				
				if ($range) {
					$x = (float)$params['x'];
					$y = (float)$params['y'];
					
					if ($x && $y) {
						$ranges = explode('|', $range);
						$db = parent::registerDbConnection('poi_server');
						
						foreach ($ranges as $v) {
							list($x1, $y1, $x2, $y2) = explode(',', $v);
							
							$add = $badgeLoose ? '' : "MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND ";
							$rgs[] = "(".$add." MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
						}
						
						$sql .= ' AND ('.implode(' OR ', $rgs).')';			
						Better_Log::getInstance()->logAlert('Range:['.$range.'], x:['.(isset($params['x']) ? "YES" : "NO").'],ID:['.$params['id']."], SQL:[".$sql."]", 'badge');
					} else {
						$sql .= ' AND 1=0';
						Better_Log::getInstance()->logAlert('Range:['.$range.'], x:['.(isset($params['x']) ? "YES" : "NO").'],ID:['.$params['id']."], SQL:[".$sql."]", 'badge');
						return false;
					}
				} 
			}
			
			$rs = self::squery($sql, $db);
			$rows = $rs->fetchAll();
			if (is_array($rows) && isset($rows[0]) && isset($rows[0]['total']) && $rows[0]['total']>0) {
				$result = true;
			}
		} else {
			if ($simple==0) {
				$x = (float)$params['x'];
				$y = (float)$params['y'];
				
				$badgeLoose = Better_Config::getAppConfig()->badge->spec_loose;

				if ($range!='') {

					if (($x && $y) || $badgeLoose) {						
						$ranges = explode('|', $range);
						
						$db = parent::registerDbConnection('poi_server');

						foreach ($ranges as $v) {
							list($x1, $y1, $x2, $y2) = explode(',', $v);
							
							$add = $badgeLoose ? '' : "MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND ";
							$rgs[] = "(".$add." MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
						}
						
						$sql .= ' AND ('.implode(' OR ', $rgs).')';
						Better_Log::getInstance()->logAlert('ID:['.$params['id']."], SQL:[".$sql."]", 'badge');
						
						$rs = self::squery($sql, $db);

					} else {
						return $result;
					}
				} else {
					Better_Log::getInstance()->logAlert('ID:['.$params['id']."], SQL:[".$sql."]", 'badge');
					
					$rs = self::squery($sql, $this->rdb);
				}

				$rows = $rs->fetchAll();

				if (is_array($rows) && isset($rows[0]) && isset($rows[0]['total']) && $rows[0]['total']) {
					if ($params['limit']>0 && $rows[0]['total']>=$params['limit']) {
						$result = true;
					} else if ($params['limit']==0) {
						$result = true;
					}
					
					if ($result==true) {
						Better_Log::getInstance()->logInfo('Gotted:['.$params['id'].'], sql:['.$sql.']', 'badge_got');
					}
				}
			} else {
				$total = 0;
				$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
				foreach ($sids as $sid) {
					$cs = parent::assignDbConnection('user_server_'.$sid);
					$rdb = &$cs['r'];
					
					Better_Log::getInstance()->logAlert('ID:['.$params['id']."], SQL:[".$sql."]", 'badge');
					$rs = self::squery($sql, $rdb);
					$rows = $rs->fetchAll();
					
					if (is_array($rows) && isset($rows[0]) && isset($rows[0]['total'])) {
						$total += (int)$rows[0]['total'];
					}
				}
				
				if ($total>=$simple) {
					$result = true;
				}
			}
		}

		return $result;
	}
	
	public function getUserBadges()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS b');
		$select->joinleft(BETTER_DB_TBL_PREFIX.'badge_exchange_log AS l', 'l.badge_id=b.bid AND l.uid=b.uid', array(
			'l.dateline AS exchanged'
			));
		$select->where('b.uid=?', $this->identifier);
		$select->order('b.get_time DESC');
		
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}

	public function getAll($val=null, $limit='0', $method='limit')
	{
		$method = $method=='limit' ? 'limit' : 'limitPage';

		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		if (is_array($val) && isset($val['order'])) {
			$this->parseOrder($select,$val['order']);
		} else {
			$select->order($this->orderKey.' DESC');
		}
		$limit!=null && self::$method($select, $limit);

		$result = self::squery($select, $this->rdb);
		$data = $result->fetchAll();

		return is_array($data) ? $data : array();
	}
	
	/**
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	public static function getbadgenums($params)
	{
		$total = 0;
		$badege = $params['badege'];
		$begintm = $params['begintm'];		
		$results = array();		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		$num = 0;
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$sql = "select count(uid) as badges from better_user_badges where bid in (".$badege.") and get_time>=".$begintm;				
			$rs = self::squery($sql, $rdb);			
			$rows = $rs->fetch();
			$num +=$rows['badges'];
		}	
		$total = (int)$num;		
		return $total;		
	}
	
	
	/**
	 * 
	 */
	public function getBadgeCntByUid($uid)
	{
		$sql = "SELECT count(*) cnt FROM better_user_badges WHERE uid='$uid'"; 		
		$rs = self::squery($sql, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['cnt'];		
	}
}