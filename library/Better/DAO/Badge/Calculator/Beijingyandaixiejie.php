<?php

/**

  烟袋斜街



 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingyandaixiejie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		if($poiId==78183){
							
			$result = true;			
					
		}
		return $result;
	}
}
?>
