<?php

/**
 * GeoName
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Geoname extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->tbl = 'roadcrosscity';
		$this->priKey = 'city';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('geoname');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getLonLat($city)
	{
		$select = $this->rdb->select();
		
		$select->from('cityname AS c', array(
			'code'
			));
		$select->join($this->tbl.' AS r', 'r.city=c.code', array(
			new Zend_Db_Expr('X(r.xy) AS x'),
			new Zend_Db_Expr('Y(r.xy) AS y')
			));
		$select->where('c.name LIKE ?', '%'.$city.'%');
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		$result = array(
			'lon' => 0,
			'lat' => 0,
			);
		if ($row['x'] && $row['y']) {
			list($lon, $lat) = Better_Functions::XY2LL($row['x'], $row['y']);
			$result['lon'] = $lon;
			$result['lat'] = $lat;
		}
		
		return $result;
	}
	
	public function getGeoname($lon, $lat, $range)
	{
		list($x,$y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x - $range/2;
		$y1 = $y + $range/2;
		$x2 = $x + $range/2;
		$y2 = $y - $range/2;
			
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS rcc', array(
			new Zend_Db_Expr('X(ll) AS lon'),
			new Zend_Db_Expr('Y(ll) AS lat'),
			new Zend_Db_Expr('X(xy) AS x'),
			new Zend_Db_Expr('Y(xy) AS y'),
			'r1', 'r2'
			));
		$select->join('cityname AS cn', 'rcc.city=cn.code', array(
			'cn.name','cn.code'
			));
		$select->where(
			new Zend_Db_Expr("MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2},{$x1} {$y1},{$x2} {$y1},{$x2} {$y2},{$x1} {$y2}))'))")
			);

		$rs = self::squery($select, $this->rdb);
		
		return $rs->fetchAll();
	}
	
	
	public function getCityname($lon, $lat, $range)
	{
		$cityname = '';
		
		list($x,$y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x - $range/2;
		$y1 = $y + $range/2;
		$x2 = $x + $range/2;
		$y2 = $y - $range/2;
			
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS rcc', array(
			new Zend_Db_Expr('X(ll) AS lon'),
			new Zend_Db_Expr('Y(ll) AS lat'),
			new Zend_Db_Expr('X(xy) AS x'),
			new Zend_Db_Expr('Y(xy) AS y'),
			'r1', 'r2', 'city'
			));
		
		$select->where(
			new Zend_Db_Expr("MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2},{$x1} {$y1},{$x2} {$y1},{$x2} {$y2},{$x1} {$y2}))'))")
		);
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		
		$result = $rs->fetch();
		
		if($result){
			$code = $result['city'];
			$code = substr($code, 0, 4).'00';
		
			$select2 = $this->rdb->select();
			$select2->from('cityname AS cn');
			$select2->where('cn.code=?', $code);
			$select2->limit(1);
			$rs = self::squery($select2, $this->rdb);
			$tmp =  $rs->fetch();
			
			if($tmp){
				$cityname = $tmp['name'];
			}
		}
		
		return $cityname;
	}
	
	public function getBigcityname($lon, $lat, $range)
	{
		list($x,$y) = Better_Functions::LL2XY($lon, $lat);
		$x1 = $x - $range/2;
		$y1 = $y + $range/2;
		$x2 = $x + $range/2;
		$y2 = $y - $range/2;
			
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS rcc', array(
			new Zend_Db_Expr('X(ll) AS lon'),
			new Zend_Db_Expr('Y(ll) AS lat'),
			new Zend_Db_Expr('X(xy) AS x'),
			new Zend_Db_Expr('Y(xy) AS y'),
			'r1', 'r2'
			));
		$select->join('cityname AS cn', 'rcc.city=cn.code', array(
			'cn.name','cn.code'
			));
		$select->where(
			new Zend_Db_Expr("MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2},{$x1} {$y1},{$x2} {$y1},{$x2} {$y2},{$x1} {$y2}))'))")
			);
		$select->limit(1);
		$rs = self::squery($select, $this->rdb);
		$date = $rs->fetchAll();			
		$code = substr($date[0]['code'],0,-2)."00";		
		
		$sql = "select name from cityname where code='".$code."'";
		Better_Log::getInstance()->logInfo($code."--**--".$sql,'cityname');
		$endrs = self::squery($sql, $this->rdb);		
		return $endrs->fetchAll();
	}
}