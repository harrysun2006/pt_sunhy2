<?php

/**
勋章名称：库布里克
勋章活动说明：
方法：签到“http://k.ai/poi/19053165可以获得

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kubulike extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
	
		$poiId = (int)$params['poi_id'];	
			
		if ($poiId==19053165) {
			$result = true;
		}		
		return $result;
	}
}