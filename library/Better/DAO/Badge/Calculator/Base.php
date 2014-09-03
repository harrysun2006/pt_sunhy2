<?php

/**
 * 计算器基类
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Base extends Better_DAO_Base
{
	protected static $poiIds = array();
	
	protected static function &getCheckinedPoiIds($uid)
	{
		if (!isset(self::$poiIds[$uid])) {
			self::$poiIds[$uid] = Better_DAO_User_PlaceLog::getInstance($uid)->getCheckinedPoiIds();
		}	
		
		return self::$poiIds[$uid];
	}
	
	protected static function touch(array $params)
	{
		
	}
	
	
	/*
	 * 检查一遍用户签到过的POI
	 * 
	 */
	protected static function &getUserCheckinPoiIds($uid, $timeOffset='')
	{
		$poiIds = array();
		
		$db = Better_DAO_User_PlaceLog::getInstance($uid)->getRdb();
		$sql = "SELECT poi_id FROM better_user_place_log WHERE uid='" .intval($uid) . "' AND checkin_score>0";
		if ($timeOffset) {
			$sql .= " AND checkin_time>".((int)$timeOffset);;
		}
		$rs = self::squery($sql, $db);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$poiIds[] = $row['poi_id'];
		}
		
		return $poiIds;
	}
	
	protected static function &getCountByPoiIds($uid, $poiIds, $timeOffset='')
	{
		$count = 0;
		$poiIds = (array)$poiIds;
		
		$db = Better_DAO_User_PlaceLog::getInstance($uid)->getRdb();
		$sql = "SELECT COUNT(*) AS total FROM better_user_place_log WHERE uid='".intval($uid)."' AND poi_id IN ('".implode("','", $poiIds)."') AND checkin_score>0";
		if ($timeOffset) {
			$sql .= " AND checkin_time>".((int)$timeOffset);;
		}
		$rs = self::squery($sql, $db);
		$row = $rs->fetch();
		$count = (int)$row['total'];
		
		return $count;
	}
	
	protected static function &getBadgePoiIds($key, $cacheKey, $poiIds=array())
	{
		$badgePoiIds = array();
		
		$where_poi = " poi_id IN ('" . implode("','", $poiIds) . "') ";
		
		$db = parent::registerDbConnection('poi_server');
		$badgePoiIds = array();
		
		$sql = "SELECT poi_id FROM better_poi WHERE $where_poi AND ((`label` LIKE '%".$key."%' AND `certified`='0') OR ((`label` LIKE '%".$key."%' OR `name` LIKE '%".$key."%') AND `certified`=1))";
		$rs = self::squery($sql, $db);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$badgePoiIds[] = $row['poi_id'];
		}
		
		return (array)$badgePoiIds;
	}
}