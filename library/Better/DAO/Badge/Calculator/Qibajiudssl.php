<?php

/**
789•读书沙龙
签到猜火车餐吧http://k.ai/poi?id=1446226并至少同步到搜狐微博

2011年8月25日
2011年9月7日

 *
 */
class Better_DAO_Badge_Calculator_Qibajiudssl extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(1, 0, 0, 8, 25, 2011);
		$endtm = gmmktime(16, 0, 0, 9, 7, 2011);
		$now = time();			
		$poilist = array(1446226);	
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$synclist = Better_User_Syncsites::getInstance($uid)->getSites();
			$result = isset($synclist['sohu.com'])?	true:false;							
		}
		return $result;
	}
}