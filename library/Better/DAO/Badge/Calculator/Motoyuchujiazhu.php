<?php

/**
 * 〖MOTO呼之欲出家族〗

签到北京/上海/广州[办公写字楼]属性任一POI+任一SNS+吼吼“MOTO
办公 8，文化 6；娱乐：3
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Motoyuchujiazhu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$city = array('/上海/','/北京/','/广州/');
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$poiCity = strtolower($poiInfo['city']);	
		foreach($city as $row){						
			$result = preg_match(strtolower($row), $poiCity);
			if($result){
				$result = true;	
				break;
			}		
		}		
		if($result){
			$cc = new Better_User_Diybadge($params);
			$condition = "CC::had_text(array('/moto/')) && CC::had_syncs(1) && CC::blog_type(array('checkin','normal')) && CC::poi_name(array('/大学/'))";		
			$todo = str_replace('CC::','$cc->',$condition);			
			try{
				$result =  eval("return ".$todo.";");	
			} catch(Exception $e){
				Better_Log::getInstance()->logInfo($todo."--\n".$e,'diybadgeerror');
			}
		}	
			
		return $result;
	}
	
	
	
}