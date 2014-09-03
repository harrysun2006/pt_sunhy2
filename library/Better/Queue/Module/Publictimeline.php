<?php

/**
 * 
 * “好友动态”队列处理
 * 
 * @package Better.Queue.Module
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Queue_Module_Publictimeline extends Better_Queue_Module_Base
{
	protected static $instance = null;
	protected $offset = 300;
	protected $passUids = array(10000);
	
	protected function __construct()
	{
		parent::__construct();	
		$this->offset = Better_Config::getAppConfig()->queue->publictimeline->offset;
	}
	
	public function __destruct()
	{
		
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 写入数据到队列
	 * 
	 * @see Better_Queue_Module_Base::push()
	 */
	public function push(array $data)
	{
		Better_DAO_Queue_Publictimeline::getInstance()->replace(array(
			'queue_time' => $data['queue_time'],
			'uid' => $data['uid'],
			'bid' => $data['bid'] ? $data['bid'] : '',
			'poi_id' => (int)$data['poi_id'],
			'following_uid' => (int)$data['following_uid'],
			'act_type' => $data['act_type'],
			'friend_uid'=> (int)$data['friend_uid']
			));
	}
	
	public function pop(array $params)
	{
		$row = array();
		
		$params['handle_time'] = '0';
		$data = Better_DAO_Queue_Publictimeline::getInstance()->get($params);
		if ($data['id']) {
			$row = &$data;
		}
		
		return $row;
	}
	
	/**
	 * 队列处理方法
	 * 
	 * @see Better_Queue_Module_Base::cal()
	 */
	public function cal(array $params)
	{
		$result = false;

		switch ($params['act_type']) {
			//	发新贴（吼吼/签到/贴士）
			case 'post':
			case '1':
				$result = $this->calPost($params);
				break;
			//	删除
			case 'delete':
			case '2':
				$result = $this->calDelete($params);
				break;
			//	关注了别人
			//case 'follow':
			//case '3':
				//$result = $this->calFollow($params);
				//break;
			//	取消了关注
			//case 'unfollow':
			//case '4':
				//$result = $this->calUnfollow($params);
				//break;
			//	被踢
			case 'banned':
			case '5':
				$result = $this->calBanned($params);
				break;
			//	调整隐私策略
			//case 'set_protected':
			//case '6':
				//$result = $this->calSetProtected($params);
				//break;
			//	取消好友
			case 'cancel_friend':
			case '7':
				$result = $this->calUnfriend($params);
				break;
			//	从被踢转换为正常
			case 'unbanned':
			case '8':
				$result = $this->calUnbanned($params);
				break;
			//case 'set_public':
			//case '9':
				//$result = $this->calSetPublic($params);
				//break;
			case 'friend':
			case '10':
				$result = $this->calFriend($params);
				break;
			//设置首页显示该好友的动态
			case '11':
				$result = $this->calFriendHomeShow($params);
				break;
			//设置首页不显示该好友的动态
			case '12':
				$result = $this->calFriendUnHomeShow($params);
				break;
			default:
				$result = true;
				break;
		}
		
		return $result;
	}
	
	public function complete($id, $handleResult=1)
	{
		$result = Better_DAO_Queue_Publictimeline::getInstance()->reconnection()->updateByCond(array(
			'handle_time' => time(),
			'handle_result' => $handleResult
			), array(
				'id' => $id
			));
			
		return $result;
	}
	
	/**
	 * 
	 * 发新内容
	 * @param array $params
	 */
	protected function calPost(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$bid = trim($params['bid']);
		
		if ($uid==BETTER_SYS_UID) {
			$result = true;
		} else {
			
			$data = Better_Cache_Module_Blog::load($bid);
			
			if ($data) {
				$uid = $data['uid'];
				$user = Better_User::getInstance($uid);
				$userInfo = $user->getUserInfo();
				
				$dateline = $data['dateline'] ? $data['dateline'] : time();
				
				//if ($data['type']!='tips') {
					if ($data['priv']=='private') {
						$priv = 'private';
					} /*else if ($data['priv']=='protected' || $userInfo['priv_blog'] || $userInfo['sys_priv_blog']) {
						$priv = 'protected';
					} */else {
					$priv = 'public';
				}
				//} else {
					//$priv = 'public';
				//}
				
				switch ($priv) {
					//case 'protected':
					case 'public':
						//$friendsUids = $user->friends;
						$friendsUids = Better_User_Friends::getInstance($uid)->getFriendsWithHomeShow();
						
						//$followersUids = $user->followers;
						
						$uids = array();
						if (count($friendsUids)>0) {
							//$uids = array_intersect($friendsUids, $followersUids);
							$uids = array_unique($friendsUids);
						}
						
						if (is_array($uids) && count($uids)>0) {
							$this->log(array(
								'uid' => $uid,
								'type' => 'post',
								'content' => 'Push bid(public)('.$data['type'].') '.$bid.' to '.count($uids).' users'
								));
							
							foreach ($uids as $todoUid) {
								Better_DAO_User_Publictimeline::getInstance($todoUid)->replace(array(
									'bid' => $bid,
									'uid' => $todoUid,
									'dateline' => $dateline
									));
							}
							
							$result = true;
						}
						break;
					//case 'public':
						/*$followersUids = $user->followers;
						$uids = array_unique($followersUids);
						
						if (count($uids)>0) {
							$this->log(array(
								'uid' => $uid,
								'type' => 'post',
								'content' => 'Push bid(public)('.$data['type'].') '.$bid.' to '.count($uids).' users'
								));						
							foreach ($uids as $todoUid) {
								Better_DAO_User_Publictimeline::getInstance($todoUid)->replace(array(
									'bid' => $bid,
									'uid' => $todoUid,
									'dateline' => $dateline
									));
							}
							
							$result = true;
						}	*/			
						//break;
					case 'private':
					default:
						$result = true;
						break;
				}
			} 
		}
		
		return $result;
	}
	
	/**
	 * 
	 * 删除内容
	 * 
	 * @param array $params
	 */
	protected function calDelete(array $params)
	{
		$result = false;
		
		$bid = trim($params['bid']);
		$uid = $params['uid'];
		
		if ($uid==BETTER_SYS_UID) {
			$result = true;
		} else {
			$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
			
			Better_DAO_User_Publictimeline::clean($bid);
							$this->log(array(
								'uid' => $uid,
								'type' => 'delete',
								'content' => 'deleted '.$bid.' from db'
								));		
			
			/*
			$user = Better_User::getInstance($uid);
			$followers = $user->followers;
			$followers = array_unique($followers);
			
			foreach ($followers as $followerUid) {
				$sum = Better_DAO_User_Publictimeline::getInstance($followerUid)->summary();
				$min = $sum['min'];
				$total = $sum['total'];
				
				if ($total==0) {
					$bids = Better_DAO_User_Publictimeline::getFollowingsBidsByCount($followerUid, $offset);
				} else if ($total>0 && $total<$offset) {
					$bids = Better_DAO_User_Publictimeline::getFollowingsBidsByCount($followerUid, $offset-$total);
				}
				
				if (count($bids)>0) {
							$this->log(array(
								'uid' => $uid,
								'type' => 'delete',
								'content' => 'add '.count($bids).' for '.$followerUid.' due to '.$uid.'\'s delete'
								));
												
					foreach ($bids as $row) {
						$bid = $row['bid'];
						$dateline = $row['dateline'];
						
						Better_DAO_User_Publictimeline::getInstance($followerUid)->replace(array(
							'uid' => $followerUid,
							'bid' => $bid,
							'dateline' => $dateline
							));
					}
				}
			}*/
			
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 
	 * 成为好友
	 * @param array $params
	 */
	protected function calFriend(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		if ($friendUid!=BETTER_SYS_UID) {
			$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
			$bids = array();
			
			$user = Better_User::getInstance($uid);
			$friendUser = Better_User::getInstance($friendUid);
			$friendUserInfo = $friendUser->getUserInfo();
			
			$tmp = Better_DAO_User_Publictimeline::getInstance($uid)->summary();
			$max = $tmp['max'];
			$min = $tmp['min'];
			$total = $tmp['total'];
			
			if ($total==0) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($friendUid, $offset, 1);
			} else if ($total>0 && $total<$offset) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($friendUid, $offset - $total, 1);
			} else if ($total>=$offset) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCountWithDateline($friendUid, $min, $offset, 1);
			}
			
			if (count($bids)>0) {
							$this->log(array(
								'uid' => $uid,
								'type' => 'friend',
								'content' => 'Push '.count($bids).' for '.$uid.' due to friend '.$friendUid
								));
											
				foreach ($bids as $bid=>$dateline) {
					Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
						'uid' => $uid,
						'bid' => $bid,
						'dateline' => $dateline
						));
				}
			}
		}
		
		$result = true;
		
		return $result;		
	}
	
	/**
	 * 
	 * 解除好友关系
	 * 
	 * @param array $params
	 */
	protected function calUnfriend(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
		
		$dao = Better_DAO_User_Publictimeline::getInstance($uid);
		
		$dao->cleanUnfriend($friendUid);
		
		$this->log(array(
			'uid' => $uid,
			'type' => 'unfriend',
			'content' => 'clean user unfriend'
			));
									
		$sum = $dao->summary();
		$min = $sum['min'];
		$total = $sum['total'];
			
		if ($total==0) {
			$bids = Better_DAO_User_Publictimeline::getFriendsBidsByCount($uid, $offset);
		} else if ($total>0 && $total<$offset) {
			$bids = Better_DAO_User_Publictimeline::getFriendsBidsByCount($uid, $offset-$total);
		}
			
		if (count($bids)>0) {
			$this->log(array(
				'uid' => $uid,
				'type' => 'unfriend',
				'content' => 'add '.count($bids).' for '.$uid
				));
										
			foreach ($bids as $bid=>$dateline) {				
				Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
					'uid' => $uid,
					'bid' => $bid,
					'dateline' => $dateline
					));
			}
		}	
		
		$result = true;
		
		return $result;
	}
	
	
	/**
	 * 
	 * 关注了某人
	 * 
	 * @param array $params
	 */
	protected function calFollow(array $params)
	{
		$result = false;
		
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
			
		if ($followingUid!=BETTER_SYS_UID) {
			$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
			$bids = array();
			
			$user = Better_User::getInstance($uid);
			$followingUser = Better_User::getInstance($followingUid);
			$followingUserInfo = $followingUser->getUserInfo();
			$isFriend = $user->isFriend($followingUid);
			
			$tmp = Better_DAO_User_Publictimeline::getInstance($uid)->summary();
			$max = $tmp['max'];
			$min = $tmp['min'];
			$total = $tmp['total'];
			
			if ($total==0) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($followingUid, $offset, $isFriend);
			} else if ($total>0 && $total < 100 ) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($followingUid, $offset - $total, $isFriend);
			} else if ($total>=$offset) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCountWithDateline($followingUid, $min, $offset, $isFriend);
			}
			
			if (count($bids)>0) {
							$this->log(array(
								'uid' => $uid,
								'type' => 'follow',
								'content' => 'Push '.count($bids).' for '.$uid.' due to follow '.$followingUid
								));
											
				foreach ($bids as $bid=>$dateline) {
					Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
						'uid' => $uid,
						'bid' => $bid,
						'dateline' => $dateline
						));
				}
			}
		}
		
		$result = true;*/
		
		return $result;
	}
	
	/**
	 * 
	 * 取消关注某人
	 * 
	 * @param array $params
	 */
	protected function calUnfollow(array $params)
	{
		$result = false;
		
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
		
		$dao = Better_DAO_User_Publictimeline::getInstance($uid);
		
		$dao->cleanUnfollow($followingUid);
						$this->log(array(
							'uid' => $uid,
							'type' => 'unfollow',
							'content' => 'clean user unfollow'
							));
									
		$sum = $dao->summary();
		$min = $sum['min'];
		$total = $sum['total'];
			
		if ($total==0) {
			$bids = Better_DAO_User_Publictimeline::getFollowingsBidsByCount($uid, $offset);
		} else if ($total>0 && $total < 100 ) {
			$bids = Better_DAO_User_Publictimeline::getFollowingsBidsByCount($uid, $offset-$total);
		}
			
		if (count($bids)>0) {
						$this->log(array(
							'uid' => $uid,
							'type' => 'unfollow',
							'content' => 'add '.count($bids).' for '.$uid
							));
										
			foreach ($bids as $bid=>$dateline) {				
				Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
					'uid' => $uid,
					'bid' => $bid,
					'dateline' => $dateline
					));
			}
		}	
		
		$result = false;*/

		return $result;
	}
	
	/**
	 * 
	 * 账号被封
	 * 
	 * @param array $params
	 */
	protected function calBanned(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		//$followersUids = $user->followers;
		$friendsUids = $user->friends;
		
		foreach ($friendsUids as $friendUid) {
			$this->calUnfriend(array(
				'uid' => $friendUid,
				'friend_uid' => $uid
				));
		}
		
		$result = true;
		
		return $result;
	}
	
	/**
	 * 
	 * 账号解封
	 * 
	 * @param array $params
	 */
	protected function calUnbanned(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		//$followersUids = $user->followers;
		//$friendsUids = $user->friends;
		$friendsUids = Better_User_Friends::getInstance($uid)->getFriendsWithHomeShow();
		
		foreach ($friendsUids as $friendUid) {
			$this->calFriend(array(
				'uid' => $friendUid,
				'friend_uid' => $uid
				));
		}

		$result = true;
		
		return $result;
	}
	
	/**
	 * 
	 * 隐私保护
	 * 
	 * @param array $params
	 */
	protected function calSetProtected(array $params)
	{
		$result = false;
		
		/*$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$followersUids = $user->followers;
		
		foreach ($followersUids as $followerUid) {
			$this->calUnfollow(array(
				'uid' => $followerUid,
				'following_uid' => $uid
				));
				
			$this->calFollow(array(
				'uid' => $followerUid,
				'following_uid' => $uid
				));
		}
		
		$result = true;*/
		
		return $result;
	}
	
	/**
	 * 
	 * 解除隐私保护
	 * @param array $params
	 */
	protected function calSetPublic(array $params)
	{
		$result = false;
		
		/*$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$followersUids = $user->followers;
		
		foreach ($followersUids as $followerUid) {				
			$this->calUnfollow(array(
				'uid' => $followerUid,
				'following_uid' => $uid
				));
							
			$this->calFollow(array(
				'uid' => $followerUid,
				'following_uid' => $uid
				));
		}		
		
		$result = true;*/
		
		return $result;
	}
	
	
	/**
	 * 设置显示好友动态
	 * @param $params
	 */
	protected function calFriendHomeShow(array $params){
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		if ($friendUid!=BETTER_SYS_UID) {
			$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
			$bids = array();
			
			$user = Better_User::getInstance($uid);
			$friendUser = Better_User::getInstance($friendUid);
			$friendUserInfo = $friendUser->getUserInfo();
			
			$tmp = Better_DAO_User_Publictimeline::getInstance($uid)->summary();
			$max = $tmp['max'];
			$min = $tmp['min'];
			$total = $tmp['total'];
			
			if ($total==0) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($friendUid, $offset, 1);
			} else if ($total>0 && $total<$offset) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCount($friendUid, $offset - $total, 1);
			} else if ($total>=$offset) {
				$bids = Better_DAO_User_Publictimeline::getSomebodyBidsByCountWithDateline($friendUid, $min, $offset, 1);
			}
			
			if (count($bids)>0) {
				$this->log(array(
					'uid' => $uid,
					'type' => 'homeshow',
					'content' => 'Push '.count($bids).' for '.$uid.' due to friend '.$friendUid
					));
											
				foreach ($bids as $bid=>$dateline) {
					Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
						'uid' => $uid,
						'bid' => $bid,
						'dateline' => $dateline
						));
				}
			}
		}
		
		$result = true;
		
		return $result;
	}
	
	
	/**
	 * 设置不显示好友的动态
	 * @param $params
	 */
	protected function calFriendUnHomeShow(array $params){
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		$offset = (int)Better_Config::getAppConfig()->queue->publictimeline->offset;
		
		$dao = Better_DAO_User_Publictimeline::getInstance($uid);
		
		$dao->cleanUnfriend($friendUid);
		
		$this->log(array(
			'uid' => $uid,
			'type' => 'unhomeshow',
			'content' => 'clean user unfriend'
			));
									
		$sum = $dao->summary();
		$min = $sum['min'];
		$total = $sum['total'];
			
		if ($total==0) {
			$bids = Better_DAO_User_Publictimeline::getFriendsBidsByCount($uid, $offset);
		} else if ($total>0 && $total<$offset) {
			$bids = Better_DAO_User_Publictimeline::getFriendsBidsByCount($uid, $offset-$total);
		}
			
		if (count($bids)>0) {
			$this->log(array(
				'uid' => $uid,
				'type' => 'unhomeshow',
				'content' => 'add '.count($bids).' for '.$uid
				));
										
			foreach ($bids as $bid=>$dateline) {				
				Better_DAO_User_Publictimeline::getInstance($uid)->replace(array(
					'uid' => $uid,
					'bid' => $bid,
					'dateline' => $dateline
					));
			}
		}	
		
		$result = true;
		
		return $result;
	}
	
	
	
	protected function log(array $params)
	{
		$type = $params['type'];
		$uid = $params['uid'];
		$content = $params['content'];
		
		Better_Log::getInstance()->logInfo($type.'|'.$uid.'|'.$content, 'pt_debug', true);
	}
}