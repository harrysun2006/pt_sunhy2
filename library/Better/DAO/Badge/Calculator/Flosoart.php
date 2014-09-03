<?php

/**
FLOSO
签到FLOSO鼓楼店http://k.ai/poi?id=17240770
或FLOSO新中关店http://k.ai/poi?id=19077035

即时
2012年6月10日24:00




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Flosoart extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(16, 0, 0, 6, 9, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 10, 2012);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && ($poiId==17240770 || $poiId==19077035)){						
						
			$result = true;			
						
		}
		return $result;
	}
}