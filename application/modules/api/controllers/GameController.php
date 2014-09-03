<?php

/**
 * 游戏API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_GameController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		if ($this->config->hunting->enabled==0) {
			$this->error('error.game.hunting_disabled');
		}
		
		$this->xmlRoot = 'user';
		
		$this->auth();
		
		$notAllowedState = array(
			Better_User_State::BANNED, 
			Better_User_State::MUTE,
			Better_User_State::SIGNUP_VALIDATING,
			Better_User_State::UPDATE_VALIDATING
			);		
			
		if ($this->userInfo['karma']<0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.karma_too_low');
		} else if ($this->user->isMuted()) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.you_are_muted');
		}
	}	
	
	/**
	 * 
	 */
	public function diggingAction()
	{
		switch ($this->todo) {
			case 'public_timeline':
				$this->_diggingPublicTimeline();
				break;
		}
		
		$this->output();
	}

	public function huntingAction()
	{
		
		switch ($this->todo) {
			case 'treasure':
				$this->_huntingTreasure();
				break;
			case 'update':
				$this->_huntingUpdate();
				break;
			case 'syn':
				$this->_huntingSyn();
				break;
			case 'response':
				$this->_huntingResponse();
				break;
			case 'invite':
				$this->_huntingInvite();
				break;
			case 'cancel_invite':
				$this->_huntingCancelInvite();
				break;
			case 'retrieve':
				$this->_huntingRetrieve();
				break;
			case 'playedwith':
				$this->_huntingPlayedWith();
				break;
			case 'received_invites':
				$this->_huntingReceivedInvites();
				break;
			case 'disturb':
				$this->_huntingDisturb();
				break;
			default:
				$params = $this->getRequest()->getParams();
				
				if (array_key_exists('treasure', $params)) {
					$this->_huntingTreasure();
				}
				break;
		}
		$this->output();
	}
	
	/**
	 * 获取谁跟我一起玩过游戏
	 */
	private function _huntingPlayedWith()
	{
		$this->xmlRoot = 'players';
		$rows = Better_Game::factory('hunting', $this->uid)->playedWith($this->page, $this->count);
		
		foreach ($rows['rows'] as $dateline=>$row) {
			$this->data[ $this->xmlRoot ][] = array(
				'player' => $this->api->getTranslator('playedwith')->translate(array(
										'data' => &$row,
										'dateline' => $dateline,
										'userInfo' => $this->userInfo
										))
				);
		}
		
		$this->output();
	}
	
	/**
	 * 宝物
	 */
	private function _huntingTreasure()
	{
		$key = $this->getRequest()->getParam('treasure', '');
		list($todo, $format) = explode('.', $key);

		if ($todo && $format) {
			$this->todo = $todo;
			$this->format = $format;

			switch ($this->todo) {
				case 'exchange':
					$this->_huntingTreasureExchange();
					break;
				case 'exchange_timeline':
					$this->_huntingTreasureExchangeTimeline();
					break;
				case 'history':
					$this->_huntingTreasureHistory();
					break;
				case 'throwadd':
					$this->_huntingTreasureThrowadd();
					break;
				case 'throw':
					$this->_huntingTreasureThrow();
					break;
				case 'add':
					$this->_huntingTreasureAdd();
					break;
				case 'ignore':
					$this->_huntingTreasureIgnore();
					break;
				case 'exchange_history':
					$this->_huntingTreasureExchangeHistory();
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->serverError();
		}
	}
	
	/**
	 * 11.11兑换宝物
	 */
	private function _huntingTreasureExchange()
	{
		$this->xmlRoot = 'response';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			$treasure = Better_Treasure::getInstance($id);
			$treasureInfo = $treasure->getInfo();
			
			if ($treasureInfo['id']) {
				$myTreasures = $this->user->treasure()->getMyTreasures();
				
				if (in_array($id, array_keys($myTreasures))) {
					if ($treasure->canExchange()) {
						if ($this->user->treasure()->exchange($id)) {
							$this->data[$this->xmlRoot] = array(
								'message' => $this->lang->game->treasure->exchange->pending,
								'treasure' => $this->api->getTranslator('treasure')->translate(array(
									'data' => &$myTreasures[$id],
									'userInfo' => &$this->userInfo,
									))
								);
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.game.digging.exchangeexpire');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.treasure_cannot_exchange');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.treasure_not_yours');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.invalid_treasure');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_treasure');
		}
	}
	
	/**
	 * 11.14 某期宝物兑换结果
	 * 
	 */
	private function _huntingTreasureExchangeHistory()
	{
		$this->xmlRoot = 'exchange_history';
		$time = (int)trim($this->getRequest()->getParam('time', ''));
		if ($time=='') {
			$d = date('d');
			if ($d>=28) {
				$time = date('Ym');
			} else {
				$time = date('Ym', time()-3600*24*29);
			}
		}
		
		$rows = Better_Game_Exchange::getExchangeHistory($time);

		foreach ($rows as $row) {
			$this->data[$this->xmlRoot][] = array(
				'history' => $this->api->getTranslator('treasure_exchange_history')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo
					))
				);
		}
	}
	
	/**
	 * 11.10兑换大厅
	 */
	private function _huntingTreasureExchangeTimeline()
	{
		$this->xmlRoot = 'exchange_hall';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		$treasure = (bool)($_REQUEST['treasure']=='true' ? true : false);
		$public = (bool)($this->getRequest()->getParam('public', 'false')=='true' ? true : false);
		$mine = (bool)($this->getRequest()->getParam('mine', 'false')=='true' ? true : false);
		
		$data = array(
			'candidates' => array(),
			'history_public' => array(),
			'history_mine' => array(),
			);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				//	treasure
				if ($treasure===true) {
					$rows = $user->treasure()->getCanExchangeTreasures();
					foreach ($rows as $row) {
						$data['candidates'][] = array(
							'treasure_exchange' => $this->api->getTranslator('treasure_exchange')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
				
				// public
				if ($public===true) {
					$rows = Better_Treasure::getExchange();
					foreach ($rows as $row) {
						$data['history_public'][] = array(
							'treasure_exchange' => $this->api->getTranslator('treasure_exchange')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
				
				// mine
				if ($mine===true) {
					$rows = $this->user->treasure()->getMyExchangeHistory();
					foreach ($rows['rows'] as $row) {
						$data['history_mine'][] = array(
							'treasure_exchange' => $this->api->getTranslator('treasure_exchange')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_user');
		}
		
		$this->data[$this->xmlRoot] = &$data;
	}
	
	/**
	 * 11.9宝物轮转历史
	 */
	private function _huntingTreasureHistory()
	{
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->xmlRoot = 'hunted_history';
		
		if ($id>0) {
			$this->count = 18;
			$rows = $this->user->treasure()->logs($id, $this->page, $this->count);
			foreach ($rows['rows'] as $row) {
				$this->data[$this->xmlRoot][] = array(
					'hunted' => $this->api->getTranslator('hunted')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_treasure');
		}
	}
	
	/**
	 * 新版11.8
	 */
	private function _huntingTreasureThrow()
	{
		$this->xmlRoot = 'treasure';
		$throwId = (int)$this->getRequest()->getParam('throw_id', 0);
		$sessId = trim($this->getRequest()->getParam('session_id', ''));
		$poiId = $coUid = 0;
		
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			$sessInfo = Better_Game::factory('hunting', $this->uid)->getSession($sessId);
			$poiId = $sessInfo['poi_id'];
			if ($this->uid==$sessInfo['starter_uid']) {
				$coUid = $sessInfo['coplayer_uid'];
			} else {
				$coUid = $sessInfo['starter_uid'];
			}
		}
				
		$treasures = $this->user->treasure()->getMyTreasures();
		
		$result = $this->user->treasure()->chuck(array(
			'treasure_id' => $throwId,
			'poi_id' => $poiId,
			'co_uid' => $coUid
			));
		$codes = &$result['codes'];
		
		switch ($result['code']) {
			case $codes['HAVNT']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.you_havnt_this_treasure');
				break;
			case $codes['SUCCESS']:
				$this->data[$this->xmlRoot] = $this->api->getTranslator('treasure')->translate(array(
							'data' => &$treasures[$throwId],
							'userInfo' => &$userInfo,
							));
				break;
			case $codes['FAILED'];
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
				break;
		}
	}
	
	/**
	 * 11.8扔掉并添加宝物
	 */
	private function _huntingTreasureThrowadd()
	{
		$this->getRequest()->setParam('id', $this->getRequest()->getParam('add_id', 0));
		$this->_huntingTreasureAdd($this->getRequest()->getParam('throw_id', 0));
	}
	
	/**
	 * 忽略游戏掉的宝
	 * 
	 * @return
	 */
	private function _huntingTreasureIgnore()
	{
		$this->xmlRoot = 'notification';
		$sessId = trim($this->getRequest()->getParam('session_id', ''));
		
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			$game = Better_Game::factory('hunting', $this->uid);
			
			$result = $game->ignoreTreasures($sessId);
			$codes = &$result['codes'];

			switch ($result['code']) {
				case $codes['SESSION_TIMEOUT']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.session_timeout');
					break;
				case $codes['SUCCESS']:
					if ($result['msg_id']) {
						$data = Better_User::getInstance($this->uid)->notification()->game()->getReceived($result['msg_id']);
						
						Better_User::getInstance($this->uid)->notification()->game()->updateDelived((array)$result['msg_id']);
						
						$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
							'data' => &$data,
							'userInfo' => &$this->userInfo,
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
					}
					break;	
				case $codes['FAILED']:
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_session_id');
		}
	}
	
	/**
	 * 11.7添加宝物到宝物箱
	 */
	private function _huntingTreasureAdd()
	{
		$this->xmlRoot = 'mytreasures';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$sessId = trim($this->getRequest()->getParam('session_id', ''));
		
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			$game = Better_Game::factory('hunting', $this->uid);
			$sessInfo = $game->getSession($sessId);
			
			if (time()-$sessInfo['end_time']<(int)Better_Config::getAppConfig()->game->pickup_timeout) {
			
				if ($sessInfo['starter_uid']==$this->uid || $sessInfo['coplayer_uid']==$this->uid) {
					$tid = $this->uid==$sessInfo['starter_uid'] ? $sessInfo['starter_treasure'] : $sessInfo['coplayer_treasure'];
					
					if ($tid) {
						$myTreasure = Better_Treasure::getInstance($tid);
						$treasureInfo = $myTreasure->getInfo();
						
						if ($treasureInfo['id']) {
							$myTreasures = $this->user->treasure()->getMyTreasures();

							if (count($myTreasures)<3) {
								$result = $this->user->treasure()->pickup(array(
									'treasure_id' => $tid,
									'co_uid' => $sessInfo['coplayer_uid'],
									'poi_id' => $sessInfo['poi_id'],
									'throw_tid' => $throwTid,
									));
	
								$codes = &$result['codes'];
								switch ($result['code']) {
									case $codes['ALREADY_HAVE']:
										$this->errorDetail = __METHOD__.':'.__LINE__;
										$this->error('error.game.duplicate_treasure');
										break;
									case $codes['TOO_MANY']:
										$this->errorDetail = __METHOD__.':'.__LINE__;
										$this->error('error.game.digging.treasureoverflow');
										break;
									case $codes['SUCCESS']:

										if ($sessInfo['starter_uid']==$this->uid) {
											
											Better_User::getInstance($sessInfo['coplayer_uid'])->notification()->game()->send(array(
												'receiver' => $this->uid,
												'type' => 'game_over',
												'sid' => $sessId,
												'content' => $this->lang->game->session->over,
												));											
										} else {

											Better_User::getInstance($sessInfo['starter_uid'])->notification()->game()->send(array(
												'receiver' => $this->uid,
												'type' => 'game_over',
												'sid' => $sessId,
												'content' => $this->lang->game->session->over,
												));															
										}

										
										$game->increasePickups($sessId);
										
										$ts = $this->user->treasure()->getMyTreasures(true);
										foreach ($ts as $row) {
											$this->data[$this->xmlRoot][] = array(
												'mytreasure' => $this->api->getTranslator('mytreasure')->translate(array(
													'data' => &$row,
													'userInfo' => &$this->userInfo,
													)),
												);
										}

										break;
									case $codes['FAILED']:
									default:
										$this->errorDetail = __METHOD__.':'.__LINE__;
										$this->serverError();
										break;
								}
							} else {
								$this->errorDetail = __METHOD__.':'.__LINE__;
								$this->error('error.game.too_many_treasures');
							}
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.game.invalide_treasure');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.session_no_treasure');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.invalid_session');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.session_timeout');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_session_id');
		}
	}
	
	/**
	 * 11.6内部对话
	 */
	private function _huntingUpdate()
	{
		$this->needPost();
		$this->xmlRoot = 'notification';
		$data = array();
		
		if ($this->user->isMuted()) {
			$this->error('error.user.you_are_muted');
		}		
		
		$text = trim(urldecode($this->getRequest()->getParam('text', '')));
		$sessId = trim($this->getRequest()->getParam('session_id', ''));
		
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			if (strlen($text)) {
				$game = Better_Game::factory('hunting', $this->uid);
				$data = $game->chat(array(
					'session_id' => $sessId,
					'content' => $text,
					));
					
				if (count($data)>0) {		
					$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
						'data' => &$data,
						'userInfo' => $this->userInfo,
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.is_ended');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.text_required');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_session_id');
		}
	}
	
	/**
	 * 11.5游戏同步、恢复
	 */
	private function _huntingSyn()
	{
		$this->xmlRoot = 'notification';
		$sessId = strtolower(trim($this->getRequest()->getParam('session_id', '')));
		
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			$data = Better_Game::factory('hunting', $this->uid)->sync($sessId);
		} else {
			$data = Better_Game::factory('hunting', $this->uid)->sync();
		}

		if (is_array($data) && count($data)>1) {
			$userInfo = $this->user->getUserInfo();
			$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
				'data' => &$data,
				'userInfo' => &$userInfo
				));
		}	
	}
	
	/**
	 * 11.4接受、拒绝邀请
	 */
	private function _huntingResponse()
	{
		$this->xmlRoot = 'notification';
		$this->needPost();
		
		$sessId = strtolower(trim($this->getRequest()->getParam('session_id', '')));
		$response = $this->getRequest()->getParam('response', '');

		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			if ($response=='accept' || $response=='reject') {
				$result = Better_Game::factory('hunting', $this->uid)->response($sessId, $response);
				$codes = &$result['codes'];

				switch ($result['code']) {
					case $codes['SUCCESS']:
						$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
							'data' => &$result['my'],
							'userInfo' => &$this->userInfo,
							));
						break;
					case $codes['YOU_ARE_PLAYING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.you_are_playing');
						break;
					case $codes['HE_IS_PLAYING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.he_is_playing');
						break;
					case $codes['SESSION_TIMEOUT']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.session_timeout');
						break;
					case $codes['SESSION_INVALID']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.session_invalid');
						break;
					case $codes['SESSION_NOT_FOUND']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.session_not_found');
						break;
					case $codes['HAS_PENDING_INVITE']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.you_are_invite_others');
						break;
					case $codes['FAILED']:
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.invalid_response');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_session_id');
		}
	}
	
	/**
	 * 
	 * 11.15 我收到的寻宝邀请
	 */
	private function _huntingReceivedInvites()
	{
		$this->xmlRoot = 'notifications';
		
		
	}
	
	/**
	 * 
	 * 取消寻宝邀请
	 */
	private function _huntingCancelInvite()
	{
		$this->xmlRoot = 'message';
		
		$sessId = strtolower(trim($this->getRequest()->getParam('session_id', '')));
		if (preg_match('/^([a-z0-9]{32})$/', $sessId)) {
			$result = Better_Game::factory('hunting', $this->uid)->cancelInvite($sessId);
			$cs = &$result['codes'];
			switch ($result['code']) {
				case $cs['SUCCESS']:
					$this->data[$this->xmlRoot] = $this->lang->game->invite_canceled;
					break;
				case $cs['INVITE_TIMEOUT']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.session_timeout');
					break;
				case $cs['ALREADY_STARTED']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.is_end2');
					break;
				case $cs['GAME_ENDED']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.session_timeout');
					break;
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.game.cancel_failed');
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_cancel_session_id');			
		}
	}
	
	/**
	 * 11.3邀请用户寻宝
	 */
	private function _huntingInvite()
	{
		$this->xmlRoot = 'notification';
		
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']>0) {
				$result = Better_Game::factory('hunting', $this->uid)->invite($id);
				$codes = &$result['codes'];

				switch ($result['code']) {
					case $codes['DUPLICATE_INVITE']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.duplicate_invite');
						break;
					case $codes['YOU_ARE_PLAYING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.you_are_playing');
						break;
					case $codes['HE_IS_PLAYING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.he_is_playing');
						break;
					case $codes['HE_HAS_PENDING_REQUEST']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.he_has_pending_request');
						break;
					case $codes['COPLAYER_INVALID']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.coplayer_invalid');
						break;
					case $codes['HAS_PENDING_INVITE']:
					case $codes['REQUEST_IS_PEINDING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.has_pending_invite');
						break;
					case $codes['CANT_SELF']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.game.cant_invite_self');
						break;
					case $codes['SUCCESS']:
						$sessId = $result['session_id'];
						$msgId = $result['msg_id'];
						$data = Better_User::getInstance($id)->notification()->game()->getReceived($msgId);

						$data['msg'] = str_replace('{NICKNAME}', $userInfo['nickname'], $this->lang->game->invite->success);
						$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
							'data' => &$data,
							));
						break;
					case $codes['FAILED']:
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_user');
		}
	}
	
	/**
	 * 11.2可供邀请用户列表
	 * 用户报到后即可查看附近的(50KM--后端可调)内
	 * 最近(30分钟--后端可调)报到过的用户,
	 *  应去除任一方向有阻止关系的人及放风次数已经用完的人
	 * 
	 */
	private function _huntingRetrieve()
	{
		$this->xmlRoot = 'users';
		$game = Better_Game::factory('hunting', $this->uid);
		$cantUids = $game->cantUids();
		
		if ($this->userInfo['checkins']>0 && $this->userInfo['last_checkin_poi']) {
			if (in_array($this->uid, $cantUids)) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.cant_join');
			} else {
				$rows = $game->getReadyUsers($this->page, $this->count);
				if ($rows['count']>0) {
					foreach ($rows['rows'] as $row) {
						$this->data[$this->xmlRoot][] = array(
							'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
								'data' => &$row,
								'userInfo' => &$this->userInfo,
								)),
							);		
					}
				}
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.plz_checkin_first');
		}
	}
	
	private function _huntingDisturb()
	{
		$this->xmlRoot = 'hunting_disturb';
		$minutes = (int)$this->getRequest()->getParam('minutes', 0);
		
		$result = Better_Game::factory('hunting', $this->uid)->setDisturb($minutes);
		$this->data[$this->xmlRoot] = $this->api->getTranslator('hunting_disturb')->translate(array(
			'data' => &$result
			));
		
		$this->output();
	}
	
	/**
	 * 11.1寻宝空间
	 */
	private function _diggingPublicTimeline()
	{
		$this->xmlRoot = 'hunting_space';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$game = Better_Game::factory('hunting', $this->uid);
				
				$this->data[$this->xmlRoot]['user'] = $this->api->getTranslator('user')->translate(array(
					'data' => &$userInfo,
					));
					
				$myState = array();
				
				if ($id==$this->uid) {
					$treasureMsg = '';
					$state = '';
					
					if ($this->user->isActive()) {
						if ($this->userInfo['cell_no']) {
							$treasureMsg = $this->lang->treasure->message->no3;
							$state = '1';
						} else {
							$treasureMsg = $this->lang->treasure->message->no2;
							$state = '0';
						}
					} else {
						$treasureMsg = $this->lang->treasure->message->no1;
						$state = '-1';
					}
					
					$myState['message'] = $treasureMsg;
					$myState['state'] = $state;
				}
				
				$this->data[$this->xmlRoot]['mystate'] = $myState;
					
				$this->data[$this->xmlRoot]['mytreasures'] = array();
				$ts = $user->treasure()->getMyTreasures(true);

				foreach ($ts as $row) {
					$this->data[$this->xmlRoot]['mytreasures'][] = array(
						'mytreasure' => $this->api->getTranslator('mytreasure')->translate(array(
							'data' => &$row,
							'userInfo' => &$userInfo,
							)),
						);
				}

				$disturb = $game->getDisturb();
				
				$this->data[$this->xmlRoot]['hunting_disturb'] = $this->api->getTranslator('hunting_disturb')->translate(array(
					'data' => &$disturb
					));				
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.game.user_not_found');
			}
			
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.game.invalid_user');
		}
	}
}