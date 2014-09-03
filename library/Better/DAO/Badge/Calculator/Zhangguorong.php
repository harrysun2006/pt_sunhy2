<?php

/**
 
签到时吼出“张国荣”/“哥哥”，或吼吼中（有无勾选地点均可）含有“张国荣”/“哥哥”关键词。
4月1日（五）

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Zhangguorong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$begintm = gmmktime(16, 0, 0, 3, 31, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 2, 2011);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($now>=$begintm && $now<=$endtm) {
			$blog = &$params['blog'];
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = $blog['message'];		
				$checked1 = '/张国荣/';	
				$checked2 = '/哥哥/';	
				if (preg_match($checked1, $message) || preg_match($checked2, $message)) {					
						$result = true;
				}
			}
			
			
		}

		return $result;
	}
	
}