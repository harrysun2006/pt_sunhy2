<?php

/**
 * Better排行榜
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Toplist
{
	
	/**
	 * 最受关注的5个人
	 * 只取比较简单的个人信息了
	 * 
	 * @param $top
	 * @return array
	 */
	public static function &followersTop5()
	{
		/*$cacheKey = 'followersTop5';
		$cacher = Better_Cache::remote();
			
		if ($cacher->test($cacheKey)) {
			$list = $cacher->get($cacheKey);
		} else {			
			$list = Better_DAO_Toplist::followersTop5();
			$cacher->set($cacheKey, $list, 3600*24) ;
		}*/

		//return $list;
		return array();
	}
}