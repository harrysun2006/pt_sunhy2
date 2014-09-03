<?php

/**
 * POI的Todo
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Todo extends Better_Poi_Base
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
	public static function getInstance($poiId)
	{
		if (!isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}

	/**
	 * 返回想来本Poi的用户数, 如果$params中指定了'user', 则个数按me/friend/other细分
	 * Enter description here ...
	 * @param unknown_type $params
	 */
	public function count($params=array())
	{
		return Better_DAO_Todo::count($this->poiId, $params);
	}

	/**
	 * 获得本Poi所有想来的用户
	 * 
	 * @param $page
	 * @param $count
	 * @param $hasAvatar Do the results contain the users who have no avatar in this poi?
	 * @param $type  The type you want to show in this poi
	 * @return array
	 */	
	public function users($page=1, $count=BETTER_PAGE_SIZE, $hasAvatar=false, $params=array())
	{
		$return = Better_DAO_Todo::users($this->poiId, $page, $count, $hasAvatar,$params);
		$user = Better_User::getInstance();

		foreach ($return['rows'] as $k => $row) {
			$return['rows'][$k] = Better_User::getInstance()->parseUser($row, false, false, true);
		}
		return $return;
	}
	
		
	
}