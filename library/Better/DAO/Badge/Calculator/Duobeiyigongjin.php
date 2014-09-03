<?php

/**
 
多背一公斤

1.签到深圳会展中心（2011公益深交会）http://k.ai/poi?id=548161
2，发布一条吼吼，吼吼中必须带有 公益 这2个字，就能获得“多背一公斤”勋章
 上线时间 2011年3月4日 8:00 
下线时间 P 2011年3月6日 24:00 

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Duobeiyigongjin extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$begintm = gmmktime(0, 0, 0, 3, 4, 2011);
		$endtm = gmmktime(16, 0, 0, 3, 6, 2011);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$poi_id = 548161;
		if ($now>=$begintm && $now<=$endtm && $poiId==$poi_id) {
			$blog = &$params['blog'];
			if ($blog['type']=='normal') {
				$message = $blog['message'];				
				if (preg_match('/公益/', $message)) {					
					$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);	
					$sql = "SELECT COUNT(*) AS total
								FROM ".BETTER_DB_TBL_PREFIX."user_place_log 
								WHERE uid='".$uid."' and poi_id =".$poi_id." and checkin_time>=".$begintm." and checkin_time<=".$endtm;
					$rslog = self::squery($sql, $rdb);
					$rows = $rslog->fetch();					
					if ($rows['total']>=1) {
						$result = true;
					}			
				}
			}
			
			
		}

		return $result;
	}
	
}