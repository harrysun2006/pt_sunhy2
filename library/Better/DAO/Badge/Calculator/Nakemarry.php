<?php

/**
 * 裸婚勋章
 * 从7月22日中午12点，到8月2日上午10点为止，在此POI签到http://www.k.ai/poi?id=326247
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Nakemarry extends Better_DAO_Badge_Calculator_Base
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
		$start = gmmktime(4, 0, 0, 7, 22, 2010);
		$end = gmmktime(2, 0, 0, 8, 2, 2010);
		$now = time();
		
		if ($now<=$end && $now>=$start && $poiId==326247) {
			$result = true;
		}
		
		return $result;
	}
}