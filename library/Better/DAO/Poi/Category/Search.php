<?php

/**
 * POI分类搜索
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Category_Search extends Better_DAO_Poi_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
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
	 * 搜索POI分类
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

			$sql = "MBRWithin(p.xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			$expr = new Zend_Db_Expr($sql);
			$select->where($expr);
		}
		
		if ($major) {
			$select->where('p.major=?', $major);
		}
		
		if ($certified!=null) {
			$select->where('p.certified=?', $certified ? 1 : 0);
		}
		
		if (count($pois)>0) {
			$select->where('p.poi_id IN (?)', $pois);
		}

		$select2 = clone($select);
		$select2->from($this->tbl.' AS p', array(
			new Zend_Db_Expr('COUNT(DISTINCT(p.category_id)) AS total')
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
						
			$select->from($this->tbl.' AS p', array(
				new Zend_Db_Expr('DISTINCT(`p`.`category_id`) AS `category_id`')
				));
			$select->join(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'p.category_id=c.category_id', array(
				'c.category_name', 'c.category_image'
				));
			$select->having('p.category_id>?', 0);

			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			$result['rows'] = &$rows;
		}

		return $result;
	}
	
}