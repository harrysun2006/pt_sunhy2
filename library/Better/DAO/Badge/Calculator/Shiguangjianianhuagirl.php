<?php

/**
签到木马剧场http://k.ai/poi/1364607+2SNS，且性别设定为女
 
上线时间
 8月3日即时
 
下线时间
 8月15日0:00am
 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shiguangjianianhuagirl extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 3, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 14, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==1364607){						
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=2) {	
				$user = Better_User::getInstance($uid);
				$userinfo = $user->getUserInfo();
				if($userinfo['gender']=='female'){		
					$result = true;		
				}		
			}									
		}
		return $result;
	}
}