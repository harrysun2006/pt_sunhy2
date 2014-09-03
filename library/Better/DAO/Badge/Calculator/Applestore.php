<?php

/**
 * AppleStore
 * 开儿们有福啦！之前没有拿到Apple Store勋章的开儿，9月25日又有机会拿啦！
 * 9月25日8:00~24:00在http://k.ai/poi?id=1006582上海香港广场店和http://k.ai/poi?id=1006571北京西单大悦城店 签到，就可以获得勋章哦！快去抢吧~
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Applestore extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		return false;
		
		parent::touch($params);
		$result = false;
		$config = Better_Config::getAppConfig();
		$range = $config->app_store->badge->range;
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$start = gmmktime(0, 0, 0, 9, 25, 2010);
		$end = gmmktime(16, 0, 0, 9, 25, 2010);
		$now = time();		
		
		if ($now<=$end && $now>=$start && ($poiId==1006582 || $poiId==1006571)) {
			$result = true;
		}
		
		return $result;
	}
}