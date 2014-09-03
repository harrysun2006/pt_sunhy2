<?php

/**
 * 用户相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_UsersController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();

		$this->xmlRoot = 'statuses';
	}
	
	public function commonfriendsAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		$friendUid = (int)$this->getRequest()->getParam('friend_uid', 0);
		
		if ($friendUid!=BETTER_SYS_UID) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			$result = $user->friends()->commonFriendsWith($friendUid, $this->page, $this->count);
			
			foreach ($result['rows'] as $row) {
				$this->data[$this->xmlRoot][] = array(
					'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		
		$this->output();	
	}
	
	/**
	 * 5.8 周围推荐用户
	 * 
	 * @return
	 */
	public function recommandAction()
	{
		$this->auth();
		$this->xmlRoot = 'users';
		
		list($lon, $lat) = $this->mixLL();		
		$range = (int)$this->getRequest()->getParam('range', 5000);
		
		$range==10000000 && $range = 50000;

//		$result = Better_User_Signup::recUsers(array(
//			'lon' => $lon,
//			'lat' => $lat,
//			'range' => $range,
//			'page' => 1,
//			'count' => 20,
//			'public' => true,
//			'uid' => $this->uid,
//			'has_avatar' => true,
//			'order_key' => 'karma'
//			));	

		$result['rows'] = array();		
		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
		}		
				
		$this->output();
	}
	
	/**
	 * 我去过的
	 * 
	 * @return
	 */
	public function checkinedpoisAction()
	{
		$this->auth();
		$this->xmlRoot = 'pois';
		
		$pois = $this->user->checkin()->checkinedPois($this->page, $this->count);
		foreach ($pois['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo
					)),
				);
		}
		
		$this->output();
	}
	
	/**
	 * 9.17 好友排序列表
	 * 
	 */
	public function sortAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'users';
		$category = $this->getRequest()->getParam('category', '');
		
		$allowed = array('rp', 'badge', 'major', 'follower', 'karma');
		
		if (in_array($category, $allowed)) {
			switch ($category) {
				case 'badge':
					$order = 'c.badges';
					break;
				case 'major':
					$order = 'c.majors';
					break;
				case 'follower':
					$order = 'c.followers';
					break;
				case 'rp':
				case 'karma':
				default:
					$order = 'p.karma';
					break;
			}
			
			$result = $this->user->friends()->all($this->page, $this->count, $order.' DESC', true);
		
			foreach ($result['rows'] as $row) {
				$this->data[$this->xmlRoot][] = array(
					'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_sort_option');
		}
		
		$this->output();
	}
	
	/**
	 * 9.16.2 是否允许/屏蔽所有ping
	 * 
	 * @return
	 */
	public function pingallAction()
	{
		$this->auth();
		
		$this->needPost();
		$allow = $this->getRequest()->getParam('allow', '');
		$this->xmlRoot = 'message';
		
		if ($allow=='true' || $allow=='false') {
			$this->user->updateUser(array(
				'allow_ping' => $allow=='true' ? 1 : 0,
				));
			$this->data[$this->xmlRoot] = $this->lang->pingall->success;
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_ping');
		}
		
		$this->output();
	}
	
	/**
	 * 9.16 是否ping用户
	 * 
	 * @return
	 */
	public function pingAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$id = (int)$this->getRequest()->getParam('id', 0);
		$allow = $this->getRequest()->getParam('allow', '');
		$message = '';
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				if ($allow=='true') {
					$this->user->ping()->pingOn($id);
					$message = $this->lang->ping->on->success;
				} else if ($allow=='false') {
					$this->user->ping()->pingOff($id);
					$message = $this->lang->ping->off->success;
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.users.invalid_ping');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->data[$this->xmlRoot] = $message;
		$this->output();
	}

	/**
	 * 9.15 举报用户
	 * 
	 * @return
	 */
	public function reportAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'message';
		$this->needPost();

		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$reason = trim(urldecode($this->getRequest()->getParam('reason', 'others')));
				$du = Better_Denounce::factory('user');
				
				if (!$du->denounced($this->uid, $id, $reason)) {
					
					$flag = $du->denounce(array(
						'uid' => $this->uid,
						'denounce_uid' => $id,
						'reason' => $reason,
						));
						
					if ($flag) {
						$this->data[$this->xmlRoot] = $this->lang->denounce->user->success;
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.users.duplicate_report');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 5.3私人用户空间
	 * 
	 * @return
	 */
	public function collectionsAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'userspace_private';
		$status = trim($this->getRequest()->getParam('status', 'false'))=='true' ? true : false;
		$poi = trim($this->getRequest()->getParam('poi', 'false'))=='true' ? true : false;
		$smallIcon = (bool)($this->getRequest()->getParam('small_icon', 'false')=='true' ? true : false);
		
		$this->data[$this->xmlRoot] = array(
			'user' => $this->api->getTranslator('user')->translate(array(
				'data' => $this->userInfo,
				)),
			'collection_status' => array(),
			'collection_poi' => array(),
			);

		if ($status==true) {
			
			$return = $this->user->favorites()->all($this->page, $this->count, array(
				'normal', 'checkin', 'tips'
				));

			foreach ($return['rows'] as $row) {
				$this->data[$this->xmlRoot]['collection_status'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		
		if ($poi==true) {
			
			$return = $this->user->poiFavorites()->all($this->page, $this->count);
			foreach ($return['rows'] as $row) {
				$row['top'] = false;
				$this->data[$this->xmlRoot]['collection_poi'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						'small_icon' => $smallIcon
						)),
					);
			}
		}
		
		$this->output();
	}
	
	/**
	 * 9.10所有好友
	 * 
	 * @return
	 */
	public function friendsAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id!=BETTER_SYS_UID) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			$result = $user->friends()->all($this->page, $this->count);
			
			foreach ($result['rows'] as $row) {
				$this->data[$this->xmlRoot][] = array(
					'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		
		$this->output();	
	}
	
	/**
	 * 9.9所有阻止的用户
	 * 
	 * @return
	 */
	public function blockingAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id!=BETTER_SYS_UID) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$result = $user->block()->getBlocksWithDetail($this->page, $this->count);
				
				foreach ($result['rows'] as $row) {
					$this->data[$this->xmlRoot][] = array(
						'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invaid_user');
			}
		}
		
		$this->output();

	}
	
	/**
	 * 9.5关注的所有人
	 * 
	 * @return
	 */
	public function followingsAction()
	{
		
		//2011-2-10 取消支持api取所有关注的人
		$this->errorDetail = __METHOD__.':'.__LINE__;
		$this->error('error.not_support_action');
		
		
		$this->auth();
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);

		$this->xmlRoot = 'users';
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
			
				if ($userInfo['uid']!=BETTER_SYS_UID) {
					$result = $user->follow()->getFollowingsWithDetail($this->page, $this->count);
			
					foreach ($result['rows'] as $row) {
						$this->data[$this->xmlRoot][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}

		$this->output();
	}
	
	/**
	 * 5.6 用户所有皇冠
	 * 
	 * @return
	 */
	public function crownsAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'crowns';
		
		$userInfo = &$this->userInfo;

		$uid = (int)$this->getRequest()->getParam('id', $this->uid);		
		$ver = trim($this->getRequest()->getParam('ver', '1'))=='2' ? '2' : '1';
		
		if ($uid) {
			$showUser = Better_User::getInstance($uid);
			$showUserInfo = $showUser->getUserInfo();
			
			if ($showUserInfo['uid'] && $showUserInfo['uid']!=BETTER_SYS_UID) {
				$majors = $showUser->major()->getAll($this->page, $this->count);
				
				foreach ($majors['rows'] as $major) {
					$this->data[$this->xmlRoot][] = array(
						'crown' => $this->api->getTranslator('crown')->translate(array(
							'data' => &$major,
							'userInfo' => &$showUserInfo,
							'ver' => $ver
							)),
						);
				}
                        } else if ($showUserInfo['uid']==BETTER_SYS_UID) {
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}

		$this->output();
	}
	
	/**
	 * 5.1 公共用户空间
	 * 
	 * @return
	 */
	public function publictimelineAction()
	{
		$this->simpleAuth();
		
		$this->xmlRoot = 'userspace_public';
		
		list($lon, $lat) = $this->mixLL();
		
		$range = (int)$this->getRequest()->getParam('range', 5000);
		$userConcise = (bool)($this->getRequest()->getParam('user_concise', 'true')=='false' ? false : true);
		$friend = (bool)($this->getRequest()->getParam('friend', 'false')=='true' ? true : false);
		$nearby = (bool)($this->getRequest()->getParam('nearby', 'false')=='true' ? true : false);
		$crown = (bool)($this->getRequest()->getParam('crown', 'false')=='true' ? true : false);
		$mentionMe = (bool)($this->getRequest()->getParam('mention_me', 'false')=='true' ? true : false);

		//user
		$userTranslator = $userConcise ? 'user_concise' : 'user';
		$data[$userTranslator] = $this->api->getTranslator($userTranslator)->translate(array(
			'data' => &$this->userInfo,
			));
		
		//	statuses_friend
		$data['statuses_friend'] = array();
		
		if ($friend==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			$result = $this->user->status()->apiPublicTimeline(array(
				'page' => $this->page,
				'page_size' => $this->count,
				'force_uids_check' => true,
				'type' => array('normal', 'checkin', 'tips')
				), $this->count);				
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			foreach ($result['rows'] as $row) {
				$data['statuses_friend'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 

		}
		
		//	statuses_nearby
		$data['statuses_nearby']= array();
		if ($nearby==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			$result = $this->user->status()->apiNearby(array(
					'page' => $this->page,
					'page_size' => $this->count
					));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			foreach ($result['rows'] as $row) {
				$data['statuses_nearby'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
		}
		
		//	crowns
		//	7.19 新晋掌门
		$data['statuses_crown'] = array();
		if ($crown==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			/*$results = $this->user->blog()->getAllBlogs(array(
				'page' => $this->page,
				'count' => $this->count,
				'type' => array('checkin'),
				'only_major' => true
				));*/
			$results = $this->user->blog()->getAllPublic(array(
				'page' => $this->page,
				'page_size' => $this->count
				));				
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 

			foreach ($results['rows'] as $row) {
				$data['statuses_crown'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
		}
		
		//	提到我的
		$data['statuses_mention'] = array();
		if ($mentionMe==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			$results = $this->user->status()->mentionMe(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
			
			$this->user->updateUser(array('last_rt_mine'=>time()));
			
			foreach ($results['rows'] as $row) {
				if(!isset($row['comment_id'])){
				$data['statuses_mention'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
				}else{
					$data['statuses_mention'][] = array(
						'status' => $this->api->getTranslator('status_comment')->translate(array(
							'data' => &$row
							))
					);
				}
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__); 
		}

		
		 //提到我的数量
		 $newRtCount = Better_DAO_Mentionme::getInstance($this->uid)->newMentionmeCount($this->userInfo['last_rt_mine']);
		 $data['mentionmes'] = $newRtCount ? $newRtCount : 0;

		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();

	}
	
	/**
	 * 5.2 用户空间
	 * 
	 * @return
	 */
	public function showAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'userspace';

		$showUid = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($showUid>0) {
			$showUser = Better_User::getInstance($showUid);	
			$showUserInfo = $showUser->getUserInfo();
			$badgeCnt = Better_DAO_User_Badge::getInstance($showUid)->getBadgeCntByUid($showUid);
			$showUser->updateUser(array('badges' => $badgeCnt), true);				
			$showUserInfo['badges']	= $badgeCnt;						
			
			$canViewDoing = $this->user->canViewDoing($showUid);
			
			if ($showUserInfo['uid']) {
		
				if ($showUserInfo['state']!=Better_User_State::BANNED || $showUserInfo['uid']==$this->uid) {
					$userConcise = (bool)($this->getRequest()->getParam('user_concise', 'false')=='false' ? false : true);
					$achievement = (bool)($this->getRequest()->getParam('achievement', 'false')=='false' ? false : true);
					$status = (bool)($this->getRequest()->getParam('status', 'false')=='true' ? true : false);
					$friend = (bool)($this->getRequest()->getParam('friend', 'false')=='true' ? true : false);
					$following = (bool)($this->getRequest()->getParam('following', 'false')=='true' ? true : false);
					$follower = (bool)($this->getRequest()->getParam('follower', 'false')=='true' ? true : false);
					$commonFriends = (bool)($this->getRequest()->getParam('common_friends', 'false')=='true'  ? true : false);
					$commonFriendsCount = (bool)($this->getRequest()->getParam('common_friends_count', 'false')=='true'  ? true : false);;
					$ver = trim($this->getRequest()->getParam('ver', '1'))=='2' ? '2' : '1';
					$userKey = $userConcise ? 'user_concise' : 'user';
					
					$data = array(
						$userKey => array(), 
						'achievements' => array(), 
						'statuses' => array(),
						'friends' => array(),
						'followings' => array(), 
						'followers' => array(),
						'common_friends' => array(),
						'common_friends_count' => 0
						);
			
					//user
					$userTranslator = $userConcise ? 'user_concise' : 'user';
					$data[$userKey] = $this->api->getTranslator($userTranslator)->translate(array(
						'data' => &$showUserInfo,
						'userInfo' => &$this->userInfo,
						'home_show' => $this->user->friends()->getHomeShow($showUid)
						));
						
					//	archievements
					if ($showUid!=BETTER_SYS_UID && $achievement==true && $canViewDoing) {
						//	掌门
						
						//	勋章
						
						//	宝物
						
						$showUserInfo = $showUser->parseUserFull();
						$ar = array(
							'crowns' => array(),
							'treasures' => array(),
							'badges' => array(),
							);
			
						$_x = 0;
						foreach ($showUserInfo['majors_detail'] as $major) {
							$ar['crowns'][] = array(
								'crown' => $this->api->getTranslator('crown')->translate(array(
									'data' => &$major,
									'userInfo' => &$showUserInfo,
									'ver' => $ver
									)),
								);
							$_x++;
						}

						$showUserInfo['badges_detail'] = (array)$showUserInfo['badges_detail'];
						foreach ($showUserInfo['badges_detail'] as $badge) {
							$ar['badges'][] = array(
								'badge' => $this->api->getTranslator('badge')->translate(array(
									'data' => &$badge,
									'userInfo' => &$showUserInfo,
									)),
								);
						}

						$ts = $showUser->treasure()->getMyTreasures(true);
						foreach ($ts as $row) {
							$ar['treasures'][] = array(
								'mytreasure' => $this->api->getTranslator('mytreasure')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
	
						$data['achievements'] = &$ar;
					}
					
					// statuses
					if ($status==true) {
						if ($showUserInfo['uid']==$this->uid) {
							$sParams = array(
								'page' => $this->page,
								'uid' => $this->uid,
								'type' => array('normal', 'checkin', 'tips'),
								'page_size' => $this->count,
								);
						} else {
							$sParams = array(
								'page' => $this->page,
								'uid' => $showUserInfo['uid'],
								'type' => array('normal', 'checkin', 'tips'),
								'page_size' => $this->count,
								);
						}
						
						$return = $this->user->status()->getSomebody($sParams);
						foreach ($return['rows'] as $row) {
							$data['statuses'][] = array(
								'status' => $this->api->getTranslator('status')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
					}
					
					// friends
					if ($showUid!=BETTER_SYS_UID && $friend==true && $canViewDoing) {
						$return = $showUser->friends()->all($this->page, $this->count, 'p.karma DESC');
	
						foreach ($return['rows'] as $row) {
							$data['friends'][] = array(
								'friend' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
					}
					
					//	common friends
					if ($showUid!=BETTER_SYS_UID && $commonFriends && $canViewDoing) {
						
						if ($this->uid!=$showUid) {
							$result = $this->user->friends()->commonFriendsWith($showUid, $this->page, $this->count);
			
							foreach ($result['rows'] as $row) {
								$data['common_friends'][] = array(
									'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
										'data' => &$row,
										'userInfo' => &$this->userInfo,
										)),
									);
							}			
						} else {
							$data['common_friends'] = array();
						}			
					}
					
					if ($showUid!=BETTER_SYS_UID && $commonFriendsCount && $canViewDoing && $this->uid!=$showUid) {
						$data['common_friends_count'] = $this->user->friends()->commonFriendsWithCount($showUid);
					}
					
					if ($showUid!=BETTER_SYS_UID && $following==true && $canViewDoing) {
						$return = $showUser->follow()->getFollowingsWithDetail($this->page, $this->count);
						
						foreach ($return['rows'] as $row) {
							$data['followings'][] = array(
								'following' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
					}
					
					if ($showUid!=BETTER_SYS_UID && $follower==true && $canViewDoing) {
						$return = $showUser->follow()->getFollowersWithDetail($this->page, $this->count);
						
						foreach ($return['rows'] as $row) {
							$data['followers'][] = array(
								'follower' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
					}
					
					$this->data[$this->xmlRoot] = &$data;
				} else if ($this->uid!=$showUserInfo['uid']) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.users.was_banned');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->output();
		
	}
	
	/**
	 * 5.4 用户报道历史
	 * 
	 * @return
	 */
	public function checkinstimelineAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'statuses';
		
		$uid = $this->userInfo['uid'];
		$uid = (int)$this->getRequest()->getParam('id', $uid);
		
		if ($uid) {
			if ($uid!=BETTER_SYS_UID) {
				$user = Better_User::getInstance($uid);
				$userInfo = $user->getUser();
				
				if ($userInfo['uid']) {
					$rows = $user->checkin()->history($this->page, $this->count);
					
					foreach ($rows['rows'] as $row) {
						$this->data[$this->xmlRoot][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * POI相关
	 * 
	 * 
	 */
	public function poiAction()
	{
		$this->auth();
		
		switch ($this->todo) {
			case 'checkins':
				$this->_poiCheckins();
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.request.not_found', 404);
				break;
		}
		
		$this->output();
	}
	
	/**
	 * 5.5 用户报到过的地点
	 * 
	 * @return
	 */
	private function _poiCheckins()
	{
		$this->xmlRoot = 'pois';
		
		$uid = (int)$this->getRequest()->getParam('id', $this->uid);		
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		if ($userInfo['uid']) {
			$rows = $user->checkin()->checkinedPois($this->page, $this->count);
			
			if ($rows['total']>0) {
				foreach ($rows['rows'] as $row) {
					$this->data[$this->xmlRoot][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo
							)),
						);
				}
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 5.7 搜索用户
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$this->auth();
		
		$this->xmlRoot = 'users';
		
		$query = trim($this->getRequest()->getParam('query', ''));
		list($lon, $lat) = $this->mixLL();
		$range = (int)$this->getRequest()->getParam('range', 5000);
		$range = 100000000;
		
		if ($query=='' && !$lon && !$lat) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.query_required');
		}
		
		$method = 'mysql';

		$result = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => $this->count,
			'keyword' => $query,
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			'method' => $method,
			))->searchByKarma();

		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
		}		

		$this->output();
	}
	
	/**
	 * 9.6 用户的粉丝
	 * 
	 * @return
	 */
	public function followersAction()
	{
		
		//2011-2-10 取消支持api取所有粉丝
		$this->errorDetail = __METHOD__.':'.__LINE__;
		$this->error('error.not_support_action');
		
		
		$this->auth();
		
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				
				if ($userInfo['uid']!=BETTER_SYS_UID) {
					$result = $user->follow()->getFollowersWithDetail($this->page, $this->count);
					foreach ($result['rows'] as $row) {
						$this->data[$this->xmlRoot][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}

		$this->output();
	}

	/**
	 * 开关：对好友设置是否在动态列表里显示其动态
	 */
	public function homeshowAction(){
		$this->auth();
		$this->xmlRoot = 'message';
		$this->needPost();

		$fuid = $this->post['fuid'];
		$show = $this->post['show'];

		$show = $show === 'false' ? false : true;
		$handle = $show ? $this->lang->show : $this->lang->hide;

		if (strlen($fuid) == 0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.friend.invalid_uid');
		} else {
			if ($this->user->friends()->setHomeShow($fuid, $show)) {
				$message = $show ? $this->lang->users->friend->homeshow->success : $this->lang->users->friend->homehide->success;
				$this->data[$this->xmlRoot] = $message;
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error($show ? 'error.users.friend.homeshow' : 'error.users.friend.homehide');
			}			
		}
		$this->output();
	}

	public function todosAction()
	{
		$this->auth();
		$this->xmlRoot = 'statuses';
		$this->needPost();
		$uid = $this->userInfo['uid'];
		$uid = (int) $this->getRequest()->getParam('id', $uid);
		$page = (int) $this->getRequest()->getParam('page', 1);
		$page_size = (int) $this->getRequest()->getParam('page_size', 20);
		$page_size > 50 && $page_size = 50;
		$userInfo = null;
		if ($uid && $uid != BETTER_SYS_UID) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUser();
		}
		if ($userInfo && $userInfo['uid']) {
			$params = array(
				'uid' => $uid,
				'page' => $page,
				'page_size' => $page_size,
				'order' => 'distance_asc'
			);
			$rows = $user->status()->getSomeTodo($params);
			foreach ($rows['rows'] as $row) {
				$this->data[$this->xmlRoot][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
		}
		
		$this->output();
		
	}
}
