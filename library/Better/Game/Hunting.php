<?php

/**
 * 挖宝游戏
 * 
 * @package Better.Game
 * @author leip <leip@peptalk.cn>
 *
 */

defined('BETTER_IN_GAME') || define('BETTER_IN_GAME', true);

class Better_Game_Hunting extends Better_Game_Base
{
	protected static $instance = array();
	protected $name = 'hunting';
	
	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 
	 * 当前有效的，给我的寻宝邀请
	 * 
	 * @return array
	 */
	public function &myValidInvites()
	{
		$invites = Better_DAO_Game_Session::getInstance()->validInvitesToSomebody($this->uid);
		
		return $invites;
	}
	
	/**
	 * 分析游戏进行到的步骤
	 * 
	 * @return string
	 */
	public static function parseGameStep(&$data)
	{
		$step = '';
		
		if ($data['expired']) {
			if ($data['ended']) {
				$step = 'game_over';
			} else {
				$step = 'invite_timeout';
			}
		} else {
			if ($data['ended']) {
				$step = 'game_result';
			} else {
				if ($data['start_time']) {
					$step = 'game_running';
				} else {
					$step = 'invite_wait_response';
				}
			}
		}

		return $step;
	}
	
	/**
	 * 取得游戏Session数据
	 * 
	 * @return array
	 */
	public function getSession($sessId)
	{
		return Better_DAO_Game_Session::getInstance()->get($sessId);
	}
	
	/**
	 * 游戏同步
	 * 
	 * @return array
	 */
	public function sync($sessId='')
	{
		$data = array();
		
		if ($sessId) {
			$sessInfo = $this->getSession($sessId);
		} else {
			$sessInfo = Better_DAO_Game_Session::getInstance()->sync($this->uid);
		}

		if (isset($sessInfo['session_id'])) {
			$data = Better_DAO_DmessageReceive::getInstance($this->uid)->getLastGame($sessInfo['session_id'], $this->uid);
			if ($data['type']=='game_invite') {
				$data = array();	
			} else {
				$data['step'] = self::parseGameStep($sessInfo);
				$data['game_poi'] = $sessInfo['poi_id'] ? Better_Poi_Info::getInstance($sessInfo['poi_id'])->getBasic() : array();
			}
			
			$this->log($sessInfo['session_id'], 'SYNC_VALID');
		} else {
			$this->log('', 'SYNC_EMPTY');
		}
		
		return $data;
	}

	/**
	 * 忽略游戏的掉宝
	 * 
	 * @param string $sessId
	 */
	public function ignoreTreasures($sessId)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'SESSION_TIMEOUT' => -1,
			);
		$code = $codes['FAILED'];
		$msgId = 0;
		
		$sessInfo = $this->getSession($sessId);
		
		if ($sessInfo['session_id']) {
			if ((time()-$sessInfo['end_time'])>(int)Better_Config::getAppConfig()->game->pickup_timeout) {
				$code = $codes['SESSION_TIMEOUT'];
				
				$this->log($sessInfo['session_id'], 'IGNORE_TIMEOUT', __METHOD__);
			} else {
		
				$sender = $sessInfo['starter_uid']==$this->uid ? $sessInfo['coplayer_uid'] : $sessInfo['starter_uid'];
				$result = Better_User::getInstance($sender)->notification()->game()->send(array(
					'receiver' => $this->uid,
					'type' => 'game_over',
					'sid' => $sessInfo['session_id'],
					'content' => Better_Language::load()->api->game->treasure->ignored
					));

				if ($result['id']) {
					$this->log($sessInfo['session_id'], 'IGNORE_OK', __METHOD__);
					
					$msgId = $result['id'];
					$code = $codes['SUCCESS'];
					
					if (($sessInfo['starter_pickup']==1 || $sessInfo['coplayer_pickup']==1) || ($sessInfo['starter_pickup']==-1 || $sessInfo['coplayer_uid']==-1)) {
						$this->expireSess($sessInfo['session_id']);
					} else {
						$key = $sessInfo['starter_uid']==$this->uid ? 'starter_pickup' : 'coplayer_pickup';
						Better_DAO_Game_Session::getInstance()->updateByCond(array(
							$key => '-1',
						), array(
							'session_id' => $sessInfo['session_id'],
						));
					}
				}				
			}		
		} else {
			$this->log('', 'IGNORE_TIMEOUT', __METHOD__);
		}
		
		return array(
			'code' => $code,
			'codes' => &$codes,
			'msg_id' => $msgId,
			);
	}
	
	/**
	 * 让某个Session强制过期
	 * 
	 * @return null
	 */
	public function expireSess($sessId)
	{
		Better_DAO_Game_Session::getInstance()->updateByCond(array(
			'expired' => '1'
		), array(
			'session_id' => $sessId
		));
	}
	
	/**
	 * 自增捡起数目
	 * 
	 * @return bool
	 */
	public function increasePickups($sessId)
	{
		$data = $this->getSession($sessId);
		$flag = false;
		
		if (isset($data['session_id'])) {
			$starterUid = $data['starter_uid'];
			$coplayerUid = $data['coplayer_uid'];
			$pickups = $data['pickups'];
			$update = array(
				'pickups' => ($pickups<2) ? $pickups+1 : 2,
				);
				
			if ($this->uid==$starterUid) {
				$update['starter_pickup'] = 1;
			} else if ($this->uid==$coplayerUid) {
				$update['coplayer_pickup'] = 1;
			}

			Better_DAO_Game_Session::getInstance()->updateByCond($update, array(
				'session_id' => $sessId,
				));
				
			$flag = true;
		}

		return $flag;
	}
	
	/**
	 * 游戏中聊天
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function chat(array $params)
	{
		$data = array();
		$dao = Better_DAO_Game_Chat::getInstance();
		
		$sess = Better_DAO_Game_Session::getInstance();
		$sessInfo = $sess->get($params['session_id']);
		
		if ($sessInfo['ended']=='0') {
			$startTime = $sessInfo['start_time'];
			$endTime = $sessInfo['end_time'];
			
			$update = array(
				'last_update' => time(),
				);
			if ((($endTime-$startTime)<=(int)Better_Config::getAppConfig()->game->play_timeout) && (($endTime-$startTime)>=(int)Better_Config::getAppConfig()->game->min_timeout)) {
				$update['end_time'] = ($endTime-20)>180 ? $endTime-20 : 180;
				
				$this->log($sessInfo['session_id'], 'CHAT_MAKE_SESSION_SHORTER', __METHOD__);
			} else {
				$this->log($sessInfo['session_id'], 'CHAT_HASNT_MAKE_SESSION_SHORTER', __METHOD__);
			}
			
			$sess->updateByCond($update, array(
				'session_id' => $params['session_id'],
				));
			
			$receiverUid = $this->uid==$sessInfo['starter_uid'] ? $sessInfo['coplayer_uid'] : $sessInfo['starter_uid'];
			$tmp = $this->user->notification()->game()->send(array(
				'receiver' => $receiverUid,
				'type' => 'game_interactive',
				'sid' => $sessInfo['session_id'],
				'content' => $params['content'],
				));
				
			$receiverUser = Better_User::getInstance($receiverUid);
			$data = $receiverUser->notification()->game()->getReceived($tmp['id']);
			
			Better_Hook::factory(array(
				'Ppns',
				))->invoke('GameChat', array(
					'sender' => $this->uid,
					'receiver' => $receiverUid,
					'content' => $params['content'],
					'session_id' => $params['session_id'],
				));			
		}

		return $data;
	}
	
	/**
	 * 检测session有效性
	 * 
	 * @param string $sessId
	 * @return bool
	 */
	public function validSession($sessId)
	{
		$valid = false;
		
		$sessInfo = Better_DAO_Game_Session::getInstance()->get($sessId);
		if (isset($sessInfo['session_id']) && ($sessInfo['starter_uid']==$this->uid || $sessInfo['coplayer_uid']==$this->uid) && $sessInfo['ended']=='0') {
			$valid = true;
		}
		
		return $valid;
	}
	
	/**
	 * 响应请求
	 * 
	 * @param string $sessId
	 * @param string $response
	 * @return bool
	 */
	public function response($sessId, $response='accept')
	{
		$codes = array(
			'SUCCESS'=> 1,
			'FAILED' => 0,
			'YOU_ARE_PLAYING' => -1,
			'HE_IS_PLAYING' => -2,
			'SESSION_TIMEOUT' => -3,
			'SESSION_INVALID' => -4,
			'HAS_PENDING_INVITE' => -5,
			'SESSION_NOT_FOUND' => -6,
			);
		$code = $codes['FAILED'];
		$msgId = '';
		$now = time();
		$myMsg = array();
		$userInfo = $this->user->getUserInfo();

		if ($this->isGaming() && $response=='accept') {
			$code = $codes['YOU_ARE_PLAYING'];
			
			$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
		} else if ($this->hasPendingInvite() && $response=='accept') {
			$code = $codes['HAS_PENDING_INVITE'];
			
			$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
		} else {

			$sessInfo = $this->getSession($sessId);

			if (!isset($sessInfo['session_id'])) {
				$code = $codes['SESSION_NOT_FOUND'];
				
				$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
			} else {
				if ($sessInfo['coplayer_uid']==$this->uid) {
					$starterUid = $sessInfo['starter_uid'];
					$coplayerUid = $sessInfo['coplayer_uid'];
					
					if (self::getInstance($starterUid)->isGaming()) {
						$code = $codes['HE_IS_PLAYING'];	
						
						$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
					} else {
						if ($now-$sessInfo['create_time']>(int)Better_Config::getAppConfig()->game->invite_timeout) {
							$code = $codes['SESSION_TIMEOUT'];
							
							$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
						} else {
							$starter = Better_User::getInstance($starterUid);
							$coplayer = Better_User::getInstance($coplayerUid);

							switch ($response) {
								case 'accept':
									Better_DAO_Game_Session::getInstance()->updateByCond(array(
										'start_time' => time(),
										'last_update' => time(),
										'end_time' => time()+(int)Better_Config::getAppConfig()->game->play_timeout,
										), array(
											'session_id' => $sessId
										));
									$act = 'accept';
									
									$myContent = $coplayer->getUserLang()->api->game->response->accepted;
									$content = str_replace('{NICKNAME}', $userInfo['nickname'], $starter->getUserLang()->api->game->invite->accept->title);
									
									$this->rejectOthers($sessId);
									break;
								case 'reject':
								default:
									Better_DAO_Game_Session::getInstance()->deleteByCond(array(
										'session_id' => $sessId,
										));
									$act = 'reject';
									
									$myContent = $coplayer->getUserLang()->api->game->response->rejected;
									$content = str_replace('{NICKNAME}', $userInfo['nickname'], $starter->getUserLang()->api->game->invite->reject->title);
									break;
							}
							
							$result = $this->user->notification()->game()->send(array(
								'type' => 'game_'.$act,
								'receiver' => $starterUid,
								'content' => $content,
								'sid' => $sessId
								));
								
							if ($result['id']) {
								
								$code = $codes['SUCCESS'];
								$msgId = $result['id'];
								
								$tmp = $starter->notification()->game()->send(array(
									'type' => 'game_'.$act,
									'receiver' => $this->uid,
									'content' => $myContent,
									'sid' => $sessId,
									));
								$myMsg = $this->user->notification()->game()->getReceived($tmp['id']);
								Better_User::getInstance($this->uid)->notification()->game()->updateDelived((array)$tmp['id']);
								
								Better_Hook::factory(array(
									'Ppns',
									))->invoke('GameResponse', array(
										'uid' => $this->uid,
										'session_id' => $sessId,
										'starter_uid' => $starterUid,
										'coplayer_uid' => $this->uid,
										'response' => $response
									));										
								
								$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
							} else {
								$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
							}
						}
					}
				} else {
					$code = $codes['SESSION_INVALID'];
					$this->log($sessInfo['session_id'], 'RESPONSE_CODE_'.$code, __METHOD__);
				}
			}
		}

		return array(
			'code' => $code,
			'codes' => &$codes,
			'msg_id' => $msgId,
			'my' => &$myMsg
			);
	}
	
	/**
	 * 当前用户是否在玩游戏
	 * 
	 * @return bool
	 */
	public function isGaming()
	{
		return (bool)Better_DAO_Game_Session::getInstance()->isGaming($this->uid);
	}
	
	/**
	 * 用户是否有正在等待响应的邀请
	 * 
	 * @return bool
	 */
	public function hasPendingInvite()
	{
		$result = (bool)Better_DAO_Game_Session::getInstance()->hasPendingInvite($this->uid);
		return $result;
	}
	
	/**
	 * 
	 * 取消游戏邀请
	 * 
	 * @return array
	 */
	public function cancelInvite($sessId)
	{
		$codes = array(
			'SUCCESS' => 1,
			'FAILED' => 0,
			'INVITE_TIMEOUT' => -1,
			'ALREADY_STARTED' => -2,
			'GAME_ENDED' => -3
			);
		$code = $codes['FAILED'];
		
		if ($sessId) {
			$sessInfo = $this->getSession($sessId);
			if ($sessInfo['starter_uid']==$this->uid) {
				if ($sessInfo['expired']==0) {
					if ($sessInfo['start_time']==0) {
						$now = time();
						$offset = (int)Better_Config::getAppConfig()->game->invite_timeout;
						if (($now-$sessInfo['create_time'])<$now) {
							Better_DAO_Game_Session::getInstance()->deleteByCond(array(
								'session_id' => $sessId
								));
							$code = $codes['SUCCESS'];
						} else {
							$code = $codes['INVITE_TIMEOUT'];
						}
					} else {
						$code = $codes['ALREADY_STARTED'];
					}
				} else {
					$code = $codes['GAME_ENDED'];
				}
			}
		}
		
		return array(
			'code' => $code,
			'codes' => $codes
			);
	}
	
	/**
	 * 邀请某用户参加游戏
	 * 
	 * @param integer $uid 用户id
	 * @return array
	 */
	public function invite($uid)
	{
		$codes = array(
			'SUCCESS' => 1,
			'FAILED' => 0,
			'YOU_ARE_PLAYING' => -1,
			'HE_IS_PLAYING' => -2,
			'COPLAYER_INVALID' => -3,
			'DUPLICATE_INVITE' => -4,
			'REQUEST_IS_PEINDING' => -5,
			'CANT_SELF' => -6,
			'HE_HAS_PENDING_REQUEST' => -7
			);
		$code = $codes['FAILED'];
		$sessId = $msgId = '';

		if ($uid!=$this->uid) {
			//	当前用户是否在玩游戏
			if ($this->isGaming()) {
				$code = $codes['YOU_ARE_PLAYING'];
			} else {
				$uGame = self::getInstance($uid);
				$sess = Better_DAO_Game_Session::getInstance();
				
				if ($uGame->isGaming()) {
					$code = $codes['HE_IS_PLAYING'];
				} else if ($sess->hasPendingInvite($uid)) {
					$code = $codes['HE_HAS_PENDING_REQUEST'];
				} else {
					
					$uids = Better_DAO_Game_Hunting::getReadyUsers($this->uid);
					
					if (count($uids)>0 && in_array($uid, $uids)) {

						if ($sess->hasPendingInvite($this->uid)) {
							$code = $codes['REQUEST_IS_PEINDING'];
						} else {
							if ($sess->isInvited($this->uid, $uid)) {
								$code = $codes['DUPLICATE_INVITE'];
							} else {
								$userInfo = $this->user->getUserInfo();
								$poiId = $userInfo['last_checkin_poi'];
								
								$sessId = md5(uniqid(rand()));
								$sess->insert(array(
									'session_id' => $sessId,
									'starter_uid' => $this->uid,
									'coplayer_uid' => $uid,
									'create_time' => time(),
									'start_time' => 0,
									'last_update' => 0,
									'ended' => 0,
									'end_time' => 0,
									'game' => $this->name,
									'poi_id' => $poiId,
									));
									
								$inviteContent = Better_User::getInstance($uid)->getUserLang()->api->game->invite->content;
								$content = str_replace('{NICKNAME}', $userInfo['nickname'], $inviteContent);
								$result = Better_User::getInstance($this->uid)->notification()->game()->send(array(
									'sid' => $sessId,
									'content' => $content,
									'receiver' => $uid,
									'type' => 'game_invite',
									));
	
								if ($result['id']) {
									$msgId = $result['id'];
									$code = $codes['SUCCESS'];
									
									$coplayerUser = Better_User::getInstance($uid);
									$coplayerUserInfo = $coplayerUser->getUserInfo();
									
									$tmp = Better_User::getInstance($this->uid)->notification()->game()->send(array(
										'sid' => $sessId,
										'content' => str_replace('{NICKNAME}', $coplayerUserInfo['nickname'], Better_Language::load()->api->game->you_are_waiting_response),
										'receiver' => $this->uid,
										'type' => 'game_invite_wait_response'
										));
									if ($tmp['id']) {
										Better_User::getInstance($this->uid)->notification()->game()->updateDelived((array) $tmp['id']);
									}
									
									//	清理冗余的、给我的邀请
									if ($this->hasPendingInvite()) {
										$rows = Better_DAO_Game_Session::getInstance()->pendingInvites($this->uid);
										foreach ($rows as $row) {
											$pendingSessId = $row['session_id'];
											$pendingSessId && $this->response($pendingSessId, 'reject');
										}
									}
									
									Better_Hook::factory(array(
										'Ping', 'Ppns'
									))->invoke('GameInvite', array(
										'starter_uid' => $this->uid,
										'coplayer_uid' => $uid,
										'content' => $content,
										'session_id' => $sessInfo['session_id']
									));
								}
							}
						}
						
					} else {
						$code = $codes['COPLAYER_INVALID'];
					}
				}
			}
		} else {
			$code = $codes['CANT_SELF'];
		}

		return array(
			'code' => $code,
			'codes' => &$codes,
			'session_id' => $sessId,
			'msg_id' => $msgId,
			);
	}
	
	/**
	 * 获取可以参加挖宝游戏的用户
	 * 
	 * 用户报到后即可查看附近的(50KM--后端可调)内
	 * 最近(30分钟--后端可调)报到过的用户, 
	 * 应去除任一方向有阻止关系的人及放风次数已经用完的人.
	 * 
	 * @return array
	 */
	public function getReadyUsers($page=1, $count=BETTER_PAGE_SIZE)
	{
		$results = array(
			'count' => 0,
			'rows' => array(),
			);
		
		$uids = Better_DAO_Game_Hunting::getReadyUsers($this->uid);

		if (count($uids)>0) {
			$userInfo = $this->user->getUserInfo();
			$lon = $userInfo['lon'];
			$lat = $userInfo['lat'];
			
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUidsWithRange($uids, $page, $count, $lon, $lat);
			$rows = array();
			foreach ($tmp['rows'] as $row) {
				$row = $this->user->parseUser($row);
				$rows[] = $row;
			}
			$results['count'] = $tmp['total'];
			$results['rows'] = &$rows;
		}

		return $results;
	}
	
	/**
	 * 
	 * 不能寻宝的用户
	 */
	public function cantUids()
	{
		return array();
		/*
		$cantUids = Better_DAO_Game_Session::getInstance()->todayCantUids($this->name);
		
		return $cantUids;*/
	}
	
	protected function log($sessId, $msg, $method='')
	{
		if (Better_Config::getAppConfig()->game->log_enable) {
			Better_Log::getInstance()->logInfo('SessionId:['.$sessId.'], Msg:['.$msg.'], Method:['.$method.']', 'hunting');
		}
	}
	
	/**
	 * 获取我曾经跟谁一起玩过游戏
	 * 
	 * @return array
	 */
	public function playedWith($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$result = array(
			'rows' => array(),
			'count' => 0,
			);
			
		$tmp = Better_DAO_User_Treasure_Log::getinstance($this->uid)->getPlayedWith($page, $pageSize);
		$result['count'] = $result['count'];
		if ($tmp['count']>0 && count($tmp['rows'])>0) {
			$users = Better_DAO_User::getInstance()->getUsersByUids($tmp['rows'], 1, count($tmp['rows']));
			$datelines = array_flip($tmp['rows']);
			foreach ($users['rows'] as $row) {
				$result['rows'][$datelines[$row['uid']]] = Better_User::getInstance()->parseUser($row);
			}
			
			krsort($result['rows']);
		}
			
		return $result;
	}
	
	/**
	 * 
	 * 拒绝其他给我的邀请
	 * @param unknown_type $acceptSessId
	 */
	public function rejectOthers($acceptSessId)
	{
		$invites = $this->myValidInvites();
		
		foreach ($invites as $row) {
			if ($row['session_id']!=$acceptSessId) {
				$this->response($row['session_id'], 'reject');
			}
		}
	}
	
	/**
	 * 
	 * 免打扰设置
	 * @param unknown_type $minutes
	 */
	public function setDisturb($minutes)
	{
		$minutes = (int)$minutes;
		
		$dao = Better_DAO_User_Hunting_Silent::getInstance($this->uid);
		if ($minutes<=0) {
			//	删除免打扰设置
			$dao->deleteByCond(array(
				'uid' => $this->uid
				));
			$result = array(
				'dateline' => 0,
				'expire_time' => 0
				);
		} else {
			//	重设免打扰设置
			$dao->replace(array(
				'uid' => $this->uid,
				'dateline' => time(),
				'expire_time' => time()+$minutes*60
				));
			$result = array(
				'dateline' => time(),
				'expire_time' => time()+$minutes*60
				);
		}
		
		return $result;
	}
	
	/**
	 * 
	 * 获取免打扰设置
	 */
	public function getDisturb()
	{
		$result = Better_DAO_User_Hunting_Silent::getInstance($this->uid)->get($this->uid);
		return $result;
	}
}