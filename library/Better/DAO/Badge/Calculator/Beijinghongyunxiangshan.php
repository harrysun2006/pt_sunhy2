<?php

/**

  红韵香山



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijinghongyunxiangshan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		if($poiId==278066){
					
			$result = true;			
			
		}
		return $result;
	}
}
?>