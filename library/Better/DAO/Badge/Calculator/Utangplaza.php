<?php

/**
签到悠唐生活广场http://k.ai/poi/9902833

即时
2011年8月23日24：00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Utangplaza extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(0, 0, 0, 7, 26, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 23, 2011);
		$now = time();		
		$poilist = array(9902833);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
							
			$result = true;			
						
		}
		return $result;
	}
}