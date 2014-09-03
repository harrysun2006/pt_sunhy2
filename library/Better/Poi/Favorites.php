<?php

/**
 * POI收藏
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Favorites extends Better_Poi_Base
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
	public static function getInstance($poiId)
	{
		if (!isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}	
		
	/**
	 * 获取本POI被哪些人收藏
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function getUsers($page=1, $count=20)
	{
		
	}
}