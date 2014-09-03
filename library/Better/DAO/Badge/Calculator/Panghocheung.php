<?php

/**
 * 彭浩翔勋章
 * 在下面的POI签到（截止时间为2010.11.11）
 * http://k.ai/poi/135690  

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Panghocheung extends Better_DAO_Badge_Calculator_Base
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
		$end = gmmktime(16, 0, 0, 11, 11, 2010);
		$now = time();	
		if ($now<=$end && $poiId==135690) {	
			$result = true;
		}
		return $result;
	}
}