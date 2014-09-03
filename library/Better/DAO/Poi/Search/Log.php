<?php

/**
 * POI搜索日志
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Search_Log extends Better_DAO_Poi_Base
{
	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_search_log';
		$this->priKey = 'id';
		$this->orderkey = &$this->priKey;
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
	
	public function insert($params)
	{
		return $this->_insertXY($params);
	}
	
	public function update($params)
	{
		return $this->_updateXY($params);
	}
	
	public function nearbyKeywords(array $params)
	{
		$results = array();
			
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('DISTINCT(keyword) AS keyword'),
			new Zend_Db_Expr('COUNT(keyword) AS search_count'),
			new Zend_Db_Expr('SUM(results) AS total_results'),
			new Zend_Db_Expr('SUM(results)/COUNT(keyword) AS ratio')
			));

		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x-$range/2;
		$y1 = $y+$range/2;
		$x2 = $x+$range/2;
		$y2 = $y-$range/2;
		$sql = "MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		$expr = new Zend_Db_Expr($sql);
		$select->where($expr);
		$expr2 = new Zend_Db_Expr("LENGTH(keyword)>3");
		$select->where($expr2);
		$select->having('total_results>?', 0);
		
		$select->group('keyword');
		$select->order('total_results DESC');
		$select->order('ratio DESC');
		$select->order('search_count DESC');

		$select->limit(20);

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$results[] = $row['keyword'];
		}
		
		return $results;		
	}
	
}