<?php

/**
 * 用户关注相关
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 *	2010-01-30  已过期 yangl
 */
class Better_User_Follow extends Better_User_Base
{
	protected static $instance = array();
	
	public $followings = array();
	public $followers = array();
	
	protected $cachedFollowings = array();
	protected $cachedFollowers = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 获取关注了哪些人
	 * 
	 * @return array
	 */
	public function getFollowings($force=false)
	{
		/*if ($this->uid) {
			if ($force===true || count($this->followings)==0) {
				$this->followings = $this->user->cache()->get('followings');
				if (!Better_Config::getAppConfig()->relation_use_cache || !$this->followings) {
					$this->followings = array();
				}
							
				if (count($this->followings)==0) {
					$data = Better_DAO_Following::getInstance($this->uid)->getAll(array(
						'uid' => $this->uid,
						'order' => 'dateline DESC',
						), null);
						
					foreach ($data as $row) {
						if (!in_array($row['following_uid'], $this->followings)) {
							$this->followings[] = (string)$row['following_uid'];
						}
					}
					
					$this->followings = array_unique($this->followings);
					$this->user->cache()->set('followings', $this->followings);
				} 				
			}
			
			$result = &$this->followings;
		} else {
			$result = array();
		}*/
		$result = array();
		return $result;		
	}
	
	/**
	 * 获取关注了的那些人的详细信息
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function &getFollowingsWithDetail($page=1, $pageSize=BETTER_PAGE_SIZE)
	{

		$tmp = array(
						'rows' => array(),
						'pages' => 0,
						);
		/*$rows = array();
		
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			$this->getFollowings();
	
			if (count($this->followings)>0) {
				$tmp = &Better_DAO_User_Following::getInstance($this->uid)->getFollowingsDetail($page, $pageSize);
	
				foreach ($tmp['rows'] as $k=>$row) {
					$tmp['rows'][$k] = $this->user->parseUser($row);
				}
			}
		}*/

		return $tmp;
	}	

	/**
	 * 获取粉丝
	 * 
	 * @return array
	 */
	public function &getFollowers($force=false)
	{
		/*if ($force===true || count($this->followers)==0) {
			$this->followers = $this->user->cache()->get('followers');
			if (!Better_Config::getAppConfig()->relation_use_cache || !$this->followers) {
				$this->followers = array();
			}
						
			if (count($this->followers)==0) {
				$data = Better_DAO_Follower::getInstance($this->uid)->getAll(array(
					'uid' => $this->uid,
					'order' => 'dateline DESC'
					));
				$checking = Better_Config::getAppConfig()->user->morefans;
				foreach ($data as $row) {
					if($checking){
							$this->followers[] = (string)$row['follower_uid'];
					} else {
					//	if (!in_array($row['follower_uid'], $this->followers)) {
							$this->followers[] = (string)$row['follower_uid'];
					///	}
					}
				}
				
				$this->user->cache()->set('followers', $this->followers);
			}
			
			$this->followers = array_unique($this->followers);
		}*/

		return array();
	}	
	
	
	/**
	 * 获取粉丝(包含时间)
	 * 
	 * @return array
	 */
	public function getFollowersWithTime($force=false)
	{
		return $this->getFollowers();
	}	
	
	/**
	 * 获取粉丝的详细信息
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function &getFollowersWithDetail($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$tmp = array(
						'rows' => array(),
						'pages' => 0,
						);
		/*$rows = array();
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			if ($this->userInfo['followers']>0) {
				$tmp = Better_DAO_User_Follower::getInstance($this->uid)->getFollowersDetail($page, $pageSize);
				foreach ($tmp['rows'] as $k=>$row) {
					$tmp['rows'][$k] = $this->user->parseUser($row);
				}
			}
		}*/
		
		return $tmp;
	}	
	
	/**
	 * 强制关注某人（用在加好友时的自动双向关注）
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function forceAdd($uid,$getkarma=1)
	{
		return $this->_add($uid, false,$getkarma);
	}
	
	/**
	 * 请求关注某人
	 * 
	 * @param $uid
	 * @return array
	 */
	public function request($uid)
	{

		$codes = array(
			'SUCCESS' => 1,
			'FAILED' => 0,
			'PENDING' => -1,
			'ALREADY' => -3,
			'BLOCKEDBY' => -4,
			'BLOCKED' => -2,
			'CANTSELF' => -5,
			'INVALIDUSER' => -6,
			'ALREADYGEO' => -7,
			'INSUFFICIENT_KARMA' => -8,
			'DUPLICATED_REQUEST' => -9,
			);

		$result = $codes['FAILED'];
/*		$this->getUserInfo();

		if ($uid>0) {
			if ($uid!=$this->uid) {
				
				$this->getFollowings();

				if (in_array($uid, $this->followings)) {
					$result = $codes['ALREADY'];
				} else {

					$blockedby = $this->user->block()->getBlockedBy();
					
					if (!in_array($uid, $blockedby)) {
						$blocked = $this->user->block()->getBlocks();
						if (!in_array($uid, $blocked)) {
							$user = Better_User::getInstance($uid);
							$userInfo = $user->getUserInfo();
							
							if ($this->userInfo['karma']>=0) {
								if ($userInfo['priv']=='public' || Better_Config::getAppConfig()->follower->nocheck) {
									$return = $this->_add($uid);
									$result = $return==1 ? $codes['SUCCESS'] : $codes['FAILED'];
								} else {
									 //加关注需要确认
									$duplicated = Better_DAO_FollowRequest::getInstance($uid)->deleteByCond(array(
										'uid' => $uid,
										'request_uid' => $this->uid
										));
									Better_DAO_FollowRequest::getInstance($uid)->insert(array(
										'uid' => $uid,
										'request_uid' => $this->uid,
										'dateline' => time(),
										));
										
									Better_DAO_User_Followrequestsent::getInstance($this->uid)->insert(array(
										'uid' => $this->uid,
										'request_to_uid' => $uid,
										'dateline' => time()
										));
										
									Better_Hook::factory(array(
										'DirectMessage', 'Email', 'Notify', 'Ppns', 'Ping'
									))->invoke('FollowRequest', array(
										'uid' => $this->uid,
										'to_follow' => $uid,
										'duplicated' => $duplicated ? true : false,
									));
				
									$result = $duplicated ? $codes['DUPLICATED_REQUEST'] : $codes['PENDING'];		
								}
							} else {
								$result = $codes['INSUFFICIENT_KARMA'];
							}
					
						} else {
							$result = $codes['BLOCKED'];
						}
					} else {
						$result = $codes['BLOCKEDBY'];
					}
				}
			} else {
				$result = $codes['CANTSELF'];
			}
		} else {
			$result = $codes['INVALIDUSER'];
		}*/

		return array(
			'result' => $result,
			'codes' => &$codes
			);
	}
	
	
	/**
	 * 通过别人的加关注请求
	 * 
	 * @param $uid
	 * @return array
	 */
	public function agree($uid)
	{
		$codes = array(
			'SUCCESS' => 1,
			'FAILED' => 0,
			'PENDING' => -1,
			'ALREADY' => -3,
			'BLOCKEDBY' => -2,
			'BLOCKED' => -4,
			'CANTSELF' => -5,
			'INVALIDUSER' => -6,
			'ALREADYGEO' => -7,
			'INVALID_REQUEST' => -8,
			);

		$code = $codes['FAILED'];
/*		$this->getUserInfo();
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		if ($userInfo['uid']) {
			$this->getFollowers();

			if (!in_array($uid, $this->followers)) {
				if ($this->hasRequestToMe($uid)) {
					$code = $user->follow()->forceAdd($this->uid) ? $codes['SUCCESS'] : $codes['FAILED'];
				} else {
					$code = $codes['INVALID_REQUEST'];
				}				
			} else {
				$code = $codes['ALREADY'];
			}
		} else {
			$code = $codes['INVALIDUSER'];
		}*/

		return array(
			'codes' => &$codes,
			'code' => $code,
			);
	}
	
	/**
	 * 强制加关注
	 * 
	 * @return array
	 */
	protected function _add($uid, $checkPriv=true,$getkarma=1)
	{

		//$this->getFollowings();
		$result = 0;

		/*if ($uid!=$this->uid) {

			Better_DAO_Following::getInstance($this->uid)->replace(array(
				'uid' => $this->uid,
				'following_uid' => $uid,
				'dateline' => time(),
				));
				
			Better_DAO_Follower::getInstance($uid)->replace(array(
				'uid' => $uid,
				'follower_uid' => $this->uid,
				'dateline' => time(),
				));

			$this->clearFollowRequest($uid);

			$result = 1;
		}*/
		
		/*if ($result==1) {
			$hooks = array();			
			$hooks[] = 'Email';
			$hooks[] = 'User';
			$hooks[] = 'Notify';
			$hooks[] = 'Karma';
			$hooks[] = 'Badge';
			$hooks[] = 'Cache';
			
			if($getkarma){
				$hooks[] = 'Rp';
			}
			
			$hooks[] = 'Queue';
			
			Better_Hook::factory($hooks)->invoke('FollowSomebody', array(
				'uid' => $this->uid,
				'following_uid' => $uid,
				));		
		}*/
		
		return $result;
		
	}
	
	/**
	 * 取消关注
	 * 
	 * @param $uid
	 * @return unknown_type
	 */
	public function delete($uid)
	{

		$result = 0;
		/*$this->getUserInfo();

		if ($uid>0) {

			if ($uid==BETTER_SYS_UID) {
				$result = -2;
			} else {
				$this->getFollowings();
				
				if (in_array($uid, $this->user->followings)) {
	
					Better_DAO_Following::getInstance($this->uid)->deleteByCond(array(
						'uid' => $this->uid,
						'following_uid' => $uid
						));
						
					Better_DAO_Follower::getInstance($uid)->deleteByCond(array(
						'uid' => $uid,
						'follower_uid' => $this->uid
						));

					$this->clearFollowRequest($uid);
						
					$result = 1;
				} else {
					$result = -1;
				}
	
				if ($result==1) {
					Better_Hook::factory(array(
						'Notify', 'User', 'Karma', 'Cache', 'Queue'
					))->invoke('UnfollowSomebody', array(
						'uid' => $this->uid,
						'following_uid' => $uid,
					));	
				}
			}

		}*/
		
		return $result;		
	}
	
	/**
	 * 拒绝关注请求
	 * 
	 * @param $requestUid
	 * @return unknown_type
	 */
	public function reject($requestUid)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'IS_FOLLOWER' => -1,
			'HAS_NO_REQUEST' => -2
			);
		$code = $codes['FAILED'];
		
		/*if ($requestUid) {
			if ($this->user->isFollower($requestUid)) {
				$code = $codes['IS_FOLLOWER'];
			} else {
				if (!$this->hasRequestToMe($requestUid)) {
					$code = $codes['HAS_NO_REQUEST'];
				} else {
					Better_DAO_FollowRequest::getInstance($this->uid)->deleteByCond(array(
						'uid' => $this->uid,
						'request_uid' => $requestUid,
						));
						
					Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
						'act_result' => 2
						), array(
							'uid' => $this->uid,
							'from_uid' => $requestUid,
							'type' => 'follow_request'
						));	

					$code = $codes['SUCCESS'];
				}
			}
		}*/
		
		return array(
			'codes' => &$codes,
			'code' => $code
			);
	}	
	
	/**
	 * 获取关注请求（普通关注）
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function &getRequest($page=1, $pageSize=BETTER_PAGE_SIZE, $updateDelived=false)
	{
		return $this->_getRequest($page, $pageSize, $updateDelived);
	}
	
	/**
	 * 获取关注请求（全部）
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */	
	public function &getAllRequest($page=1, $pageSize=BETTER_PAGE_SIZE, $updateDelived=false)
	{
		return $this->_getRequest($page, $pageSize);
	}
	
	/**
	 * 获取关注请求
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	protected function &_getRequest($page=1, $pageSize=BETTER_PAGE_SIZE, $updateDelived=false)
	{
		
/*		$dao = Better_DAO_FollowRequest::getInstance($this->uid);
		$count = $dao->getRequestCount();
		
		$result = array(
			'rows' => array(),
			'count' => $count,
			);
		
		if ($count>0) {
			$cond = array(
				'uid' => $this->uid,
				'order' => 'dateline DESC',
				);

			$rows = Better_DAO_FollowRequest::getInstance($this->uid)->getAll($cond, $page.','.$pageSize);
			$uids = array();
			foreach ($rows as $row) {
				$uids[] = $row['request_uid'];
			}

			if (count($uids)>0) {
				$tmp = Better_DAO_User::getInstance($this->uid)->getUsersByUids($uids, 1, 999);
				foreach ($tmp['rows'] as $k=>$row) {
					$result['rows'][$k] = $this->user->parseUser($row);
				}
				
				if ($updateDelived) {
					Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
						'readed' => '1'
						), array(
							'type' => 'follow_request',
							'from_uid' => $uids
						));
				}
			}
		}*/
		$result = array('rows'=>array(), 'count'=>0);
		return $result;		
	}
	
	/**
	 * 判断是否有关注请求
	 * 
	 * @return bool
	 */
	public function hasRequests()
	{
		/*$count = Better_DAO_FollowRequest::getInstance($this->uid)->getRequestCount();

		return $count ? true : false;*/
		return false;
	}
	
	/**
	 * 判断是否有某人关注我的请求
	 * 
	 * @param $request_uid
	 * @return bool
	 */
	public function hasRequest($request_uid)
	{
		/*$rows = Better_DAO_FollowRequest::getInstance($this->uid)->get(array(
						'uid' => $request_uid,
						'request_uid' => $this->uid,
						));

		return isset($rows['uid']) ? true : false;*/
		return false;
	}	
	
	public function hasRequestToMe($uid)
	{
		/*$row = Better_DAO_FollowRequest::getInstance($this->uid)->get(array(
			'uid' => $this->uid,
			'request_uid' => $uid
			));

		return isset($row['uid']) ? true : false;*/
		return false;
	}
	
	/**
	 * 获取我及我关注的人的围脖
	 * 
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public function getBlogs($page=1, $pageSize=20)
	{
		return Better_User_Blog::getInstance($this->uid)->getFollowingsBlogs($page, $pageSize);
	}
	
	/**
	 * 清理关注请求
	 * 
	 * @return null
	 */
	protected function clearFollowRequest($uid)
	{
		/*Better_DAO_FollowRequest::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_uid' => $this->uid
			));
			
		Better_DAO_FollowRequest::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'request_uid' => $uid,
			));		
			
		Better_DAO_User_Followrequestsent::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'request_to_uid' => $uid,
			));
			
		Better_DAO_User_Followrequestsent::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_to_uid' => $this->uid
			));*/
	}
	
	
	/**
	 * 获得新增粉丝的数量
	 */
	public function getNewFollowerCount(){
		$count = 0;
		
		/*$userInfo = $this->getUserInfo();
		if($userInfo['uid']){
			$last_follower = $userInfo['last_my_followers'];
			
			$followers = $this->getFollowersWithTime(true);
			foreach($followers as $row){
				if($row['dateline']>$last_follower){
					$count++;
				}
			}
		}*/
		
		return $count;
	}
	
	
	//关注的人加好友
	public function getFollowingsWithEach($page=1, $pageSize=20){
		$return = $result = array();
		$rows = array();
		$result = Better_DAO_User_Following::getInstance($this->uid)->getFollowEach();
		
		$data = array_chunk($result, $pageSize);
		$_rows = isset($data[$page-1]) ? $data[$page-1] : array();
		
		foreach($_rows as $row){
			$following_uid = $row['following_uid'];
			$follower = $row['follower_uid'];
			$followingUserInfo = Better_User::getInstance($following_uid)->getUser();
			if($followingUserInfo['state']!='banned'){
				$tmp = $this->user->parseUser($followingUserInfo);
				if($follower){
					$tmp['follow_eachother'] =1;
				}else{
					$tmp['follow_eachother'] =0;
				}
				
				$hasRequest = $this->user->friends()->hasRequest($following_uid);
				if($hasRequest){
					$tmp['hasRequest'] =1;
				}else{
					$tmp['hasRequest'] =0;
				}
				$rows[] = $tmp;
			}
		}
		
		$return['rows'] = $rows;
		$return['pages'] = count($data);
		$return['count'] = count($result);
		unset($data);
		
		return  $return;
	}
}