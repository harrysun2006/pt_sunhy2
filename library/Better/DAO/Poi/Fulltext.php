<?php

/**
 * POI全文检索数据
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Fulltext extends Better_DAO_Poi_Base
{
	private static $instance = null;
	public static $fulltextKeys = array(
		'name', 'address', 'city', 'major', 'phone', 'category_id', 'label', 'intro', 'lon', 'lat', 'level', 'bonus', 'closed', 'x', 'y'
		);
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_index';
		$this->priKey = 'poi_id';
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
	
	public function updateItem($id, $type)
	{
		if ($type==2) {
			$row = array(
				'x' => 0,
				'y' => 0,
				'poi_id' => $id
				);
		} else {
			$sql = "SELECT *, X(xy) AS x, Y(xy) AS y FROM `".BETTER_DB_TBL_PREFIX."poi` WHERE `poi_id`='".$id."'";
			$rs = self::squery($sql, $this->rdb);
			$row = $rs->fetch();
			
			if ($type==1) {
				Better_Cache::remote()->set('kai_poi_'.$id, null);
			}
		}
		
		if ($row['poi_id']) {
			Better_Log::getInstance()->logInfo($id.'|'.$type, 'ft_update', true);
			$sql = "REPLACE INTO `".$this->tbl."` (
				`act_type`, 
				`poi_id`,
				`xy`
			) 
			VALUES (
				'".$type."', 
				'".((int)$id)."',
				geomfromtext(\"point({$row['x']} {$row['y']})\")
			)
			";	
						
		}
		return $this->wdb->query($sql);
	}
	
	public function joinInsertedCount(array $params)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);

		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		$keyword = $params['keyword'];
		$page = 1;
		$count = 20;

		$select = $this->rdb->select();
		
		if (strlen($keyword)) {
			$where = ' `p`.`name` '.$this->rdb->quoteInto('LIKE ?', '%'.$keyword.'%');
			$select->where($where);
		}

		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$range || $range = 5000;
			$x1 = $x-$range;
			$y1 = $y+$range;
			$x2 = $x+$range;
			$y2 = $y-$range;

			//$sql = "MBRWithin(p.xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			$sql = " X(p.xy) BETWEEN $x1 AND $x2 AND Y(p.xy) BETWEEN $y2 and $y1";
			$expr = new Zend_Db_Expr($sql);
			$select->where($expr);
		}
		
		$select->where('p.closed=?', 0);
		
		$selected = array(
			new Zend_Db_Expr('COUNT(*) AS total')	
			);
			
		$select->from($this->tbl.'_view AS p', $selected);
		$select->where('p.act_type=?', 0);
		$select->where('p.creator>?', 0);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result = (int)$row['total'];

		return $result;		
	}
	
	/**
	 * 
	 * 获取新增的poi（还没有来得及进入全文搜索的索引的）
	 * 
	 * @return array
	 */
	public function joinInserted(array $params)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);

		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		$keyword = $params['keyword'];
		$minDist = isset($params['min_dist']) ? (int)$params['min_dist'] : null;
		$maxDist = isset($params['max_dist']) ? (int)$params['max_dist'] : null;
		$page = 1;
		$count = 20;

		$select = $this->rdb->select();
		
		if (strlen($keyword)) {
			$where = ' `p`.`name` '.$this->rdb->quoteInto('LIKE ?', '%'.$keyword.'%');
			$select->where($where);
		}

		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$range || $range = 5000;
			$x1 = $x-$range;
			$y1 = $y+$range;
			$x2 = $x+$range;
			$y2 = $y-$range;

			//$sql = "MBRWithin(p.xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			$sql = " X(p.xy) BETWEEN $x1 AND $x2 AND Y(p.xy) BETWEEN $y2 and $y1";
			$expr = new Zend_Db_Expr($sql);
			$select->where($expr);
		}
		
		$select->where('p.closed=?', 0);
		$select->order('p.create_time DESC');
		
		$selected = array(
			'p.poi_id', 'p.category_id', 'p.name', 'p.major', 'p.major_change_time', new Zend_Db_Expr('X(p.xy) AS x'), new Zend_Db_Expr('Y(p.xy) AS y'), 'p.address', 
			'p.city', 'p.creator', 'p.create_time', 'p.checkins', 'p.favorites', 'p.users', 'p.posts', 'p.tips', 'p.visitors',
			'p.province', 'p.country', 'p.phone', 'p.certified', 'p.logo', 'p.intro',			
			);
		if ($lon && $lat) {
			$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			if ($minDist) {
				$select->where("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")')))))>=?", $minDist);
			}
			
			if ($maxDist) {
				$select->where("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")')))))<=?", $maxDist);
			}
		}

		$select->from($this->tbl.'_view AS p', $selected);
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'c.category_id=p.category_id', array(
			'c.category_image', 'c.category_name', 'c.tags',
			));
		$select->where('p.act_type=?', 0);
		$select->where('p.creator>?', 0);
		$select->order('p.id DESC');

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		$result['rows'] = &$rows;

		return $result;
	}
}
