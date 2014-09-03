<?php

/**
 * 附近更多微博
 * 
 * @package Better.DAO.Poi
 * @author yangl
 */

class Better_DAO_Roundmore_Weibo extends Better_DAO_Base implements Better_DAO_Roundmore_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_weibo';
		$this->orderKey = 'dateline';
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
	 * 获得微博信息
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

		$select = $this->rdb->select();
		

		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$select->where(new Zend_Db_Expr("MBRWithin(`w`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))"));
		}
		
		
		$select2 = clone($select);
		$select2->from($this->tbl.' AS w', array(
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
			
			$select->order('w.dateline DESC');
			
			$select->from($this->tbl.' AS w', array('*'));
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			$result['rows'] = &$rows;
			
		}

		return $result;
	}
	
	
	
}