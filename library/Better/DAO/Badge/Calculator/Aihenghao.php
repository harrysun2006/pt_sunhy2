<?php

/**
签到世博庆典广场+2SNS

19089278
 
上线时间
 8月2日即时
 
下线时间
 8月7日0:00am
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Aihenghao extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 2, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 6, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==19089278){						
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=2) {			
				$result = true;				
			}									
		}
		return $result;
	}
}