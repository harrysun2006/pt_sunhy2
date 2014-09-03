<?php

/**
 * 开开用户，实地签到以下POI可得，无时限：
>           上海美术馆 http://k.ai/poi/369668

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shartmuseun extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		/*
		if ($poiId==369668) {
			$result = true;
		}
		*/		
		return $result;
	}
}