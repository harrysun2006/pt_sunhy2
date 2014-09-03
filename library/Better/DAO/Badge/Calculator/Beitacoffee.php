<?php

/**
贝塔咖啡
签到“http://k.ai/poi/19060194”+至少同步新浪微博
2011年12月31日24：00






 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beitacoffee extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(1, 0, 0, 8, 9, 2011);
		$endtm = gmmktime(16, 0, 0, 12, 31, 2011);
		$now = time();	
		if($now>=$begtm && $now<=$endtm && $poiId==19060194){					
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if (count($syncnums)>=1) {		
				$result = true;		
						
			}			
		}
		
		return $result;
	}
}