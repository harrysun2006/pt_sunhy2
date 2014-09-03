<?php

/**
 * Mao
 * 8月3日12:00起，凡在该POI有效签到过，就可以获得MAO勋章 不设截止时间
 * POI http://www.k.ai/poi?id=356028
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mao extends Better_DAO_Badge_Calculator_Base
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
		$start = gmmktime(16, 0, 0, 9, 16, 2010);
		$end = gmmktime(16, 0, 0, 9, 17, 2010);
		$now = time();
		
		if ($now<=$end && $now>=$start && $poiId==356028) {
			$result = true;
		}
		
		return $result;
	}
}