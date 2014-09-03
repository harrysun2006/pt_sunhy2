<?php

/**
 * 打飞的
 * 你在机场签到过5次
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Dafeidi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$flag = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$poiId = (int)$params['poi_id'];
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$key = '机场';
		
		if ($poiInfo['certified']==1 && (preg_match('/'.$key.'/', $poiInfo['name']) || preg_match('/'.$key.'/', $poiInfo['label']))) {
			$flag = true;
		} else if ($poiInfo['certified']==0 && preg_match('/'.$key.'/', $poiInfo['label'])) {
			$flag = true;
		}			

		if ($flag==true) {
			$_checkinPoiIds = self::getUserCheckinPoiIds($uid);
			$badgePoiIds = self::getBadgePoiIds($key, __CLASS__, $_checkinPoiIds);
			$count = self::getCountByPoiIds($uid, $badgePoiIds);
			
			if ($count>=5) {
				$result = true;
			}
		}
		
		return $result;
	}
}