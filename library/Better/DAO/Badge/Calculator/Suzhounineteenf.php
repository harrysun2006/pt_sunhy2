<?php

/**
 * 苏州19楼
 *活动上线时间---12月27日0时结束  POI：http://k.ai/poi/180053
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Suzhounineteenf extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];			
		$end = gmmktime(16, 0, 0, 12, 26, 2010);
		$now = time();			
		if ($now<=$end && $poiId==180053) {
			$result = true;
		}
		
		return $result;
	}
}