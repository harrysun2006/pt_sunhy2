<?php

/**
 * 附近更多团购
 * 
 * @package Better.DAO.Poi
 * @author yangl
 */

class Better_DAO_Roundmore_Tuangou extends Better_DAO_Base implements Better_DAO_Roundmore_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_tuangou';
		$this->orderKey = 'id';
		$this->priKey = 'id';		
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
	

	/**
	 * 获得团购信息
	 * 
	 * @param $param
	 * @return array
	 */
	public function getAllMsg(array $param)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);

		$lon = (float)$param['lon'];
		$lat = (float)$param['lat'];
		$range = (int)$param['range'];
		$limit = (int)$param['limit'];
		$page = (int)$param['page'];
		$count = (int)$param['count'];
		$poi_id = (int)$param['poi_id'];

		$select = $this->rdb->select();
		

		if (!$poi_id && $lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$select->where(new Zend_Db_Expr("MBRWithin(`t`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))"));
		}
		
		
		if ($poi_id) {
			$select->where('t.poi_id=?', $poi_id);
		}
		
		$select->where('t.expired=?', 0);
		//$select->where('t.begintm<?', time());
		//$select->where('t.endtm>?', time());

		$select2 = clone($select);
		$select2->from($this->tbl.' AS t', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));

		$rs = self::squery($select2, $this->rdb);
		$row = $rs->fetch();
		$result['total'] = $row['total'];
		
		if ($result['total']>0) {
			if ($page && $count) {
				$select->limitPage($page, $count);	
			} else {
				$select->limit(BETTER_MAX_LIST_ITEMS);
			}
			
			$selected = array('*');
			if (!$poi_id && $lon && $lat) {
				$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
				$select->order('distance ASC');
			} else {
				$select->order('t.id DESC');
			}
			
			$select->from($this->tbl.' AS t', $selected);
			
			$joinSelected = array(
				'p.poi_id', 'p.category_id', 'p.name', 'p.major', 'p.major_change_time', new Zend_Db_Expr('X(`p`.`xy`) AS x'), new Zend_Db_Expr('Y(`p`.`xy`) AS y'), 'p.address', 'p.city', 'p.creator', 'p.create_time', 'p.checkins', 'p.favorites', 'p.users', 
			);
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi AS p', 'p.poi_id=t.poi_id', $joinSelected);
			
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'p.category_id=c.category_id', array('category_image'));
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			$result['rows'] = &$rows;
			
		}

		return $result;
	}
	
	
	
	public function insert($data){
		return parent::_insertXY($data, $this->tbl);
	}
	
	
	/**
	 * 根据主键查询
	 * @param $val
	 */
	public function get($val){
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS t', '*');
		$select->where('t.id=?', $val);
		
		$joinSelected = array(
			'p.poi_id', 'p.category_id', 'p.name', 'p.major', 'p.major_change_time', new Zend_Db_Expr('X(`p`.`xy`) AS x'), new Zend_Db_Expr('Y(`p`.`xy`) AS y'), 'p.address', 'p.city', 'p.creator', 'p.create_time', 'p.checkins', 'p.favorites', 'p.users', 
		);
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi AS p', 'p.poi_id=t.poi_id', $joinSelected);
		
		$select->limit(1);
		$result = self::squery($select, $this->rdb);
		
		return $result->fetch();
		
	}
	
	
	
}