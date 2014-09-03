<?php

/**
灿都餐吧
签到灿都餐吧http://k.ai/poi?id=19092223

2011年8月24日
2011年11月30日


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Canducanba extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 8, 24, 2011);
		$endtm = gmmktime(16, 0, 0, 11, 30, 2011);
		$now = time();		
		$poilist = array(19092223);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){							
			$result = true;					
		}
		return $result;
	}
}