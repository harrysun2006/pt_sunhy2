<?php

/**
 * 
 * 基础生活
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Better_Market_Event extends Better_Market_Base
{
	private static $_instance = null;
	public $poiIds = array();
	public $distance = 1;
	public $day = 7;
	public $blacklist = array();

	
	private function __construct()
	{
		$config = Better_Config::getAppConfig();
		$pois = $config->market->event;
		
		$this->poiIds = explode(',', $pois);
		$this->distance = $this->distance  * 1000;
		
		if (APPLICATION_ENV!='production') $this->day = $this->day / (36*24); //700s
	}
		
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	
	/**
	 * 检查是否在活动中
	 */
	public function inEvent($id)
	{
		if (in_array($id, $this->poiIds)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 得到活动POI的掌门列表
	 */
	public function getMajor()
	{
		$majors = Better_DAO_Poi_Event::getInstance()->getAll();
		
		$a = array();
		foreach ($majors as $row) {
			$uid = $row['uid'];
			$a[$uid] = 1;
		}
		
		return array_keys($a);
	}
	
	
}