<?php

/**
 * POI： http://k.ai/poi/267323  绿咖啡
 *

 





 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Greenroom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];		
		if($poiId==267323){
			$result = true;
		}		
		return $result;
	}
}