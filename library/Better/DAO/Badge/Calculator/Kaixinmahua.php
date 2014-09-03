<?php

/**
 * 开心麻花
 * 特定的时间在如下地点签到获得
 * 10月26-28日
 深圳
 http://k.ai/poi/599630
 
11月5-7日
 南京
 http://k.ai/poi/459925
 
11月12-14日
 武汉
 http://k.ai/poi/121092
 
11月19-21日
 天津
 http://k.ai/poi/874686
MAIL中让修改到403972
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kaixinmahua extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$szstart = gmmktime(16, 0, 0, 10, 25, 2010);
		$szend = gmmktime(16, 0, 0, 10, 27, 2010);
		$szpoi = 599630;
		
		$njstart = gmmktime(16, 0, 0, 11, 4, 2010);
		$njend = gmmktime(16, 0, 0, 11, 6, 2010);
		$njpoi = 459925;
		
		$whstart = gmmktime(16, 0, 0, 11, 11, 2010);
		$whend = gmmktime(16, 0, 0, 11, 13, 2010);
		$whpoi = 121092;
		
		$tjstart = gmmktime(16, 0, 0, 11, 18, 2010);
		$tjend = gmmktime(16, 0, 0, 11, 20, 2010);
		$tjpoi = 403972;
				
		$now = time();
		if($now>=$szstart && $now<=$szend && $poiId==$szpoi){
			$result = true;
		} else if($now>=$njstart && $now<=$njend && $poiId==$njpoi){
			$result = true;
		} else if($now>=$whstart && $now<=$whend && $poiId==$whpoi){
			$result = true;
		} else if($now>=$tjstart && $now<=$tjend && $poiId==$tjpoi){
			$result = true;
		}
		return $result;
	}
}