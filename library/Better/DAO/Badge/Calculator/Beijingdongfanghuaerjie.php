<?php

/**

  东方华尔街

签到金融街19047199

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingdongfanghuaerjie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poilist = array(19047199);		
		if(in_array($poiId,$poilist)){						
			$result = true;					
		}
		return $result;
	}
}
?>