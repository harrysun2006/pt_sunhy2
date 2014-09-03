<?php

/**

  胡同游

签到地名含“胡同”满4次

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijinghutongyou extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$keyname  = '胡同';	
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		if(preg_match('/'.$keyname.'/', $poiInfo['name'])){
			$checkinpoilist = Better_User_Checkin::getInstance($uid)->checkinedPois(1,1000);
			$checkintimes = 0;
			foreach($checkinpoilist['rows'] as $row){
				$temppoi_id = $row['poi_id'];
				$temppoi_info = Better_Poi_Info::getInstance($temppoi_id)->getBasic();				
				if(preg_match('/'.$keyname.'/', $temppoi_info['name'])){
					$temptimes = Better_User_Checkin::getInstance($uid)->checkinedPoisTimes($temppoi_id);								
					$checkintimes = $checkintimes+$temptimes['total'];				
					if($checkintimes>=3){
						$result = true;
						break;
					}			
				}
			}
		}	
		return $result;
	}
}
?>