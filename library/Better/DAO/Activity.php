<?php

/**
 * 活动
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Activity extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'poi_activity';
    	$this->priKey = 'act_id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		
	}
	
  	public static function getInstance($identifier=0)
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getActivity($id, $browse, $closed=false)
	{
		if ($browse != 'prev' && $browse != 'next') {
			return $this->get($id);
		}

		$select = $this->rdb->select();
		if ($closed) {
			$select->where('checked=0');
		} else {
			$select->where('checked=1');
		}
		if ($browse == 'prev') {
			$select->where('act_id<?', $id);
			$select->order('act_id DESC');
		} else if ($browse == 'next') {
			$select->where('act_id>?', $id);
			$select->order('act_id ASC');
		}
		$select->limit(1);
		$select->from($this->tbl, '*');
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();

		if (!$row) {
			$select->reset(Zend_Db_Select::WHERE);
			if ($closed) {
				$select->where('checked=0');
			} else {
				$select->where('checked=1');
			}
			$rs = self::squery($select, $this->rdb);
			$row = $rs->fetch();
		}

		return $row;
	}
	public function getActivitiesAroundRandom(array $param)
	{
		if (($param['count'] == 1) && ($param['last_id'] > 0)) {
			$result[]  = $this->getActivity($param['last_id'], 'next');
		} else {
			$param['count'] = 15;
			$activities = $this->getActivitiesAround($param);
			$rand_keys = array_rand($activities, 1);
			$result[] = $activities[$rand_keys];
		}
		return $result;
	}
	
	public function getActivitiesAround(array $param)
	{
		$lon = (float)$param['lon'];
		$lat = (float)$param['lat'];
		$range = (int)$param['range'];
		$page = (int)$param['page'];
		$count = (int)$param['count'];

		$select = $this->rdb->select();
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
// 忽略range参数，返回距离最近的活动
//		$x1 = $x-$range;
//		$y1 = $y+$range;
//		$x2 = $x+$range;
//		$y2 = $y-$range;
//		$select->where(new Zend_Db_Expr("MBRWithin(`p`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))"));
		
		$select->where('a.checked=1');
		//$select->where('t.begintm<?', time());
		//$select->where('t.endtm>?', time());

		if ($page && $count) {
			$select->limitPage($page, $count);	
		} else {
			$select->limit(BETTER_MAX_LIST_ITEMS);
		}
		
		$selected = array('*');
		if ($lon && $lat) {
			$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			$select->order('distance ASC');
		} else {
			$select->order('a.act_id DESC');
		}
		
		$select->from($this->tbl.' AS a', $selected);
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_activity_poi AS pa', 'a.act_id=pa.act_id', '');
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi AS p', 'pa.poi_id=p.poi_id','');
		$select->group('a.act_id');
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();

		return $rows;
	}
	
	/**
	 * 地点上的所有活动
	 */
	public function getActivitiesAtPoi(array $params)
	{
		$poi_id = (int)$params['poi_id'];
		$page = (int)$params['page'];
		$count = (int)$params['count'];
		
		$select = $this->rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi'.' AS p','')
			   ->joinleft(BETTER_DB_TBL_PREFIX.'poi_activity_poi'.' AS pa','p.poi_id=pa.poi_id','')
			   ->joinleft($this->tbl.' AS a','pa.act_id=a.act_id','*')
			   ->where('p.poi_id=?', $poi_id)
			   ->where('a.checked=1')
			   ->order('a.act_id DESC');
		if ($page && $count) {
			$select->limitPage($page, $count);	
		} else {
			$select->limit(BETTER_MAX_LIST_ITEMS);
		}
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		return $rows;
	}
	
	/**
	 * 地点上的所有活动总数
	 */
	public function getActivitiesAtPoiCount($poi_id)
	{
		$select = $this->rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi'.' AS p',array(new Zend_Db_Expr('COUNT(*) AS total')));
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_activity_poi'.' AS pa','p.poi_id=pa.poi_id','')
			   ->joinleft($this->tbl.' AS a','pa.act_id=a.act_id','')
			   ->where('p.poi_id=?', $poi_id)
			   ->where('a.checked=1');
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		return $row['total'];
	}
	
	/**
	 * 获得所有活动列表
	 */
	public function getActivities(array $params)
	{
		$closed = $params['closed'];
		$page   = (int)$params['page'];
		$count  = (int)$params['count'];
		$checked = $closed ?  '0' : '1';
		
		$select = $this->rdb->select();
		$select->from($this->tbl)
			   ->where('checked=?',$checked)
			   ->order('act_id DESC');
		if ($page && $count) {
			$select->limitPage($page, $count);	
		} else {
			$select->limit(BETTER_MAX_LIST_ITEMS);
		}

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();

		return $rows;
	}
	
	
	/**
	 * 活动关联的地点
	 */
	public function getAttachedPois($act_id)
	{
		$poi_table = BETTER_DB_TBL_PREFIX.'poi';
		$relate_tasble = BETTER_DB_TBL_PREFIX.'poi_activity_poi';
		$sql = "SELECT p.*, X(p.xy) AS x, Y(p.xy) AS y FROM `".$this->tbl."` a, `$relate_tasble` pa, `$poi_table` p WHERE a.act_id=$act_id AND a.act_id=pa.act_id and pa.poi_id=p.poi_id";
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		
		$return = array();
		foreach($rows as $row){
			$x = $row['x'];
			$y = $row['y'];
			list($row['lon'], $row['lat']) = Better_Functions::XY2LL($x, $y);
			$return[] = $row;
			
		}
		unset($rows);
		
		return $return;
	}
	
	public function getAllactivity(array $params)
	{
		$results = array('count'=>0, 'rows'=>array());			
		$page = $params['page'] ? intval($params['page']) : 1;	
		$page = $page-1;		
		$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;
		$keywords = trim($params['keywords']) ? trim($params['keywords']) : '';
		$what = "1";
		if($keywords){
			$what .= " and (activity.content like '%".$keywords."%'";
		}
		$select = "select activity.* from ".BETTER_DB_TBL_PREFIX."poi_activity AS activity where ".$what."  order by activity.act_id desc  limit ".$page*$pageSize.",".$pageSize;	
		$rs = self::squery($select, $this->rdb);			
		$row = $rs->fetchAll();	
		$selectcount = "select count(*) as t_count from ".BETTER_DB_TBL_PREFIX."poi_activity AS activity where ".$what;
		
		$rscount = self::squery($selectcount, $this->rdb);
		$rowcount = $rscount->fetch();	
		$result['count'] = $rowcount['t_count'];		
		$result['rows'] = $row;		
		return $result;
	}
	
	public function getMaxid()
	{
		$sql = "select max(act_id) as max_id from ".BETTER_DB_TBL_PREFIX."poi_activity";
		$rs = self::squery($sql, $this->rdb);			
		$row = $rs->fetch();
		return $row['max_id'];
	}
}