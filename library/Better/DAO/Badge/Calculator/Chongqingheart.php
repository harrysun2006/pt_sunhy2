<?php

/**
 * 重庆美乐身心灵书友会勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Chongqingheart extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$endTime = gmmktime(16, 0, 0, 9, 27, 2010);
		
		if (time()<$endTime && $poiId==926616) {
			$result = true;
		}
		
		return $result;
	}
}