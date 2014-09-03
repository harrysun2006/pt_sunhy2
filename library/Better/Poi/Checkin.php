<?php

/**
 * POI入驻
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Checkin extends Better_Poi_Base
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
	 * 获得本Poi所有checkin历史
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=20)
	{
		$params = array(
			'page' => $page,
			'count' => $count,
			'poi' => $this->poiId
			);
		$result = array(
			'rows' => array(),
			'count' => 0,
			);
		
		$tmp = Better_DAO_Blog_Checkin::search($params);
		$rows = &$tmp['rows'];
		$result['count'] = $tmp['count'];
		foreach ($rows as $row) {
			$result['rows'][] = Better_Blog::parseBlogRow($row);
		}
		unset($rows);
		
		return $result;
	}

	/**
	 * 返回本签到过本Poi的用户数, 如果$params中指定了'user', 则个数按me/friend/other细分
	 * 
	 * @param int $poiId
	 * @param array $params
	 */
	public function count($params=array())
	{
		return Better_DAO_Poi_Visitors::count($this->poiId, $params);
	}
	
	
	public function countLog($params=array())
	{
		return Better_DAO_Poi_Visitors::countLog($this->poiId, $params);
	}

	/**
	 * 获得本Poi所有入驻用户
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function users($page=1, $count=BETTER_PAGE_SIZE, $hasAvatar=false, $params=array())
	{
		$return = Better_DAO_Poi_Visitors::search($this->poiId, $page, $count, $hasAvatar, $params);
		$user = Better_User::getInstance();

		foreach ($return['rows'] as $k => $row) {
			$return['rows'][$k] = Better_User::getInstance()->parseUser($row, false, false, true);
		}
		return $return;
	}
	
	
	public static function meetingUsers($page, $count=BETTER_PAGE_SIZE)
	{
		$return = Better_DAO_Poi_Visitors::searchMeeting($page, $count);
		$user = Better_User::getInstance();
		
		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = $user->parseUser($row);
		}
		
		return $return;
	}	
	public function lastcheckin($page=1,$count=12){
		$return = Better_DAO_Poi_Visitors::search($this->poiId, $page, $count);		
		$user = Better_User::getInstance();
		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = $user->parseUser($row);
			$checkinhere = 	Better_DAO_User_PlaceLog::getInstance($return['rows'][$k]['uid'])->getMyCheckinCount($this->poiId);
			$return['rows'][$k]['checkinheretimes'] = $checkinhere['total'];	
			$return['rows'][$k]['checkinhere_time'] = $checkinhere['checkin_time'];	
		}
		return $return;
	}
	public function topcheckin($page=1,$count=3){
		$return = Better_DAO_Poi_Visitors::searchcount($this->poiId, $page, $count);
		//Zend_Debug::dump($return);
		$user = Better_User::getInstance();
		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = $user->parseUser($row);
			$checkinhere = 	Better_DAO_User_PlaceLog::getInstance($return['rows'][$k]['uid'])->getMyCheckinCount($this->poiId);
			$return['rows'][$k]['checkinheretimes'] = $checkinhere['total'];	
			$return['rows'][$k]['checkinhere_time'] = $checkinhere['checkin_time'];	
		}
		
		return $return;
	}
	
	public function checkinhour(){
		$return = Better_DAO_Poi_Visitors::searchtimecount($this->poiId);
		return $return;
	}
	public function checkinday(){
		$return = Better_DAO_Poi_Visitors::searchdatecount($this->poiId);
		return $return;
	}
	public function checkingender(){
		$return = Better_DAO_Poi_Visitors::searchgendercount($this->poiId);	
		return $return;
	}
}