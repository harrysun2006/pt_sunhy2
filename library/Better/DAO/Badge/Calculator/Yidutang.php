<?php

/**
 * 一渡堂
 *在一渡堂艺术空间http://k.ai/poi?id=1151421 做有效签到，即可获得此枚勋章，无时间限制。

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yidutang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		/*
		if ($poiId==1151421) {
			$result = true;
		}
		*/		
		return $result;
	}
}