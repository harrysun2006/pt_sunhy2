<?php

/**
 * 活力岛音乐节
 * 从7月16日早10点，到7月18日晚12点为止，在此POI签到http://www.k.ai/poi?id=262494，就能拿活力岛勋章，不作地理范围限制
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Rockfestival extends Better_DAO_Badge_Calculator_Base
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
		$start = gmmktime(2, 0, 0, 7, 16, 2010);
		$end = gmmktime(16, 0, 0, 7, 18, 2010);
		$now = time();
		
		if ($now<=$end && $now>=$start && $poiId==262494) {
			$result = true;
		}
		
		return $result;
	}
}