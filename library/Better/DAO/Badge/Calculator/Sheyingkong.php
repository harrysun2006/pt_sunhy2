<?php

/**
 * 摄影控
勋章上线时间：即日起-------1月9日24时  

POI：http://k.ai/poi/19052206

 
POI:http://k.ai/poi/19047994 

 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Sheyingkong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];			
		$end = gmmktime(16, 0, 0, 1, 9, 2011);
		$now = time();			
		if ($now<=$end && $poiId==19052206) {
			$result = true;
		}
		
		return $result;
	}
}