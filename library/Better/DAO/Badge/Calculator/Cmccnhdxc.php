<?php

/**
上海移动CMCC WiFi特制勋章（南汇大学城）
签到配置文件中的 南汇大学城的POILIST


 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Cmccnhdxc extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 8, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 30, 2011);
		$cmcc = Better_Market_Cmcc::getInstance()->poilist();		
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId, $cmcc['nhdxc'])){						
			$result = true;		
		}
		return $result;
	}
}