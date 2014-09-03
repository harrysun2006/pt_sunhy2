<?php

/**
 * 勋章相关
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Badge
{
	public static $families = array(
		'normal' => '荣誉勋章',
		'explore' => '城市探索',
		'memorial' => '纪念日',
		'event' => '精彩活动',
		'partner' => '品牌优惠',
	);
	
	protected static $instance = array();
	protected static $badges = array();
	
	public static function &getBadge($id)
	{
		if (!isset(self::$instance[$id])) {
			$badge = null;
			
			$data = Better_DAO_Badge::getInstance()->getBadge($id);
			if (isset($data['id'])) {
				$badge = self::factory($data);
			}

			self::$instance[$id] = $badge;
		}
		
		return self::$instance[$id];
	}
	
	public static function factory(array $params)
	{
		$badgeId = $params['id'];
		$category = $params['category'];
		$class = 'Better_Badge_'.ucfirst(strtolower($category));

		$badge = call_user_func(array(
			$class,
			'getInstance'
			), $badgeId);

		$badge->setParams($params);
		
		return $badge;
	}
	
	/**
	 * 看看某个用户是否有条件获得该勋章
	 * 
	 * @return bool
	 */
	public function touch(Better_User $user)
	{
		
	}
	
	/**
	 * 取得当前所有可用的勋章
	 * 
	 * @return array
	 */
	public static function &getAllBadges(array $params=array(), array $already=array())
	{
		$badges = array();
		
		$cache = Better_Cache::remote()->get('kai_badges');
		if (!$cache) {
			$cache = Better_Cache_Module_Badge::load();	
		}
		
		$category = $params['category'];
		$gender = $params['gender'] ? $params['gender'] : array('all');
		$dirty = (isset($params['dirty']) && $params['dirty']) ? $params['dirty'] : false;
		
		foreach ($cache as $row) {
			$badgeId = $row['id'];
			
			if (count($params)==0) {
				$badges[$row['id']] = self::factory($row);
			} else if (count($params)>0 && $row['category']==$category && in_array($row['gender'], $gender) && !in_array($badgeId, $already)) {
				$badges[$row['id']] = self::factory($row);
			}
		}

		return $badges;
	}
	
	protected function &getAllBadgesFromDb(array $params, array $already)
	{
		$badges = array();
		$params = (array) $params;
		if (isset($params['gender'])) {
			$params['gender'] = (array)$params['gender'];
		}

		$tmp = Better_DAO_Badge::getInstance()->getAllAvailable($params);

		foreach ($tmp as $row) {
			$badgeId = $row['id'];
			if (!in_array($badgeId, $already)) {
				$badges[$row['id']] = self::factory($row);
			}
		}
		
		return $badges;		
	}
	
	/**
	 * 获取勋章的物理存储路径
	 * 
	 * @return string
	 */
	public static function getBadgeSavePath($id)
	{
		$basePath = Better_Config::getAppConfig()->attachment->badge_save_path;
		
		$path = $basePath.DIRECTORY_SEPARATOR.'big'.DIRECTORY_SEPARATOR.$id.'.png';
		
		if (!file_exists($path)) {
			$path = '';
		}
		
		return $path;
	}
	
	/**
	 * 获取获得某个勋章的所有用户
	 * 
	 * @param unknown_type $bid
	 * @return array
	 */
	public static function &getBadgeUsers($bid, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$return = array(
			'rows' => array(),
			'total' => 0,
			'pages' => 0,
			);
		$bid = (int)$bid;
		$badges = self::getAllBadges();
		
		if (isset($badges[$bid])) {
			$cacher = Better_Cache::remote();
			$cacheKey = 'badge_users_'.$bid;
			if ($cacher->test($cacheKey)) {
				$data = $cacher->get($cacheKey);
			} else {
				$tmp = Better_DAO_Badge::getBadgeUsers($bid);
				$rows = &$tmp['rows'];

				$data = array();
				foreach ($rows as $row) {
					$data[$row['get_time'].'.'.$row['uid']] = Better_User::getInstance()->parseUser($row);
				}
				
				krsort($data);
				$cacher->set($cacheKey, $data, 3600);
			}
			
			$return['total'] = count($data);
			$return['pages'] = Better_Functions::calPages($return['total'], $pageSize);
			if ($page<=$return['pages']) {
				$tmp = array_chunk($data, $pageSize);
				$return['rows'] = $tmp[$page-1];
			}
		}

		return $return;
	}
	
	/**
	 * 
	 */
	public static function logBadge($badgeId)
	{
		$badgeInfo = Better_DAO_Badge::getInstance()->get($badgeId);
		if (!$badgeInfo) return false;
		
		$frist_time = $badgeInfo['frist_time'];
		$last_time = $badgeInfo['last_time'];
		$total = $badgeInfo['total'];
		
		$total++;
		if ($frist_time) {
			$badgeInfo['last_time'] = time();
		} else {
			$badgeInfo['frist_time'] = $badgeInfo['last_time'] = time();
		}
		$badgeInfo['total'] = $total;
		
		return Better_DAO_Badge::getInstance()->update($badgeInfo, $badgeId);
	}
	
	
	public function create($params)
	{
		$result = array();
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];
		$id = Better_DAO_Badge::getInstance()->getMaxid()+1;		
		$badgeToInsert = array(
				'id' => $id,	
				'active' => 1,			
				'badge_name' => $params['badge_name'],				
				'got_tips' => $params['got_tips'],
				'sync_tips' => $params['sync_tips'],				
				'btm' => $params['btm'],
				'etm' => $params['etm'],
				'family' => $params['family'],
				'category' => $params['category'],
				'gender' => $params['gender'],
				'info' => $params['info'],
				'condition' => $params['condition'],
				'category' => $params['category'],
				'badge_picture'=> $id.".png"
				);
	
		$flag = Better_DAO_Badge::getInstance()->insert($badgeToInsert);	
		if ($flag) {
			$code = $codes['SUCCESS'];			
		}		
		$result['code'] = $code;
		$result['id'] = $id;
		Better_Log::getInstance()->logInfo("\nResult:".serialize($result),'newbadge');
		return $result;
	} 
	
}