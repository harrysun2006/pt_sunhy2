<?php

/**
 * 用户排行榜数据操作
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_Ranking extends Better_DAO_User
{
	private static $instance = array();
	private static $sysuser = '10000,168671';
	public function __construct($identifier=0)
	{
		parent::__construct($identifier);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	
	/**
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	public function karmaWeekMyFriend($params)
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page || $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		$limit = $page*$pageSize+1;
		$ids = $params['ids'];
		$uid = $params['uid'];
		
		$results = array(
			'rows' => array(),
			'count' => 0
			);
    	$cacher = Better_Cache::remote();
    	$cacheKey = 'rp_ranking_friend_500_' . $uid;
    	$result = $cacher->get($cacheKey);	  	
    	if ($result === false) {
    		$rows = Better_DAO_Rp::getInstance()->getUserByIds($ids);
			$allData = array();
			foreach($rows as $row) {
				$_uid = $row['uid'];
				$_weekRp = $row['rp_week'];
				$_userinfo = Better_User::getInstance($_uid)->getUserInfo();
				$_userinfo['rp'] = $_weekRp;
				$allData[] = $_userinfo;			
			}    		
    		$cacher->set($cacheKey, $allData, 3600);
    		$result = $allData;
    	} 

		$tmp = array_chunk($result, $pageSize);
		$results['rows'] = isset($tmp[$page-1]) ? $tmp[$page-1] : array();
		$results['count'] = count($results['rows']);		
		
		return $results;		
						
	}
	
	/**
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	public function karmaWeekMyCity($params)
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page || $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		$city = trim($params['city']);
		$limit = $page*$pageSize+1;
		
		$results = array(
			'rows' => array(),
			'count' => 0
			);

		$city = mb_substr($city, 0, 3, 'UTF-8');	
			
    	$cacher = Better_Cache::remote();
    	$cacheKey = 'rp_ranking_city_500_' . md5($city);
    	$result = $cacher->get($cacheKey);	   				
    	if ($result === false) {
    		$rows = Better_DAO_Rp::getInstance()->getCityUser($city);
			$allData = array();
			foreach($rows as $row) {
				$_uid = $row['uid'];
				$_weekRp = $row['rp_week'];
				$_userinfo = Better_User::getInstance($_uid)->getUserInfo();
				
				$_userinfo['rp'] = $_weekRp;
				$allData[] = $_userinfo;			
			}    		
    		$cacher->set($cacheKey, $allData, 3600);
    		$result = $allData;
    	} 

		$tmp = array_chunk($result, $pageSize);
		$results['rows'] = isset($tmp[$page-1]) ? $tmp[$page-1] : array();
		$results['count'] = count($results['rows']);		
		return $results;			
	}
	
	
	/**
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	public function karmaWeekGlobal($params)
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page || $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		$limit = $page*$pageSize+1;

		$results = array(
			'rows' => array(),
			'count' => 0
			);
		$tmp = array();

    	$cacher = Better_Cache::remote();
    	$cacheKey = 'rp_ranking_global_week_500';
    	$result = $cacher->get($cacheKey);		
		if ($result === false) {
	    	$rows = Better_DAO_Rp::getInstance()->getAll(NULL, 100);	    		
			$allData = array();
			foreach ($rows as $row) {
				$_uid = $row['uid'];
				$_weekRp = $row['rp_week'];
				$_userinfo = Better_User::getInstance($_uid)->getUserInfo();
				
				$_userinfo['rp'] = $_weekRp;
				$allData[] = $_userinfo;
			}
			$cacher->set($cacheKey, $allData, 3600);
			$result = $allData;			
		}    	
    	
		$tmp = array_chunk($result, $pageSize);
		$results['rows'] = isset($tmp[$page-1]) ? $tmp[$page-1] : array();
		$results['count'] = count($results['rows']);		
		return $results;
	}
		
	
	public function karmaMyCity(array $params=array())
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page || $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		$city = trim($params['city']);
		$limit = $page*$pageSize+1;
		
		$results = array(
			'rows' => array(),
			'count' => 0
			);
		$tmp = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma' , 
				'p.last_checkin_poi', 'p.timezone', 'p.rp'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
				
			$select->limit($limit);
			$select->order('p.rp DESC');
			$select->where('p.live_city=?', $city);
			$select->where('p.state!=?', 'banned');
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();

			foreach ($rows as $row) {
				$tmp[$row['rp'].'.'.$row['uid']] = $row;
			}
		}
		
		if (count($tmp)>0) {
			krsort($tmp, SORT_NUMERIC);
			$tmp = array_chunk($tmp, $pageSize);
			$results['rows'] = isset($tmp[$page-1]) ? $tmp[$page-1] : array();
			$results['count'] = count($results['rows']);
		}
		
		return $results;
	}
	


	public function &karmaGlobal(array $params=array())
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page || $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		$limit = $page*$pageSize+1;
		
		$results = array(
			'rows' => array(),
			'count' => 0
			);
		$tmp = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma' , 
				'p.last_checkin_poi', 'p.timezone', 'p.rp'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
				
			$select->limit($limit);
			$select->order('p.rp DESC');
			$select->where('p.state!=?', 'banned');
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$tmp[$row['rp'].'.'.$row['uid']] = $row;
			}
		}
		
		if (count($tmp)>0) {
			krsort($tmp, SORT_NUMERIC);
			$tmp = array_chunk($tmp, $pageSize);
			$results['rows'] = isset($tmp[$page-1]) ? $tmp[$page-1] : array();
			$results['count'] = count($results['rows']);
		}
		
		return $results;
	}	
	
}