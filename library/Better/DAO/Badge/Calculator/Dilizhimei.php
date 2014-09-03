<?php

/**
地理之美2011
签到《华夏地理》杂志社http://k.ai/poi/19090972

2011年8月12日 9：00
2011年8月31日 24:00
精彩活动


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Dilizhimei extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(1, 0, 0, 8, 12, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 31, 2011);
		$now = time();		
		$poilist = array(19090972);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
			$result = true;				
		}
		return $result;
	}
}