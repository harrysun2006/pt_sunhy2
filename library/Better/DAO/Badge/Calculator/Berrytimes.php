<?php

/**
 * 莓控常来 
 * http://k.ai/poi/1141862   10月1日中午起在这个POI有效签到就能获此勋章，无下线时间限制
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Berrytimes extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$start = gmmktime(0, 0, 0, 10, 3, 2010);
		$end = gmmktime(16, 0, 0, 10, 3, 2010);
		$now = time();
		
		if ($now>=$start && $now<=$end && $poiId==1271264) {
			$result = true;
		}
		
		return $result;
	}
}