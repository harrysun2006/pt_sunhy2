<?php

/**
 * 用户的POI收藏
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_PlaceLog extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid="")
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 取得某一POI的签到排行
	 */
	public function users($poiID,$count)
	{
		$return = Better_DAO_User_PlaceLog::getInstance($this->uid)->getValidCheckinRang($poiID,$count);
		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = Better_User::getInstance($row->uid)->parseUser($row, false, false, true);
		}
		return $return;
	}
	/**
	 * 
	 */
	public function friends($poiID,$page=1,$count)
	{
		$cacheKey = 'friends_checkin_list_uid_'.$this->uid.'_poiid_'.$poiID.'_page_'.$page;
		$cacher = Better_Cache::remote();
		if($cacher->test($cacheKey)){
			$return = $cacher->get($cacheKey);
		}else{			
			$return = Better_DAO_User_PlaceLog::getInstance($this->uid)->getFriendsCheckin($poiID,$page,$count,$this->uid);
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = Better_User::getInstance($row->uid)->parseUser($row, false, false, true);
			}
			$cacher->set($cacheKey,$return,180);
		}
		return $return;
	}
	/**
	 * 某人对所有POI收藏
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=BETTER_PAGE_SIZE)
	{
		$results = Better_DAO_User_PoiFavorites::getInstance($this->uid)->getFavoritesWithDetail($page, $count);

		return $results;
	}
}