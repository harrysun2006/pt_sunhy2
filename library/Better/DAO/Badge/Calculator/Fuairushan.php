<?php

/**
〖父爱如山〗
纪念日
吼吼（有无勾选地点均可）或签到时吼吼含有以下关键词：
“感恩您，爸爸”或“感恩您，父亲”
6月17日
6月20日0:01am
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Fuairushan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 6, 16, 2011);
		$endtm = gmmktime(16, 1, 0, 6, 19, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){			
			$blog = &$params['blog'];						
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = Better_Filter::make_semiangle($blog['message']);		
				$checked1 = Better_Filter::make_semiangle('/感恩您，爸爸/');
				$checked2 = Better_Filter::make_semiangle('/感恩您，父亲/');					
				if (preg_match($checked1, $message) || preg_match($checked2, $message) ) {					
					$result = true;			
				}
			}				
		}
		return $result;
	}
}