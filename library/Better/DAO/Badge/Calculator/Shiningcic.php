<?php

/**
 * 网络光芒
 * 8月3日12:00~19日22:00，在该POIhttp://www.k.ai/poi?id=327155有效签到即可获得此勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shiningcic extends Better_DAO_Badge_Calculator_Base
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
		$start = gmmktime(4, 0, 0, 8, 3, 2010);
		$end = gmmktime(14, 0, 0, 8, 19, 2010);
		$now = time();
		
		if ($now<=$end && $now>=$start && $poiId==327155) {
			$result = true;
		}
		
		return $result;
	}
}