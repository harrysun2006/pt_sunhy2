<?php

/**

  夜观天象



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingyeguantianxiang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		if($poiId==79293){
						
			$result = true;			
					
		}
		return $result;
	}
}
?>