<?php

/**
 * 用户相关
 * 
 * @package 
 * @author 
 *
 */
class Public_UsersController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();

		$this->xmlRoot = 'statuses';
		$this->auth();
	}

	/**
	 * 5.2 用户空间
	 * 
	 * @return
	 */
	public function showAction()
	{
		$this->xmlRoot = 'userspace';
		
		$userInfo = $this->userInfo;

		$showUid = (int)$this->getRequest()->getParam('id', $this->uid);

		if ($showUid>0) {
			$showUser = Better_User::getInstance($showUid);
			$showUser->getUserInfo();
			$showUserInfo = $showUser->parseUserFull();
			if ($showUserInfo['uid']) {
		
				if ($showUserInfo['state']!=Better_User_State::BANNED || $showUserInfo['uid']==$this->uid) {
					$userConcise = (bool)($this->getRequest()->getParam('user_concise', 'false')=='false' ? false : true);
					$achievement = (bool)($this->getRequest()->getParam('achievement', 'false')=='false' ? false : true);
					$status = (bool)($this->getRequest()->getParam('status', 'false')=='true' ? true : false);
					$friend = (bool)($this->getRequest()->getParam('friend', 'false')=='true' ? true : false);
					$following = (bool)($this->getRequest()->getParam('following', 'false')=='true' ? true : false);
					$follower = (bool)($this->getRequest()->getParam('follower', 'false')=='true' ? true : false);
					$userKey = $userConcise ? 'user_concise' : 'user';
					
					$data = array(
						$userKey => array(), 
						);
			
					//user
					$userTranslator = $userConcise ? 'user_concise' : 'user';
					$data[$userKey] = $this->api->getTranslator($userTranslator)->translate(array(
						'data' => &$showUserInfo,
						'userInfo' => &$this->userInfo,
						));
						
					//	archievements
					if ($achievement==true) {
						$ar = array(
							'crowns' => array(),
							'treasures' => array(),
							'badges' => array(),
							);
			
						foreach ($showUserInfo['majors_detail'] as $major) {
							$ar['crowns'][] = array(
								'crown' => $this->api->getTranslator('crown')->translate(array(
									'data' => &$major,
									'userInfo' => &$showUserInfo
									)),
								);
						}
	
						ksort($showUserInfo['badges_detail']);
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
					if ($friend==true) {
						$return = $showUser->friends()->all($this->page, $this->count);
	
						foreach ($return['rows'] as $row) {
							$data['friends'][] = array(
								'friend' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
						
					}
					
					if ($following==true) {
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
					
					if ($follower==true) {
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
	 * 5.7 搜索用户
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$this->xmlRoot = 'users';
		
		$query = trim($this->getRequest()->getParam('query', ''));
		
		if ($query=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.query_required');
		}
		
		$method = 'mysql';

		$result = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => $this->count,
			'keyword' => $query,
			'method' => $method,
			))->search();

		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user' => $this->api->getTranslator('user')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
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
		$this->xmlRoot = 'checkins';
		
		$this->auth();
		$uid = $this->userInfo['uid'];
		$uid = (int)$this->getRequest()->getParam('id', $uid);
		
		if ($uid) {
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
	 * 5.5 用户报到过的地点
	 * 
	 * @return
	 */
	public function poiAction()
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
	 * 9.6 用户的粉丝
	 * 
	 * @return
	 */
	public function followersAction()
	{
		$this->xmlRoot = 'users';		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				
				$result = $user->follow()->getFollowersWithDetail($this->page, $this->count);
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
				$this->error('error.users.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.users.invalid_user');
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
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
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
		
		$this->output();

	}
	
	/**
	 * 9.10所有好友
	 * 
	 * @return
	 */
	public function friendsAction()
	{
		$this->xmlRoot = 'users';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
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
		
		$this->output();	
	}	
	
	
	
	
	public function checkinedpoisAction()
	{
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
		$this->xmlRoot = 'userspace_private';
		$status = trim($this->getRequest()->getParam('status', 'false'))=='true' ? true : false;
		$poi = trim($this->getRequest()->getParam('poi', 'false'))=='true' ? true : false;
		
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
				$this->data[$this->xmlRoot]['collection_poi'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		
		$this->output();
	}
	

	
	/**
	
	/**
	 * 9.5关注的所有人
	 * 
	 * @return
	 */
	public function followingsAction()
	{
		$id = (int)$this->getRequest()->getParam('id', $this->uid);

		$this->xmlRoot = 'users';
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
			
				$result = $user->follow()->getFollowingsWithDetail($this->page, $this->count);
		
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
		$this->xmlRoot = 'crowns';
		
		$userInfo = &$this->userInfo;

		$uid = (int)$this->getRequest()->getParam('id', $this->uid);		
		
		if ($uid) {
			$showUser = Better_User::getInstance($uid);
			$showUserInfo = $showUser->getUserInfo();
			
			if ($showUserInfo['uid']) {
				$majors = $showUser->major()->getAll($this->page, $this->count);
				
				foreach ($majors['rows'] as $major) {
					$this->data[$this->xmlRoot][] = array(
						'crown' => $this->api->getTranslator('crown')->translate(array(
							'data' => &$major,
							'userInfo' => &$showUserInfo,
							)),
						);
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
	 * 5.1 公共用户空间
	 * 
	 * @return
	 */
	public function publictimelineAction()
	{
		$this->xmlRoot = 'userspace_public';
		
		$this->auth();
		
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
			$uids = array_merge($this->user->friends, $this->user->followings);

			if (count($uids)>0) {
				$result = $this->user->status()->apiPublicTimeline(array(
					'page' => $this->page,
					'page_size' => $this->count,
					'uids' => (array)$uids,
					'force_uids_check' => true,
					'type' => array('normal', 'checkin', 'tips')
					), $this->count);
				
				foreach ($result['rows'] as $row) {
					$data['statuses_friend'][] = array(
						'status' => $this->api->getTranslator('status')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo
							)),
						);
				}
			}
		}
		
		//	statuses_nearby
		$data['statuses_nearby']= array();
		if ($nearby==true) {
			$result = $this->user->status()->apiNearby(array(
					'page' => $this->page,
					'page_size' => $this->count
					));
			
			foreach ($result['rows'] as $row) {
				$data['statuses_nearby'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		
		//	crowns
		//	7.19 新晋掌门
		$data['statuses_crown'] = array();
		if ($crown==true) {
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

			foreach ($results['rows'] as $row) {
				$data['statuses_crown'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						)),
					);
			}
		}
		
		//	提到我的
		$data['statuses_mention'] = array();
		if ($mentionMe==true) {
			$results = $this->user->status()->mentionMe(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
			
			foreach ($results['rows'] as $row) {
				$data['statuses_mention'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}
		}

		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();

	}
	
}