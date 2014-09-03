<?php

/**
 * 8月15日上午10:00到18:00在下面POI作有效签到，可以得到social beta勋章
 * http://www.k.ai/poi?id=447193
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Socialbeta extends Better_DAO_Badge_Calculator_Base
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
		$start = gmmktime(4, 0, 0, 9, 12, 2010);
		$end = gmmktime(12, 0, 0, 9, 12, 2010);
		$now = time();
		
		if (APPLICATION_ENV=='production') {
			if ($now<=$end && $now>=$start && $poiId==201933) {
				$result = true;
			}
		} else {
			$month = intval(date('m', time()+$offset));
			$day = intval(date('d', time()+$offset));		

			if ($poiId==122660) {
				$result = true;
			}
		}
		
		return $result;
	}
}