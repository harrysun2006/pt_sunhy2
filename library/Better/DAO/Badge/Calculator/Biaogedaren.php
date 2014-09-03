<?php

/**
 * 飙歌达人
 * 在以下3个POI任意一个签到，同时实际位置与签到的POI的距离不超过5KM（截止时间为2010.11.30）
 * 西部飚歌城  http://k.ai/poi/122834
音乐空间站  http://k.ai/poi/26747
水果音乐广场 http://k.ai/poi/26725
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Biaogedaren extends Better_DAO_Badge_Calculator_Base
{
	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(16, 0, 0, 11, 29, 2010);
		$now = time();	
		if ($now<=$end && ($poiId==122834 || $poiId==26747 || $poiId==26725)) {	
			$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
			list($temp['lon'], $temp['lat']) = Better_Functions::XY2LL($x, $y);
			$result = Better_Service_Lbs::getDistance($poiInfo['lon'],$poiInfo['lat'],$temp['lon'],$temp['lat'])<=5000 ? true : false;	
		}
		return $result;
	}
}