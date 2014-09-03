<?php

/**
20110806 24:00
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qixiqingrenjie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 5, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 6, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm ){				
			$blog = &$params['blog'];			
			$message = strtolower($blog['message']);											
			$checkinfo1 = array('/七夕/');			
			foreach($checkinfo1 as $row){						
				if (preg_match($row, $message)){
					$result = true;	
					break;
				}	
			}											
		}
		return $result;
	}
}