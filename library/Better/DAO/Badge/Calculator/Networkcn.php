<?php

/**

 

上线：8月22日     12:00；下线：8月25日    24:00 
获得条件 签到 POI ID: 19087940

，并至少同步到一个SNS 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Networkcn extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(0, 0, 0, 8, 23, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 25, 2011);
		$poilist = array(19087940);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=1) {		
				$result = true;							
			}						
		}
		return $result;
	}
}