<?php

/**

  冰河世纪

签到地名含“滑雪场”满2次

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingbingheshiji extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$keyname  = '滑雪场';		
		if(preg_match('/'.$keyname.'/', $poiInfo['name'])){
			$checkinpoilist = Better_User_Checkin::getInstance($uid)->checkinedPois(1,1000);
			$totaltimes = 0;
			foreach($checkinpoilist['rows'] as $row){
				$temppoi_id = $row['poi_id'];
				$temppoi_info = Better_Poi_Info::getInstance($temppoi_id)->getBasic();
				if(preg_match('/'.$keyname.'/', $temppoi_info['name'])){	
					$sql = "select count(*) as total from better_user_place_log where uid=".$uid." and poi_id=".$temppoi_id." and checkin_score>0";		
					$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
					$rs = Better_DAO_Base::squery($sql, $rdb);			
					$data = $rs->fetch();		
					$totaltimes = $totaltimes +$data['total']; 					
				}
				if($totaltimes>=2){
					$result = true;
					break;
				}
			}
		}			
		return $result;		
	}
}
?>