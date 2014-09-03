<?php

/**
 * 钲艺廊
 * 在这里http://www.k.ai/poi?id=75640
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Zenyilan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		
		if ($poiId==75640) {
			$result = true;
		}
		
		return $result;
	}
}