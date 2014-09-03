<?php

/**
 * 用户发围脖相关功能
 * 
 * @package Better.Blog
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Blog extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * api的用户公共空间
	 * @param unknown_type $page
	 * @param unknown_type $pageSize
	 */
	public function publicTimeLine($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		if (is_array($page)) {
			$params = $page;
			$params['page_size'] || $params['page_size'] = $pageSize;			
			$params['type'] = $page['type'];
			$params['ignore_block'] = $page['ignore_block'];			
			isset($page['uids']) && $params['uids'] = &$page['uids'];			
			$params['without_me'] = isset($page['without_me']) ? $page['without_me'] : false;
			if($page['withoutme']){
				$params['without_me'] = true;				
				if(in_array($this->uid,$params['uids'])){
					$params['without_me'] = false;					
				} 
			}
		} else {
			$params = array(
				'page' => $page,
				'page_size' => $pageSize,
				);
		}
		$karmaLimit!=-9999 && $params['karma_limit'] = (int)$karmaLimit;
		$reverse = isset($params['reverse']) ? $params['reverse'] : true;
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->publicTimeLine($params);

		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0 && $reverse) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}

		return $return;						
	}
	
	public function friendsTips($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$return = array(
			'count' => 0,
			'pages' => 0,
			'rows' => array()
			);
		
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->friendsTips($page, $pageSize);
			
		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			
			$return['pages'] = count($data);
			unset($data);
			
			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}
					
		return $return;		
	}
	
	public function followingsTips($page=1, $pageSize=BETTER_PAGE_SIZE, $lon=0, $lat=0, $range=5000)
	{
		$return = array(
			'count' => 0,
			'pages' => 0,
			'rows' => array()
			);
		
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->followingsTips($page, $pageSize, $lon, $lat, $range);
			
		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			
			$return['pages'] = count($data);
			unset($data);
			
			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}
					
		return $return;		
	}	
	
	public function getRangedTips(array $params)
	{
		$return = array(
			'count' => 0,
			'pages' => 0,
			'rows' => array()
			);
		$pageSize = $params['count'] ? $params['count'] : BETTER_PAGE_SIZE;
		$page = (int)$params['page'];
		
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->rangedTips(array(
			'page' => $page,
			'lon' => (float)$params['lon'],
			'lat' => (float)$params['lat'],
			'range' => (float)$params['range'],
			'poi_id' => (int)$params['poi_id'],
			'order' => $params['order'] ? $params['order'] : ''
			));
		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			
			$return['pages'] = count($data);
			unset($data);
			
			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}
					
		return $return;
	}
	
	public function getAllTips($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		
		$return = array(
			'count' => 0,
			'pages' => 0,
			'rows' => array(),
			);
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->tips($page, $pageSize, 'dateline');

		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			
			$return['pages'] = count($data);
			unset($data);
			
			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}
		
		return $return;						
	}
	
	/**
	 * 改进的“所有最新”
	 * 
	 * @return array
	 */
	public function getAllPublic(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
					
		$params['page_size'] || $params['page_size'] = BETTER_PAGE_SIZE;
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->publicAll($params);
		
		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));				

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}
		
		return $return;			
	}
	
	/**
	 * 取所有最新微博
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function getAllBlogs($page=1, $pageSize=BETTER_PAGE_SIZE, $karmaLimit=-9999)
	{
		
		if (is_array($page)) {
			$params = $page;
			$params['page_size'] || $params['page_size'] = $pageSize;			
			$params['type'] = $page['type'];
			$params['ignore_block'] = $page['ignore_block'];			
			isset($page['uids']) && $params['uids'] = &$page['uids'];			
			$params['without_me'] = isset($page['without_me']) ? $page['without_me'] : false;
			if($page['withoutme']){
				$params['without_me'] = true;				
				if(in_array($this->uid,$params['uids'])){
					$params['without_me'] = false;					
				} 
			}
		} else {
			$params = array(
				'page' => $page,
				'page_size' => $pageSize,
				);
		}
		$karmaLimit!=-9999 && $params['karma_limit'] = (int)$karmaLimit;
		$reverse = isset($params['reverse']) ? $params['reverse'] : true;
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
		
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->all($params);

		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0 && $reverse) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}

		return $return;				
	}
	
	/**
	 * 获取我关注的人的微博
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function getFollowingsBlogs($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array()
			);
			
		/*$rows = Better_DAO_User_Blog::getInstance($this->uid)->followings($page, $pageSize);
		if (count($rows)>0) {
			$data = array_chunk($rows, $pageSize);
			$return['count'] = count($rows);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']) {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));			
				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}
		}*/

		return $return;		
	}
	
	/**
	 * 取得我的好友的微博
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function getFriendsBlogs($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			);
			
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->friends($page, $pageSize);		
		if (count($rows)>0) {
			$data = array_chunk($rows, $pageSize);
			$return['count'] = count($rows);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']) {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));			
				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}

		return $return;				
	}
	
	/**
	 * 获取发过的围脖
	 * 
	 * @param $page
	 * @param $pageSize
	 * @param $passbyExcludes
	 * @return array
	 */
	/*public function getBlogs($page=1, $pageSize=BETTER_PAGE_SIZE, $type='normal')
	{
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		$return = array(
							'count' => 0,
							'rows' => array(),
							'rts' => array(),
							);		
							
		if ($sessUid==$this->uid || (($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFollower($sessUid)))) && $userInfo['priv']!='private')) {			
			$return = $this->getAllBlogs(array(
				'page' => $page,
				'page_size' => $pageSize,
				'type' => (array)$type,
				'uids' => (array)$this->uid,
				));		
		} 
		
		return $return;
	}*/
	
	public function getSomebodyNew(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
					
		$rows = Better_DAO_User_Blog::getInstance($this->uid)->getSomebody($params);
		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0 && $reverse) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}

		return $return;			
	}
	
	/**
	 * 获取其他人的
	 * 
	 * @return array
	 */
	public function getSomebody(array $params)
	{
		$uid = $params['uid'];
		$page = $params['page'];
		$pageSize = $params['page_size'];
		$type = isset($params['type']) ? $params['type'] : 'normal';
		$withoutMe = isset($params['without_me']) ? $params['without_me'] : true;
		$return = array(
			'rows' => array(),
			'pages' => 0,	
			'rts' => array(),
			);
			
		$this->getUserInfo();
		$userInfo = Better_User::getInstance($uid)->getUserInfo();

		if (!is_array($type) && $type=='tips') {
			$return = $this->getAllBlogs(array(
				'page' => $page,
				'page_size' => $pageSize,
				'without_me' => true,
				'type' => 'tips',
				'uids' => (array)$uid,
				));
		} else {
			if ($uid==$this->uid || ($uid!=$this->uid && ($userInfo['priv']=='public' || ($userInfo['priv']=='protected' && $this->user->isFriend($uid))))) {	
				if ($uid==$this->uid && $type=='normal') {
					$return = $this->getBlogs($page, $pageSize);
				} else if ($uid==$this->uid && $type=='checkin') {
					$params = array(
						'page' => $page,
						'page_size' => $pageSize,
						'type' => 'checkin',
						'uids' => $this->uid,
						'ignore_block' => isset($params['ignore_block']) ? $params['ignore_block'] : false
						);
					$return = $this->getAllBlogs($params);
				} else { 
					$params = array(
						'page' => $page,
						'page_size' => $pageSize,
						'without_me' => $withoutMe,
						'type' => $type,
						'uids' => (array)$uid,
						'ignore_block' => isset($params['ignore_block']) ? $params['ignore_block'] : false
						);
					$return = $this->getAllBlogs($params);
				}
			}
		}

		return $return;		
	}
	
	/**
	 * 发布一个围脖
	 * 
	 * @param $data
	 * @return unknown_type
	 */
	public function add($data)
	{
		return Better_Blog::post($this->uid, $data);
	}
	
	/**
	 * 根据条件判断是否已经发表过这条微博
	 * 
	 * @param $condition
	 * @return $bid blog id in the database
	 */
	public function getBidByCond($uid,$poiid,$type)
	{
		return Better_Blog::getBidByCond($uid,$poiid,$type);
	}
	
	/**
	 * 删除一个围脖
	 * 
	 * @param $bid
	 * @return unknown_type
	 */
	public function delete($bid)
	{
		list($uid, $cnt) = explode('.', $bid);
		$bdata = Better_Blog::getBlog($bid);
		$flag = false;

		if (isset($bdata['blog']['bid']) && $this->uid==$bdata['blog']['uid']) {
			$blog = &$bdata['blog'];
			$userInfo = &$bdata['user'];
			
			$flag = Better_DAO_Blog::getInstance($uid)->delete($bid);
			
			Better_Hook::factory(array(
				'Karma', 'User', 'Newblog', 
				))->invoke('BlogDeleted', array(
				'blog' => &$bdata['blog'],
				'userInfo' => &$bdata['user'],
				));
		}
		
		return $flag;		
	}
	
	/**
	 * 取所有我被转发的微博
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function getRtBlogs($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		
		$params = array(
				'page' => $page,
				'page_size' => $pageSize,
				);
		$reverse = isset($params['reverse']) ? $params['reverse'] : true;
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);

		$rows = Better_DAO_User_Blog::getInstance($this->uid)->rtblogs($params);
		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0 && $reverse) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));			

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}
		return $return;				
	}
	
	
	public function getUnreadRtCount(){
		$count = 0;
		
		$userInfo = Better_User::getInstance($this->uid)->getUserInfo();
		if($userInfo['uid']){
			$last_rt_mine = $userInfo['last_rt_mine'];
			
			$rt_blogs['rows'] = array();
			foreach($rt_blogs['rows'] as $row){
				if($row['dateline']>$last_rt_mine){
					$count++;
				}
			}
		}
		
		$this->user->cache()->set('last_rt_got', $count, 300);
		
		return $count;
		
	}
	/**
	 * 取用户在POI的微博
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function user_poi_done($page=1, $pageSize=BETTER_PAGE_SIZE, $karmaLimit=-9999)
	{
		
		if (is_array($page)) {
			$params = $page;
			$params['page_size'] || $params['page_size'] = $pageSize;			
			$params['type'] = $page['type'];
			$params['poi'] = $page['poi'];
			isset($page['uids']) && $params['uids'] = &$page['uids'];
		} 
	
		$reverse = isset($params['reverse']) ? $params['reverse'] : true;
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);		
		$rows = Better_DAO_User_Blog::getInstance()->user_poi_done($params);

		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0 && $reverse) {
				$upbids = array_unique($upbids);
				$uprows = $this->getAllBlogs(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}

		return $return;				
	}
}
