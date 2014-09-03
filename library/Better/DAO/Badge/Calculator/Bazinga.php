<?php

/**
 
勋章名称
 Bazinga！
 
获得条件
 在签到的同时吼出“Bazinga”或者吼吼中（有无勾选地点都可）含有“Bazinga”关键词可以获得。

注明：该关键词不区分大小写，即“BAZINGA“、”bazinga“、”Bazinga“等写法均有效
 
上线时间
 2010年4月1日0:00
 
下线时间
 2010年4月1日24:00
 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Bazinga extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$begintm = gmmktime(16, 0, 0, 3, 31, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 1, 2011);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($now>=$begintm && $now<=$endtm) {
			$blog = &$params['blog'];
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = strtolower($blog['message']);		
				$checked = '/bazinga/';		
				if (preg_match($checked, $message)) {					
						$result = true;
				}
			}
			
			
		}

		return $result;
	}
	
}