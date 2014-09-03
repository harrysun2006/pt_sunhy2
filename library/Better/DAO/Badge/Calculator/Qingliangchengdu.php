<?php

/**
清凉成都
签到南湖梦幻岛：http://k.ai/poi/1597665

2011年7月14日
2011年8月7日



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qingliangchengdu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(1, 0, 0, 7, 14, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 7, 2011);
		$now = time();		
		$poilist = array(1597665);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
				
			$result = true;			
						
		}
		return $result;
	}
}