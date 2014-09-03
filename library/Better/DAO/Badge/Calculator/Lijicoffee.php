<?php

/**
李记咖啡
签到李记咖啡馆http://k.ai/poi?id=19090986 

即时
2011年9月25日

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Lijicoffee extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 8, 29, 2011);
		$endtm = gmmktime(16, 0, 0, 9, 25, 2011);
		$now = time();		
		$poilist = array(19090986);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
			$result = true;				
		}
		return $result;
	}
}