<?php

/**
〖爱你，妈妈〗
纪念日
吼吼（有无勾选地点均可）或签到时吼吼含有以下关键词：
“我爱你，妈妈”（或“我爱你 妈妈”“我爱你妈妈”）
5月6日即时
5月8日24:00pm


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ainimama extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 5, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 8, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){	
			$blog = &$params['blog'];						
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = strtolower($blog['message']);		
				$checked1 = '/我爱你，妈妈/';	
				$checked2 = '/我爱你妈妈/';	
				$checked3 = '/我爱你 妈妈/';	
				if (preg_match($checked1, $message) || preg_match($checked2, $message) || preg_match($checked3, $message)) {					
					$result = true;			
				}
			}			
		}
		return $result;
	}
}