<?php

/**
上线：8月6日     10:00；下线： 8月21日   24:00 
获得条件 签到以下POI ID:805904,1324399,480612,17087046,925466,17087727,1522243,9026252,480612,19089590,1173050,19073364  中的任意一个，并至少同步到一个SNS 


 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xinshangshumajie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 6, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 21, 2011);
		$now = time();	
		$poilist = array(1324399,17087046,19073364,805904,381516,683388,312587,245699);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=1) {		
				$result = true;		
						
			}									
		}
		return $result;
	}
}