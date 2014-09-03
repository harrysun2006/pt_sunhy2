<?php

/**
 * 08票务网
 * 在这里http://k.ai/poi/565611有效签到，可以获得【08票务网】勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Piaowu08 extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		/*
		$poiId = (int)$params['poi_id'];
		
		if ($poiId==565611) {
			$result = true;
		}
		*/
		return $result;
	}
}