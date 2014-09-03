<?php

/**
 * POI皇帝计算
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Major extends Better_Poi_Base
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
	 * 计算皇帝
	 * 
	 * @return integer
	 */
	public function calculate($uid)
	{
		return $uid;
	}
	
	public static function &majors(array $params)
	{
		$return = array(
			'rows' => array(),
			'count' => 0,
			'pages' => 0,
			'total' => 0,
			);
			
		$tmp = Better_DAO_Poi_Info::getInstance()->nearbyMajors($params);
		if ($tmp['total']>0 && is_array($tmp['rows']) && count($tmp['rows'])>0) {
			$return['total'] = $tmp['total'];
			$return['count'] = $tmp['total'];
			$return['pages'] = Better_Functions::calPages($tmp['total'], $params['limit']);
			
			$keys = array_flip($tmp['rows']);
			
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($tmp['rows'], 1, $params['limit']);

			foreach ($tmp['rows'] as $k=>$v) {
				/*$tmp = explode('.', $keys[$v['uid']]);
				$v['major_change_time'] = date('Y-m-d H:i:s', $tmp[0]);*/
				$return['rows'][$keys[$v['uid']]] = Better_User::getInstance()->parseUser($v);
			}	
			
			krsort($return['rows']);
		}

		return $return;
	}
	
	
	/**
	 * 首页掌门
	 */
	public static function indexMajors(array $params){
		$return = array();
		$majors = array();
		$rows = Better_DAO_Poi_Info::getInstance()->indexMajors($params);
		if($rows){
			foreach($rows as $k=>$row){
				$poi = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
				$user = Better_User::getInstance($row['major'])->getUser();
				$return[$k]['username'] = $user['username'];
				$return[$k]['avatar_small'] = $user['avatar_small'];
				$return[$k]['nickname'] = $user['nickname'];
				$return[$k]['poi'] = $poi;				
			}
		}	
		return $return;
	}
}