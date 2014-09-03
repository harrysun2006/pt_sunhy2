<?php

/**
 * POI促销的数据操作
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Poi_Notification extends Better_DAO_Poi_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_notification';
		$this->orderKey = 'nid';
		$this->priKey = 'nid';		
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
	
	public function &getCoupons(array $params)
	{
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$pageSize = isset($params['page_size']) ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$poiId = (int)$params['poi_id'];
		$result = array(
			'rows' => array(),
			'count' => 0
			);
		
		$sql = "SELECT n.nid, n.poi_id, n.title, n.content, n.dateline, n.image_url, n.uid, n.begintm, n.endtm, n.is_top, n.sms_no, n.sms_content, n.url, n.phone
						,p.name
					FROM ".BETTER_DB_TBL_PREFIX."poi_notification AS n
						LEFT JOIN ".BETTER_DB_TBL_PREFIX."poi AS p
							ON p.poi_id=n.poi_id AND p.closed=0
					WHERE n.poi_id=".$poiId." AND n.checked=1 AND n.begintm<".time()." AND n.endtm>".time()."
					ORDER BY n.is_top DESC
					LIMIT ".($page*$pageSize+1)."
		";

		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$result['rows'][] = $row;
		}
		$result['count'] = count($result['rows']);
		
		return $result;
	}

	/**
	 * 搜索促销信息
	 * 
	 * @param $param
	 * @return array
	 */
	public function &search(array $param)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);

		$lon = (float)$param['lon'];
		$lat = (float)$param['lat'];
		$range = (int)$param['range'];
		$keyword = $param['keyword'];
		$limit = (int)$param['limit'];
		$major = (int)$param['major'];
		$page = (int)$param['page'];
		$count = (int)$param['count'];
		$pois = $param['poi_id'] ? (array)$param['poi_id'] : array();
		$certified = isset($param['certified']) ? (int)$param['certified'] : null;
		$isTop = isset($param['is_top']) ? (int)$param['is_top'] : null;

		$select = $this->rdb->select();
		
		if (strlen($keyword)) {
			$select->where('p.name LIKE ?', '%'.$keyword.'%');
		}

		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			//$select->where(new Zend_Db_Expr("MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))"));
			$select->where(new Zend_Db_Expr("(X(p.xy)>$x1 AND X(p.xy) < $x2 ) AND (Y(p.xy)>$y2 AND Y(p.xy)<$y1 )"));
	
		}
		
		if ($major) {
			$select->where('p.major=?', $major);
		}
		
		if ($isTop!=null) {
			$select->where('n.is_top=?', $isTop);
		}
		
		if ($certified!=null) {
			$select->where('p.certified=?', $certified ? 1 : 0);
		}
		
		if (count($pois)>0) {
			$select->where('p.poi_id IN (?)', $pois);
		}
		
		$select->where('n.begintm<?', time());
		$select->where('n.endtm>?', time());
		$select->where('n.checked=?', 1);
		$select2 = clone($select);
		$select2->from($this->tbl.' AS n', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select2->joinleft(BETTER_DB_TBL_PREFIX.'poi AS p', 'p.poi_id=n.poi_id', array());

		$rs = self::squery($select2, $this->rdb);
		$row = $rs->fetch();
		$result['total'] = $row['total'];
		
		if ($result['total']>0) {
			
			if ($page && $count) {
				$select->limitPage($page, $count);	
			} else {
				$select->limit(BETTER_MAX_LIST_ITEMS);
			}

			$select->from($this->tbl.' AS n', array(
				'n.nid', 'n.title', 'n.content','n.poi_id', 'n.image_url', 'n.sms_no', 'n.sms_content', 'n.phone', 'n.url', 'n.is_top'
				));
				
			$joinSelected = array(
				 'p.category_id', 'p.name', 'p.major', 'p.major_change_time', new Zend_Db_Expr('X(`p`.`xy`) AS x'), new Zend_Db_Expr('Y(`p`.`xy`) AS y'), 'p.address', 'p.city', 'p.creator', 'p.create_time', 'p.checkins', 'p.favorites', 'p.users', 
				);
			if ($lon && $lat) {
				$joinSelected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
				$select->order('distance ASC');
			} else { 
				$select->order('n.nid DESC');
			}
			
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi AS p', 'p.poi_id=n.poi_id', $joinSelected);
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			$result['rows'] = &$rows;
			
		}

		return $result;
	}
	
	public function getCheckedCount($poiId)
	{
		$sql = "SELECT COUNT(*) AS count FROM `".$this->tbl."` WHERE poi_id=".((int)$poiId)." AND checked=1 AND begintm<".time()." AND endtm>".time();
		$rs = self::squery($sql, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['count'];
	}
		
	public function getLastest($poiId)
	{
		if(Better_Config::getAppConfig()->market->wlan->switch){		
			$wlanpoi = Better_Market_Cmcc::getInstance()->poilist();
			$poilist = array();
			foreach($wlanpoi as $row){
				foreach($row as $rows){
					$poilist[]= $rows;
				}	
			}
			if ( in_array($poiId, $poilist) ) {
				$is_event = true;
				$params['poi_id'] = $poiId;
				$datapolo = Better_DAO_Poi_Notificationpolo::getInstance()->getPoispecial($params);
				$row = $datapolo['rows'][0];
				
				return $row;
			}
		}	
		
		$sql = "SELECT * FROM `".$this->tbl."` WHERE poi_id=".intval($poiId)." AND checked=1 AND begintm<".time()." AND endtm>".time()." ORDER BY `dateline` DESC LIMIT 1";
		$rs = self::squery($sql, $this->rdb);
		$row = (array)$rs->fetch();
		
		return $row;
	}
	
	public function getInfo($nid)
	{		
		$sql = "select poi.*,special.*,o.owner from ".BETTER_DB_TBL_PREFIX."poi_notification AS special left join " .BETTER_DB_TBL_PREFIX."poi as poi on special.poi_id=poi.poi_id left join (select poi_id,group_concat(owner_id) as owner from better_poi_owner group by poi_id) as o  on o.poi_id=special.poi_id where special.nid=".intval($nid);		
		Better_Log::getInstance()->logInfo($sql,'sqlspecial');
		$rs = self::squery($sql, $this->rdb);
		$row = (array)$rs->fetch();
		
		return $row;
	}
	public function getPoispecial(array $param)
	{	
		$result = array(
			'total' => 0,
			'rows' => array()
			);
		$poi_id = (int)$param['poi_id'];
		$checked = $param['checked'] ? $param['checked'] : 1;
		$sql = "select special.* from ".BETTER_DB_TBL_PREFIX."poi_notification AS special where special.poi_id='".$poi_id."' and checked in(".$checked.")";
		if($checked==1){
			$sql .= " and special.begintm<".time()." AND special.endtm>".time();	
		}
		$sql .= " order by special.nid desc";			
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();		
		$result['total'] = count($rows);		
		$result['rows'] = $rows;	
		return $result;
	}
	
}