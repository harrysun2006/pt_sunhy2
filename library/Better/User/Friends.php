<?php

/**
 * 好友关系
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Friends extends Better_User_Base
{
	protected static $instance = array();
	
	protected static $requestResult = array(
		'SUCCESS' => 1,				//	请求成功
		'PENDING' => -1,			//	等待确认
		'FAILED' => 0,					//	未知错误
		'BLOCKED' => -2,			//	阻止了对方，不能请求好友
		'REQUESTED' => -3,		//	已经发过请求了
		'BLOCKEDBY' => -4,		//	被对方阻止了，不能请求好友
		'CANTSELF' => -5,			//	不能加自己,
		'KARMA_TOO_LOW' => -7,	//	Karma过低
		'ALREADY' => -6,
		'CANTSYS' => -8,   //不能添加系统用户

		);
	protected $friends = array();
	protected $cachedFriends = array();
	protected $recentRequests;

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	public function nearByCount($lon, $lat, $range=5000)
	{
		return Better_DAO_User_Friends::getInstance($this->uid)->nearByCount($lon, $lat, $range);
	}
	
	public function nearBy($lon, $lat, $range=5000, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$fuids = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			'total' => 0,
			);
					
		$this->getUserInfo();
		if (!Better_LL::isValidLL($lon, $lat)) {
			$lon = $this->userInfo['lon'];
			$lat = $this->userInfo['lat'];
		}
		
		if ($this->userInfo['friends']>0) {
			$return = &Better_DAO_User_Friends::getInstance($this->uid)->nearBy($lon, $lat, $range, $page, $pageSize);
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = $this->user->parseUser($row);
			}
		}

		return $return;				
	}
	
	public function commonFriendsWithCount($uid)
	{
		$cacher = Better_Cache::remote();
		$cacheKey = 'kai_common_friends_'.$this->uid.'_'.$uid;
		$count = $cacher->get($cacheKey);
		
		if (!$count) {
			$sessUid = Better_Registry::get('sess')->get('uid');
			$this->getUserInfo();
			
			//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
				if ($this->userInfo['friends']>0) {
					$count = Better_DAO_User_Friends::getInstance($this->uid)->commonFriendsWithCount($uid);
					$count && $cacher->set($cacheKey, $count, 60);
				}
			//}
		}
	
		return $count;				
	}
	
	public function commonFriendsWith($uid, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$fuids = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			'total' => 0,
			);
					
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			if ($this->userInfo['friends']>0) {
				$return = &Better_DAO_User_Friends::getInstance($this->uid)->commonFriendsWith($uid, $page, $pageSize);
				foreach ($return['rows'] as $k=>$row) {
					$return['rows'][$k] = $this->user->parseUser($row);
				}
			}
		//}

		return $return;		
	}
	
	/**
	 * 发起一个好友请求
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function request($uid, $withKarma=true)
	{
		//判断要不要扣karma, 如果是找到的好友，则不扣karma
		$finded_uids = array_merge((array)$this->user->cache()->get('findedUids_email'), (array)$this->user->cache()->get('findedUids_sns'), (array)$this->user->cache()->get('findedUids_phone'));
		in_array($uid, $finded_uids) && $withKarma = false;
		
		$result = self::$requestResult['FAILED'];
		$doubleRequest = 0;
		
		if ($uid>0) {
			
			if ($uid==$this->uid) {
				$result = self::$requestResult['CANTSELF'];
			}  else if($uid==BETTER_SYS_UID){
				$result = self::$requestResult['CANTSYS'];
			}  else {
				$this->getUserInfo();
				$event = '';
				
				$f = $withKarma ? ($this->userInfo['karma']+$this->user->karma()->calculate('FriendRequest'))>=0 : true;
				if ($f) {
					
					if ($this->hasRequest($uid)) {
						$result = self::$requestResult['REQUESTED'];
					} else {
						
						$blockedby = $this->user->block()->getBlockedBy();
						$friendsUids = $this->getFriends();
						$blocked = $this->user->block()->getBlocks();
						if (in_array($uid, $blockedby)) {
							$result = self::$requestResult['BLOCKEDBY'];
						} else if (in_array($uid, $friendsUids)) {
							$result = self::$requestResult['ALREADY'];
						} else if (in_array($uid, $blocked)){
							$result = self::$requestResult['BLOCKED'];
						} else {
							$requestUser = Better_User::getInstance($uid);
							$requestUserFriends = self::getInstance($uid);
							$doubleRequest = false;
					
							if ($requestUserFriends->hasRequest($this->uid)) {		//	对方也请求加我为好友
								$doubleRequest = 1;
								
								Better_Cache::remote()->increment('fb_counter_'.$this->uid.'_'.$uid);
								define('BETTER_IN_FREIND_REQUEST_BLOG', true);
												
								Better_Hook::factory(array(
									'Notify', 'Karma', 'Badge', 'Cache', 'Blog', 'User', 'Clean', 'Rp', 'Queue'
								))->invoke('FriendWithSomebody', array(
									'uid' => $this->uid,
									'friend_uid' => $uid,
									'is_request' => true
								));		
														
								Better_DAO_Friends::getInstance($uid)->insert(array(
									'uid' => $uid,
									'friend_uid' => $this->uid,
									'dateline' => time()
									));
								Better_DAO_Friends::getInstance($this->uid)->insert(array(
									'uid' => $this->uid,
									'friend_uid' => $uid,
									'dateline' => time(),
									));									
		
								$result = self::$requestResult['SUCCESS'];
								
							} else {
								$hooks = array('DirectMessage', 'Email', 'Notify');
								$withKarma && $hooks[] = 'Karma';
								$hooks = array_merge($hooks, array('Badge', 'Clean', 'Ppns', 'Ping'));
								
								Better_Hook::factory($hooks)->invoke('FriendRequest', array(
									'uid' => $this->uid,
									'friend_uid' => $uid
								));	
								
								Better_DAO_FriendsRequestToMe::getInstance($uid)->deleteByCond(array(
									'uid' => $uid,
									'request_uid' => $this->uid
									));
								Better_DAO_FriendsRequestToMe::getInstance($uid)->insert(array(
									'uid' => $uid,
									'request_uid' => $this->uid,
									'dateline' => time()
									));
									
								Better_DAO_FriendsRequest::getInstance($this->uid)->deleteByCond(array(
									'uid' => $this->uid,
									'request_to_uid' => $uid
									));
								Better_DAO_FriendsRequest::getInstance($this->uid)->insert(array(
									'uid' => $this->uid,
									'request_to_uid' => $uid,
									'dateline' => time(),
									));
									
								$result = self::$requestResult['PENDING'];
							}
						}
					}
				} else {
					$result = self::$requestResult['KARMA_TOO_LOW'];
				}
			}
		}
		
		return array(
			'result' => $result,
			'codes' => self::$requestResult,
			'double_request' => $doubleRequest
			);
	}
	
	/**
	 * 同意好友请求
	 * 
	 * @param $uid
	 * @return array
	 */
	public function agree($uid, $force = false)
	{
		$codes = array(
			'ALREADY' => -2,
			'INVALID_REQUEST' => -1,
			'FAILED' => 0,
			'SUCCESS' => 1,
			);
		$code = $codes['FAILED'];
		
		$friendsUids = $this->getFriends();
		
		if (in_array($uid, $friendsUids)) {
			$code = $codes['ALREADY'];
		} else {
			if ($this->hasRequestToMe($uid) ||$force) {
				
				Better_Cache::remote()->increment('fb_counter_'.$this->uid.'_'.$uid);
				define('BETTER_IN_FREIND_REQUEST_BLOG', true);
				
				Better_Hook::factory(array(
					'Notify', 'Karma', 'Badge', 'Cache', 'Blog', 'User', 'Clean', 'Rp', 'Queue'
				))->invoke('FriendWithSomebody', array(
					'uid' => $this->uid,
					'friend_uid' => $uid,
					'is_request' => false,
					'follow_from_friend' => true,
					'no_notice' => $force == true
				));
		
				Better_DAO_Friends::getInstance($uid)->insert(array(
					'uid' => $uid,
					'friend_uid' => $this->uid,
					'dateline' => time()
					));
				Better_DAO_Friends::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'friend_uid' => $uid,
					'dateline' => time(),
					));
				$code = $codes['SUCCESS'];
			} else {
				$code = $codes['INVALID_REQUEST'];
			}
			
		}
		
		return array(
			'code' => $code,
			'codes' => &$codes,
			);
	}
	
	/**
	 * 拒绝别人的好友请求 
	 * 
	 * @param $uid
	 * @return array
	 */
	public function reject($uid)
	{
		$result = 0;
		
		$flag = Better_DAO_FriendsRequest::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_to_uid' => $this->uid,
			));
		Better_DAO_FriendsRequestToMe::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'request_uid' => $uid,
			));
			
		Better_DAO_FriendsRequest::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'request_to_uid' => $uid,
			));
		Better_DAO_FriendsRequestToMe::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_uid' => $this->uid,
			));			

		Better_Hook::factory(array(
			'Karma', 'User', 'Notify'
		))->invoke('RejectFriendRequest', array(
			'uid' => $this->uid,
			'request_uid' => $uid
		));
			
		$result = 1;
		
		return $result;
	}
	
	/**
	 * 判断我和某人是否好友关系
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function isFriend($uid)
	{
		/*
		$data = Better_DAO_Friends::getInstance($this->uid)->get(array(
			'uid' => $this->uid,
			'friend_uid' => $uid,
			));
			
		return $data['friend_uid'] ? true : false;
		*/
		return in_array($uid, $this->user->friends) ? true : false;
	}

	/**
	 * 删除好友
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function delete($uid)
	{
		$result = 0;
		
		$flag = Better_DAO_Friends::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'friend_uid' => $this->uid,
			));
			
		Better_DAO_Friends::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'friend_uid' => $uid,
			));

		$result = 1;
		
		$hooks = array();
		if ($flag) {
			$hooks[] = 'Karma';
			$hooks[] = 'User';
		}
		
		$hooks[] = 'Cache';
		$hooks[] = 'Queue';

		Better_Hook::factory($hooks)->invoke('UnfriendWithSomebody', array(
			'uid' => $this->uid,
			'friend_uid' => $uid
		));		
		
		return $result;
	}
	
	/**
	 * 是否有某人的加好友请求
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function hasRequestToMe($uid)
	{
		$rows = Better_DAO_FriendsRequestToMe::getInstance($this->uid)->get(array(
						'uid' => $this->uid,
						'request_uid' => $uid,
						));
		return isset($rows['uid']) ? true : false;		
	}
	
	/**
	 * 是否请求过加别人为好友
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function hasRequest($uid)
	{
		$row = Better_DAO_FriendsRequest::getInstance($this->uid)->get(array(
			'uid' => $this->uid,
			'request_to_uid' => $uid,
			));

		return isset($row['uid']) ? true : false;
	}
	
		
	/**
	 * 获得等待确认的好友请求，超过3天的认为请求已经丢失 
	 * 
	 * @return array 已经加过好友的id
	 */
	public function getPenddingRequests()
	{
		$recent = 3 * 86400; // 最近3天
		$data = Better_DAO_FriendsRequest::getInstance($this->uid)->getRecentRequests(time()-$recent);

		$result = array();
		foreach ($data as $row) {
			if (!in_array($row['request_to_uid'], $result)) {
				$result[] = (string)$row['request_to_uid'];
			}
		}
		return $result;
	}
	
	/**
	 * 获取所有好友的uids
	 * 
	 * @return array
	 */
	public function &getFriends($force=false)
	{
		if ($force===true || count($this->friends)==0) {
			$this->friends = $this->user->cache()->get('friends');
			if (!Better_Config::getAppConfig()->relation_use_cache || !$this->friends) {
				$this->friends = array();
			}
			
			if (count($this->friends)==0) {
				$data = Better_DAO_Friends::getInstance($this->uid)->getAll(array(
					'uid' => $this->uid,
					'order' => 'dateline DESC',
					), null);				
				
				foreach ($data as $row) {
					if (!in_array($row['friend_uid'], $this->friends)) {
						$this->friends[] = (string)$row['friend_uid'];
					}
				}

				$this->user->cache()->set('friends', (array)$this->friends);
			}
		}

		return $this->friends;				
	}
	
	
	/**
	 * 获得在首页显示他动态的好友
	 */
	public function getFriendsWithHomeShow(){
		$return = array();
		
		$data = Better_DAO_Friends::getInstance($this->uid)->getAll(array(
			'uid'=> $this->uid,
			'home_show'=> 1,
			'order'=> 'dateline DESC'
		), null);
		
		foreach($data as $row){
			$return[] = (string)$row['friend_uid'];
		}
		return $return;
	}
	
	/**
	 * 获得在首页不显示他动态的好友
	 */
	public function getFriendsNotWithHomeShow(){
		$return = array();
		
		$data = Better_DAO_Friends::getInstance($this->uid)->getAll(array(
			'uid'=> $this->uid,
			'home_show'=> 0,
			'order'=> 'dateline DESC'
		), null);
		
		foreach($data as $row){
			$return[] = (string)$row['friend_uid'];
		}
		return $return;
	}
	
	/**
	 * 
	 * 设置用户好友
	 * @param unknown_type $friends
	 */
	public function setFriends(&$friends)
	{
		$this->friends = $friends;
		$this->user->cache()->set('friends', $this->friends);
	}
	
	/**
	 * 所有好友
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=BETTER_PAGE_SIZE, $order='', $forceWithMe=false)
	{
		$fuids = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			'total' => 0,
			);
					
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		
			if ($this->userInfo['friends']>0) {
			$return = Better_DAO_User_Friends::getInstance($this->uid)->getAllFriends($page, $count);
				foreach ($return['rows'] as $k=>$row) {
					$return['rows'][$k] = $this->user->parseUser($row);
				}
			}
		

		return $return;
	}
	
/**
	 * 按关键字搜索所有好友
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function allbykeywords($page=1, $count=BETTER_PAGE_SIZE, $keywords="",$order='', $forceWithMe=false)
	{
		$fuids = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			'total' => 0,
			);					
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();		
		if ($this->userInfo['friends']>0) {
			$return = Better_DAO_User_Friends::getInstance($this->uid)->getAllFriendsByKeywords($page, $count,$keywords);
			foreach ($return['rows'] as $k=>$row) {
					$return['rows'][$k] = $this->user->parseUser($row);
			}
		}
		return $return;
	}
	
	public function &rightbar()
	{
		$rows = array();
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			if ($this->userInfo['friends']>0) {
				$return = &Better_DAO_User_Friends::getInstance($this->uid)->rightbar();
				foreach ($return as $k=>$row) {
					$row['avatar_normal'] = $row['avatar_url'] = $this->user->getUserAvatar('normal', $row);
					$row['avatar_small'] = $this->user->getUserAvatar('thumb', $row);
					$row['avatar_tiny'] = $this->user->getUserAvatar('tiny', $row);
					$rows[$k] = $row;
				}
			}
		//}		
		
		return $rows;
	}
	
	public function webAll($page=1, $count=BETTER_PAGE_SIZE, $order='', $forceWithMe=false)
	{
		$fuids = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			'total' => 0,
			);
					
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			if ($this->userInfo['friends']>0) {
				$return = &Better_DAO_User_Friends::getInstance($this->uid)->getAllFriends($page, $count);
				foreach ($return['rows'] as $k=>$row) {
					$row['avatar_normal'] = $row['avatar_url'] = $this->user->getUserAvatar('normal', $row);
					$row['avatar_small'] = $this->user->getUserAvatar('thumb', $row);
					$row['avatar_tiny'] = $this->user->getUserAvatar('tiny', $row);
					$return['rows'][$k] = $row;
				}
			}
		//}

		return $return;
	}	
	
	/**
	 * 所有好友请求
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function &allRequestsToMe($page=1, $count=BETTER_PAGE_SIZE)
	{
		$requests = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			);
			
		$rows = Better_DAO_FriendsRequestToMe::getInstance($this->uid)->getAll(array(
			'uid' => $this->uid,
			));
		foreach ($rows as $row) {
			$requests[] = $row['request_uid'];
		}
		
		if (count($requests)>0) {
			$return = &Better_DAO_User::getInstance($this->uid)->getusersByUids($requests, $page, $count);
		}
		
		return $return;
	}
	
	/**
	 * 我发出的所有好友请求
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function &allRequests($page=1, $count=BETTER_PAGE_SIZE, $updateDelived=false)
	{
		$requests = array();
		$return = array(
			'pages' => 0,
			'rows' => array(),
			);
			
		$rows = Better_DAO_FriendsRequest::getInstance($this->uid)->getAll(array(
			'uid' => $this->uid,
			));
		foreach ($rows as $row) {
			$requests[] = $row['request_to_uid'];
		}

		if (count($requests)>0) {
			$return = &Better_DAO_User::getInstance($this->uid)->getusersByUids($requests, $page, $count);
		}
		
		return $return;		
	}
	
	/**
	 * 获得好友发布的贴士
	 * 
	 * @return array
	 */
	public function theirTips($page=1, $count=BETTER_PAGE_SIZE)
	{
		$rows = $this->dao->getAll(array(
						'uid' => $this->uid,
						'type' => 'tips',
						));
		$bids = array();
		foreach ($rows as $v) {
			$bids[] = $v['bid'];
		}
		
		$rows = $this->dao->getTipsFavorites($bids);
		$return = array('count'=>0, 'rows'=>array(), 'rts'=>array());
		
		if (count($rows)>0) {
			$data = array_chunk($rows, $pageSize);
			$return['count'] = count($rows);
			$return['rows'] = isset($data[$page-1]) ? $data[$page-1] : array();
			unset($data);
			
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = Better_Blog::parseBlogRow($row);
			}
		}
				
		return $return;		
	}

	public function requests($user_list, $withKarma=true)
	{
		$result = self::$requestResult['FAILED'];
		$doubleRequest = 0;
		
		if(is_array($user_list)){
			$user_sucess = array();
			$pending_count = 0;
			
			$finded_uids = array_merge((array)$this->user->cache()->get('findedUids_email'), (array)$this->user->cache()->get('findedUids_sns'), (array)$this->user->cache()->get('findedUids_phone'));
			
			foreach($user_list as $row){
				$uid = $row;
				
				$widthKarma = true;
				//判断要不要扣karma, 如果是找到的好友，则不扣karma
				in_array($uid, $finded_uids) && $withKarma = false;
				
				if ($uid>0) {
					if ($uid==$this->uid) {
						$result = self::$requestResult['CANTSELF'];
					}  else if($uid==BETTER_SYS_UID){
						$result = self::$requestResult['CANTSYS'];
					}  else {
						$this->getUserInfo();
						$event = '';	

						$f = $withKarma ? ($this->userInfo['karma']+$this->user->karma()->calculate('FriendRequest'))>=0 : true;
						if ($f) {
							
							if ($this->hasRequest($uid)) {
								$result = self::$requestResult['REQUESTED'];
							} else {
								
								$blockedby = $this->user->block()->getBlockedBy();
								$friendsUids = $this->getFriends();
								$blocked = $this->user->block()->getBlocks();
								if (in_array($uid, $blockedby)) {
									$result = self::$requestResult['BLOCKEDBY'];
								} else if (in_array($uid, $friendsUids)) {
									$result = self::$requestResult['ALREADY'];
								} else if (in_array($uid, $blocked)){
									$result = self::$requestResult['BLOCKED'];
								} else {
									$requestUser = Better_User::getInstance($uid);
									$requestUserFriends = self::getInstance($uid);
									$doubleRequest = false;
							
									if ($requestUserFriends->hasRequest($this->uid)) {		//	对方也请求加我为好友									
										$doubleRequest = 1;
										
										Better_Cache::remote()->increment('fb_counter_'.$this->uid.'_'.$uid);
										define('BETTER_IN_FREIND_REQUEST_BLOG', true);
														
										Better_Hook::factory(array(
											'Notify', 'Karma', 'Badge', 'Cache', 'User', 'Clean', 'Rp', 'Queue'
										))->invoke('FriendWithSomebody', array(
											'uid' => $this->uid,
											'friend_uid' => $uid,
											'is_request' => true
										));		
										$user_sucess[] =$uid; 						
										Better_DAO_Friends::getInstance($uid)->insert(array(
											'uid' => $uid,
											'friend_uid' => $this->uid,
											'dateline' => time()
											));
										Better_DAO_Friends::getInstance($this->uid)->insert(array(
											'uid' => $this->uid,
											'friend_uid' => $uid,
											'dateline' => time(),
											));									
				
										$result = self::$requestResult['SUCCESS'];
										
									} else {
										$hooks = array('DirectMessage', 'Email', 'Notify');
										$withKarma && $hooks[] = 'Karma';
										$hooks = array_merge($hooks, array('Badge', 'Clean', 'Ppns', 'Ping'));
										
										Better_Hook::factory($hooks)->invoke('FriendRequest', array(
											'uid' => $this->uid,
											'friend_uid' => $uid
										));	
										$pending_count++;
										
										Better_DAO_FriendsRequestToMe::getInstance($uid)->deleteByCond(array(
											'uid' => $uid,
											'request_uid' => $this->uid
											));
										Better_DAO_FriendsRequestToMe::getInstance($uid)->insert(array(
											'uid' => $uid,
											'request_uid' => $this->uid,
											'dateline' => time()
											));
											
										Better_DAO_FriendsRequest::getInstance($this->uid)->deleteByCond(array(
											'uid' => $this->uid,
											'request_to_uid' => $uid
											));
										Better_DAO_FriendsRequest::getInstance($this->uid)->insert(array(
											'uid' => $this->uid,
											'request_to_uid' => $uid,
											'dateline' => time(),
											));
											
										$result = self::$requestResult['PENDING'];
									}
								}
							}
						} else {
							$result = self::$requestResult['KARMA_TOO_LOW'];
						}
					}
				}
			}
		}

		if(is_array($user_sucess) && count($user_sucess)>0){
		Better_Hook::factory(array('Blog'))->invoke('FriendWithSomebodys', array(
											'uid' => $this->uid,
											'friend_uid' => $user_sucess,
											'is_request' => true
										));	
		}
		return array(
			'result' => $result,
			'codes' => self::$requestResult,
			'double_request' => $doubleRequest,
			'resultnum' => count($user_sucess),
			'pendding' => $pending_count,
			);		
	}
	
	
	/**
	 * 设置在首页显不显示好友动态
	 * params: uid 被操作好友uid
	 */
	public function setHomeShow($uid, $show=true){
		$result = false;
		if($uid){
			if($show){
				$home_show = 1; 
			}else{
				$home_show = 0; 
			}
			$result = Better_DAO_User_Friends::getInstance($uid)->updateByCond(array('home_show'=>$home_show), array('uid'=>$uid, 'friend_uid'=>$this->uid));
			
			if($result){
				Better_Hook::factory(array(
					'Queue'
				))->invoke('SetHomeShow', array(
					'uid' => $this->uid,
					'friend_uid' => $uid,
					'show'=>$home_show
				));
			}
		}
		return $result;
	}
	
	
	/**
	 * 获得我是否愿意看到该好友在首页显示动态
	 */
	public function getHomeShow($uid){
		$row = Better_DAO_User_Friends::getInstance($uid)->getAll(array(
			'uid'=> $uid,
			'friend_uid'=> $this->uid
		));
		$r = count($row) > 0 && $row[0]['uid'] && $row[0]['home_show'] == 1;
		return $r;
	}
	
	
	/**
	 * 自动加第三方在开开的好友
	 * @param $uid
	 * @param $partner
	 * @param $service
	 * @return unknown_type
	 */
	public static function autoAddFriend($uid, $partner, $service, $third_info)
	{
		$weibo = array('sina.com', 'qq.com');
		$sns = array('kaixin001.com', 'renren.com');
		
		$protocols = array_merge($weibo, $sns);
		
		$add_uids = array();
		if ( !in_array($partner, $protocols) ) {
			return false;
		}
		
		$tid = $service->tid; //me
		$friends = $service->getFriends();
		$me = Better_User::getInstance($uid);		
//error_log('findfriend:' . $partner . ' ' . join(',', $friends));		
		if (in_array($partner, $sns)) {
			foreach ($friends as $fid) {
				$user_info = Better_DAO_ThirdBinding::getBindUser($partner, $fid);
				$bind_uid  = $user_info['uid'];
//error_log('findfriend:' . "$bind_uid,$uid");				
				if ($bind_uid) {
					$is_friend = $me->isFriend($bind_uid);
					if ($is_friend) continue;
					$me->friends()->agree($bind_uid, true);
					$add_uids[] = $bind_uid;					
				}
			}
			//end renren kaixi001
		} else {
			//这边是新浪微博和腾讯微博哦
			$rows =  Better_DAO_3rdFriends::getInstance()->getFriendIds($partner, $friends);
			foreach ($rows as $row) {
				$tid_friends = $row['tid_friends'];
				$bind_uid = $row['uid'];
				$temp_a = explode(',' , $tid_friends);
				if ( in_array($tid, $temp_a) ) {
					$is_friend = $me->isFriend($bind_uid);
					if ($is_friend) continue;
					$me->friends()->agree($bind_uid, true);
					$add_uids[] = $bind_uid;
				}
			}
			//end sina qq
		}
		
		//还要发消息的哦
		$lang = Better_Language::loadIt('zh-cn')->api->toArray();	
		$key = str_replace('.', '_', $partner);
		$name = $lang['sns'][$key]['name'];	
		// Hi，你知道吗？来自{第三方}的{第三方昵称}也加入开开了！去和@{开开昵称}  打个招呼吧？
		$third_username = $third_info['nickname'];
		$me_info = $me->getUserInfo();
		$_username = $me_info['nickname'];
		$content = "Hi，你知道吗？来自{$name}的{$third_username} 也加入开开了！去和@{$_username} 打个招呼吧？";	

		foreach ($add_uids as $u) {
			Better_User_Notification_DirectMessage::getInstance(Better_Config::getAppConfig()->user->sys_user_id)->send(array(
							'content' => $content,
							'receiver' => $u
							));
		}

		//还有为统计纪录log啊 有人会看吗？	
		if (is_array($add_uids) && count($add_uids)) {
			$log = join(',', $add_uids) . '|' . $uid;
			Better_Log::getInstance()->logAlert($log, 'autoFriend');
		}
		
		return $add_uids;
	}
}
