<?php

/**
 * POI搜索日志
 * 
 * @package Better.Poi.Search
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Poi_Search_Log
{
	protected static $instance = null;
	private $dao = null;
	
	private function __construct()
	{
		$this->dao = Better_DAO_Poi_Search_Log::getInstance();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}	
		
		return self::$instance;
	}
	
	public function log(array $params)
	{
		$keyword = trim($params['keyword']);
		if (Better_LL::isValidLL($params['lon'], $params['lat']) && trim($keyword)!='' && !Better_Filter::getInstance()->filterBanwords($keyword)) {
			list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
			$id = $this->dao->insert(array(
				'uid' => $params['uid'],
				'x' => $x,
				'y' => $y,
				'dateline' => time(),
				'keyword' => $keyword,
				'results' => (int)$params['results'],
				'range' => (int)$params['range']
				));
			return $id;	
		}
	}
	
	public function logEmpty(array $params)
	{
		$keyword = trim($params['keyword']);
		if (Better_LL::isValidLL($params['lon'], $params['lat']) && trim($keyword)!='' && !Better_Filter::getInstance()->filterBanwords($keyword)) {
			list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
			Better_DAO_Poi_Search_Log2::getInstance()->insert(array(
				'uid' => $params['uid'],
				'x' => $x,
				'y' => $y,
				'dateline' => time(),
				'keyword' => $keyword,
				'results' => (int)$params['results'],
				'range' => (int)$params['range']
				));
		}
	}
	
	public function &nearbyKeywords(array $params)
	{
		$results = array();
			
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		$range || $range = 5000;
		
		$results = $this->dao->nearbyKeywords(array(
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			));
		
		return $results;
	}
}