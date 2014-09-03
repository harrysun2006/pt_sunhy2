<?php

/**
 * 嘉禾
2010年12月22日中午12点至2011年1月15日晚24点
 * 签到POI http://k.ai/poi/19052023 即可得

 *
        
 * 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Szjiahe extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];		
		$end = gmmktime(16, 0, 0, 1, 15, 2011);
		$now = time();	
			
		if ($now<=$end && $poiId==19052023) {			
			$result = true;			
		}

		return $result;
	}
	
}