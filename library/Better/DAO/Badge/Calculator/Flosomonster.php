<?php

/**
FLOSO MONSTER
签到FLOSO鼓楼店http://k.ai/poi?id=17240770
或FLOSO新中关店http://k.ai/poi?id=19077035

即时
2011年6月7日24：00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Flosomonster extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 25, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 7, 2011);
		$now = time();		
		$poilist = array(17240770,19077035);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			
					$result = true;
			
		}
		return $result;
	}
}