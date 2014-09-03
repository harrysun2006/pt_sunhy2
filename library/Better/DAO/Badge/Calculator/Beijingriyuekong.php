<?php

/**

  日月曌

签到以下两处：北京日坛公园1257396+月坛公园19081451

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingriyuekong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poilist = array(19081451,1257396);					
		if(in_array($poiId,$poilist)){
			$temppoi = array();
			foreach($poilist as $row){
				if($poiId!=$row){
					$temppoi[] = $row;
				}
			}
			$temptimes = Better_User_Checkin::getInstance($uid)->checkinedPoisTimes($temppoi[0]);					
			if($temptimes['total']>0){
				$result = true;
			}
		}
		return $result;
	}
}
?>