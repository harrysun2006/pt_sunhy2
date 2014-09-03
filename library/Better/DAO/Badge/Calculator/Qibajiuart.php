<?php

/**
789艺文节
签到789艺文节北大站（http://k.ai/poi?id=19073326）或789艺文节北师站（http://k.ai/poi?id=19073336）

2011年5月19日
2011年5月24日24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qibajiuart extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 18, 2011);
		$endtm = gmmktime(17, 0, 0, 5, 24, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && ($poiId==19073326 || $poiId==19073336)){						
			$result = true;					
		}
		return $result;
	}
}