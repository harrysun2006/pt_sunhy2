<?php

/**
 * 酷电
 * 在这里http://k.ai/poi?id=514026有效签到，可以获得【CODE酷电】勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kudian extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		
		if ($poiId==514026) {
			$result = true;
		}
		
		return $result;
	}
}