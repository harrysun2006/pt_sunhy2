<?php

/**
 * POI店长
 * @author sunhy
 *
 */
class Better_Poi_Owner extends Better_Poi_Base
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
	 * 获得本Poi所有店长
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function owners($page=1, $count=BETTER_PAGE_SIZE)
	{
		$poi = Better_Poi_Info::getInstance($this->poiId);
		$ownerids = is_string($poi->ownerid) ? split(',', $poi->ownerid) : array();
		$oids = array();
		foreach ($ownerids as $ownerid) {
			$oid = intval($ownerid);
			if ($oid <= 0) continue;
			$oids[] = $oid;
		}
		$rows = array();
		if (count($oids) > 0) {
			$rows = Better_DAO_User::getInstance()->getUsersByUids($oids, $page, $count);
			$user = Better_User::getInstance();
			foreach ($rows['rows'] as $k => $row) {
				$rows['rows'][$k] = Better_User::getInstance()->parseUser($row, false, false, true);
			}
		}
		return $rows;
	}
}