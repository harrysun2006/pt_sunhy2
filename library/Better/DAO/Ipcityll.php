<?php

/**
 * IP取经纬度操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Ipcityll extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'ipcityll';
    	$this->priKey = 'start';
    	$this->orderKey = 'offset';
    	
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getLL($ip)
	{
		$cacher = Better_Cache::remote();
		$cacheKey = 'ip2ll_'.md5($ip);
		$cached = $cacher->get($cacheKey);
		$lon = $lat = 0;

		if ($cached && Better_LL::isValidLL($cached['lon'], $cached['lat'])) {
			$lon = $cached['lon'];
			$lat = $cached['lat'];			
		} else {
			$long = Better_Functions::ip2long($ip);
			
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				'lon', 'lat'
				));
			$select->where('start<=?', $long);
			$select->where('end>=?', $long);
			$select->order('offset ASC');
			$select->limit(1);

			$rs = self::squery($select, $this->rdb);
			$row = $rs->fetch();
			
			$lon = $row['lon'];
			$lat = $row['lat'];
			
			Better_LL::isValidLL($lon, $lat) && $cacher->set($cacheKey, $row);
		}
		
		return array(
			'lon' => $lon,
			'lat' => $lat
			);
	}
		
}
