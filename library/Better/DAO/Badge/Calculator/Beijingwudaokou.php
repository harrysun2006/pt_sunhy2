<?php

/**

  五道口



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingwudaokou extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$keyname  = '五道口';	
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		if(preg_match('/'.$keyname.'/', $poiInfo['name'])){			
			$result = true;			
		}		
		return $result;
	}
}
?>