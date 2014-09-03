<?php

/**

  九门提督
签到全部以下地点：正阳门、崇文门、宣武门、东直门、朝阳门、阜成门、安定门、德胜门、西直门
899256、10296704、14391490、19081630、19081633、19081634、19081635、79580、19081631



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingjiumengtidu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		
		$poilist = array(899256,10296704,14391490,19081630,19081633,19081634,19081635,79580,19081631);	
			
		if(in_array($poiId,$poilist)){			
			$total = 0;
			foreach($poilist as $row){
				$temppoi_id = $row;
				$temppoi_info = Better_Poi_Info::getInstance($temppoi_id)->getBasic();	
				if($temppoi_info['major']==$uid){
					$total = $total +1;
				}
			}			
			
			if($total==9){
				$result = true;	
			}							
		}
		
		return $result;
	}
}
?>