<?php

/**
 * 苏州19楼
活动上线时间------12月25日24时结束
 
POI:http://k.ai/poi/19047994 

 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Woaisuda extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];			
		$end = gmmktime(16, 0, 0, 12, 25, 2010);
		$now = time();			
		if ($now<=$end && $poiId==19047994) {
			$result = true;
		}
		
		return $result;
	}
}