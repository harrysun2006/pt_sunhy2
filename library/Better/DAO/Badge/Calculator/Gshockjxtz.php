<?php

/**
 上线：8月5日上午10:00;下线8月19日晚24:00 
获得条件 签到POI ID 19078558,并同步至任一SNS 

 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gshockjxtz extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 5, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 19, 2011);
		$now = time();	
		$blog = &$params['blog'];		
		if($now>=$begtm && $now<=$endtm && $poiId==19078558 && $blog['attach']){						
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=1) {		
				$result = true;		
						
			}									
		}
		return $result;
	}
}