<?php

/**
 
勋章名称	汶川记忆
获得条件	吼吼（无论是否带地点）中含有“汶川”或“512”关键词
上线时间	即时
下线时间	2011年5月13日

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Wenchuanjiyi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$begintm = gmmktime(16, 0, 0, 5, 11, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 13, 2011);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($now>=$begintm && $now<=$endtm) {
			$blog = &$params['blog'];
			if ($blog['type']=='normal') {
				$tempmessage1 = Better_Filter::make_semiangle($blog['message']);
				$message = strtolower($tempmessage1);		
				$checked1 = '/汶川/';	
				$checked2 = '/512/';	
				if (preg_match($checked1, $message) || preg_match($checked2, $message)) {					
						$result = true;
				}
			}
			
			
		}

		return $result;
	}
	
}