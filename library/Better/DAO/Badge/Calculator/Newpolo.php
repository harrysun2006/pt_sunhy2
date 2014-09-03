<?php

/**
 * new polo勋章
 * 获得条件：新的就是2月10日15:00--2月22日23:59:59 在25个活动poi签到就能拿
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Newpolo extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];	
		$config = Better_Config::getAppConfig();		
		$poi_list_str = $config->market->polo->poi->food.",".$config->market->polo->poi->film;
		$poi_list = split(",",$poi_list_str);
		$start = gmmktime(7, 0, 0, 2, 10, 2011);
		$end = gmmktime(16, 0, 0, 2, 22, 2011);		
		//$now = time();	
		$now = 0;
		if ($now>=$start && $now<=$end && in_array($poiId,$poi_list)) {			
			$result = true;			
		}

		return $result;
	}
	
}