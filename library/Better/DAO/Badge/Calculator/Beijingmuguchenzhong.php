<?php

/**

  暮鼓晨钟



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingmuguchenzhong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$poilist = array(81571,1011149);					
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