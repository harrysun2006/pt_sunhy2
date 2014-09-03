<?php

/**

  玩物不丧志

签到以下两处：潘家园旧货市场7105049+琉璃厂文化市场302707

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingwanwubushanzhi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poilist = array(7105049,302707);					
		if(in_array($poiId,$poilist)){
			$temppoi = array();
			foreach($poilist as $row){
				if($poiId!=$row){
					$temppoi[] = $row;
				}
			}
			$temptimes = Better_User_Checkin::getInstance($uid)->checkinedPoisTimes($temppoi[0]);					
			if($temptimes>0){
				$result = true;
			}
		}
		return $result;
	}
}
?>