<?php

/**
〖五道杠儿童节〗
精彩活动
吼吼或签到吼吼中含有“儿童节”关键词
5月31日即时
6月1日24:00pm


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Wudaogang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 30, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 1, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){			
				$blog = &$params['blog'];						
				if ($blog['type']=='normal' || $blog['type']=='checkin') {
					$message = strtolower($blog['message']);
					$checked1 = '/儿童节/';						
					if (preg_match($checked1, $message)) {					
						$result = true;			
					}
				}
						
		}
		return $result;
	}
}