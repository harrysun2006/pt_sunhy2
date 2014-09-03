<?php

/**
 * Server组特定api
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_S2sController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		self::limitServerIpSource();
	}		
	
	/**
	 * 取通知
	 * 
	 * @return
	 */
	public function notifyAction()
	{
		$this->xmlRoot = 'notifies';
		$this->needPost();
		$robot = $this->getRequest()->getParam('robot', '');
		
		if ($robot) {
			$tmp = Better_Robot_Msn::getMyNotifies($robot);
			$result = array();
			foreach ($tmp as $row) {
				$result[$row['type']][] = $row;
			}

			foreach($result as $key=>$rows) {
				$this->data[$this->xmlRoot][$key] = array();
				
				foreach ($rows as $row) {
					$this->data[$this->xmlRoot][$key][] = $this->api->getTranslator('robot_notify')->translate(array(
							'data' => &$row,
							));
				}
			}

		} else {
			$this->errorDetail = __METHOD__.':'.__LINE_;
			$this->error('error.s2s.robot_not_provide');
		}		
		
		$this->output();
	}
	
	/**
	 * 执行指令
	 * 
	 * @return
	 */
	public function commandAction()
	{
		$this->xmlRoot = 'robot';

		$this->needPost();
		$identify = trim($this->post['identify']);
		$source = trim($this->post['source']);
		$command = strtolower(trim($this->post['command']));
		$username = isset($this->post['username']) ? $this->post['username'] : '';
		$content = isset($this->post['content']) ? $this->post['content'] : '';
		$bid = isset($this->post['bid']) ? $this->post['bid'] : 0;
		$params = array(
			'username' => $username,
			'content' => $content,
			'bid' => $bid,
			);
		
		switch ($source) {
			case 'gtalk':
			case 'msn':
				$user = Better_User::getInstance($identify, $source);
				$userInfo = $user->getUser();
				if ($userInfo['uid']) {
					Better_Registry::get('sess')->init(false);
					Better_Registry::get('sess')->set('uid', $userInfo['uid']);
					Better_Registry::get('sess')->set('user', $userInfo);
					Better_Registry::set('user', Better_User::getInstance($userInfo['uid']));		
											
					$robot = Better_Robot::getInstance($userInfo['uid'], $source);
					if ($robot instanceof Better_Robot_Base) {
						$return = $robot->execCommand($command, $params) ? 'true' : 'false';

						$error = $robot->getError();
						switch ($error) {
							case Better_Robot_Base::COMMAND_NOT_SUPPORTED:
								$this->errorDetail = __METHOD__.':'.__LINE_;
								$this->error('error.s2s.command_not_supported');
								break;
							case Better_Robot_Base::COMMAND_NOT_IMPLEMENT:
								$this->errorDetail = __METHOD__.':'.__LINE_;
								$this->error('error.s2s.command_not_implement');
								break;
							case '':
							default:
								$this->data[$this->xmlRoot] = $this->api->getTranslator('robot_msg')->translate(array(
									'data' => array(
										'command' => $command,
										'result' => $return,
										'error' => $error,
										'msg' => $robot->getMessage(),
										),
									));
								break;
						}									
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.im_not_bindded');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.user_auth_failed');
				}
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE_;
				$this->error('error.s2s.source_not_permitted');
				break;
		}		
		
		$this->output();
	}
	
	/**
	 * 更新短信猫池
	 * 
	 * @return
	 */
	public function modemsAction()
	{
		$this->needPost();
		$xml = trim($this->post['status']);
		$xml=='' && $xml = $this->getRequest()->getRawBody();
		
		if ($xml) {
			Better_Modem_Pool::updatePool($xml);
			
			header('Content-Length: '.strlen($xml));
			header('Content-Type: text/xml; charset=utf-8');
			echo $xml;
	
			exit;			
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.s2s.format_invalid');
		}
		
		$this->output();
	}
	
	/**
	 * 更新单个短信猫
	 * 
	 * @return
	 */
	public function singlemodemAction()
	{
		$cell = trim(urldecode($this->getRequest()->getParam('cell', '')));
		$status = $this->getRequest()->getParam('status', 'on')=='off' ? 'off' : 'on';
		$this->xmlRoot = 'message';
		
		if ($cell) {
			Better_Modem_Pool::updateSinglePool($cell, $status);
			$this->data[$this->xmlRoot] = 'ok';
		} else {
			$this->error('error.s2s.format_invalid');
		}
		
		$this->output();
	}
	
	/**
	 * 机器人
	 * 
	 * @return
	 */
	public function robotsAction()
	{
		$this->needPost();
		$xml = trim($this->post['status']);
		$xml=='' && $xml = $this->getRequest()->getRawBody();
		
		if ($xml) {
			Better_MsnRobots::updateList($xml);
			
			header('Content-Length: '.strlen($xml));
			header('Content-Type: text/xml; charset=utf-8');
			echo $xml;
	
			exit;
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE_;
			$this->error('error.s2s.format_invalid');
		}		
		
		$this->output();
	}
	
	/**
	 * 兼容拼写错误的Action
	 * 
	 * @return
	 */
	public function rebotsAction()
	{
		$this->robotsAction();
		exit(0);
	}
	
	/**
	 * 发微博
	 * 
	 * @return
	 */
	public function statusesAction()
	{
		$this->needPost();
		$this->xmlRoot = 'status';
		$status = trim(urldecode($this->post['status']));

		if ($status=='') {
			$this->errorDetail = __METHOD__.':'.__LINE_;
			$this->error('error.s2s.status_required');
		}
		
		$source = $this->post['source'];
		$in_reply_to_status_id = $this->post['in_reply_to_status_id'];
		$identify = urldecode($this->post['identify']);
		
		if ($identify) {
			switch ($source) {
				case 'sms':
				case 'mms':
					$user = Better_User::getInstance($identify, 'cell');
					break;
				case 'msn':
				case 'gtalk':
					$user = Better_User::getInstance($identify, $source);
					break;
				default :
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.source_not_permitted');
					break;
			}
		} else {
			$this->error('error.s2s.identifiy_required');
		}
		$userInfo = $user->getUser();
		
		//	试图执行绑定操作
		if (!$userInfo['uid'] && $source=='msn') {
			if (in_array($source, Better_User_Bind_Im::$allowedProtocols)) {
				$uid = Better_User_Bind_Im::hasRequest($identify);
				$user = Better_User_Bind_Im::getInstance($uid)->bind($identify);
					
				if ($user instanceof Better_User) {
					$userInfo = $user->getUser();					
				}				
			}
		}

		if (!$userInfo['uid']) {
			$this->addLogMsg = $identify;
			$this->error('error.s2s.user_auth_failed');
		} else {
			Better_Registry::get('sess')->init(false);
			Better_Registry::get('sess')->set('uid', $userInfo['uid']);
			Better_Registry::get('sess')->set('user', $userInfo);
			Better_Registry::set('user', Better_User::getInstance($userInfo['uid']));
			
			$checkCommand = Better_Robot::isCommand($status);
			
			if ($checkCommand['command']!='') {
				$this->xmlRoot = 'robot';
				
				$command = $checkCommand['command'];
				$params = array(
					'username' => $checkCommand['username'],
					'content' => $checkCommand['content'],
					'bid' => isset($this->post['bid']) ? $this->post['bid'] : 0,
					'commandUid' => $userInfo['uid'],
					);

				$robot = Better_Robot::getInstance($userInfo['uid'], $source);
				if ($robot instanceof Better_Robot_Base) {
					$return = $robot->execCommand($command, $params) ? 'true' : 'false';

					$error = $robot->getError();
					$errorStr = '';
					switch ($error) {
						case Better_Robot_Base::COMMAND_NOT_SUPPORTED:
							$errorStr = 'error.s2s.command_not_supported';
							break;
						case Better_Robot_Base::COMMAND_NOT_IMPLEMENT:
							$errorStr = 'error.s2s.command_not_implement';
							break;
						case Better_Robot_Base::COMMAND_PARAM_NOT_VALID:
							$errorStr = 'error.s2s.command_param_not_valid';
							break;
					}

					$this->data[$this->xmlRoot] = $this->api->getTranslator('robot_msg')->translate(array(
						'data' => array(
							'command' => $command,
							'result' => $return,
							'error' => $errorStr,
							'msg' => $robot->getMessage(),
							),
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.im_not_bindded');
				}					
						
			} else {
							
				$photo = '';
				if (is_array($_FILES) && isset($_FILES['photo'])) {
					$at = Better_Attachment::getInstance('photo');
					$newFile = $at->uploadFile('photo');

					if (is_object($newFile) && ($newFile instanceof Better_Attachment)) {
						$result = $newFile->parseAttachment();
					} else {
						$result = &$newFile;
					}

					if (is_array($result) && $result['file_id']) {
						$photo = $result['file_id'];
					} else if (count(explode('.', $result))==2) {
						$photo = $result;
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.upload.code_'.$result);
					}
				}
			
				$poiId = $userInfo['last_checkin_poi'] ? (int)$userInfo['last_checkin_poi'] : 0;
				$bid = Better_Blog::post($userInfo['uid'], array(
								'message' => $status,
								'upbid' => $in_reply_to_status_id,
								'attach' => $photo,
								'source' => $source,
								'poi_id' => $poiId
								));
				$blog = Better_Blog::getBlog($bid);
				$blog['user']['user_lon'] = $blog['user']['lon'];
				$blog['user']['user_lat'] = $blog['user']['lat'];

				if ($bid==-1 || $bid==-2 || $bid==-3 || $bid==-4 || $bid==-5 || $bid==-6) {
						
						switch ($bid) {
							case -1:
								$msg = $this->lang->error->statuses->post_need_check;
								$code = 'error.statuses.post_need_check';
								break;
							case -2:
							case -4:
								$msg = $this->lang->error->user->you_are_muted;
								$code = 'error.user.you_are_muted';
								break;
							case -3:
								$msg = $this->lang->error->users->account_banned;
								$code = 'error.users.account_banned';
								break;
							case -5:
								//error.statuses.post_too_fast
								$msg = $this->lang->error->statuses->post_too_fast;
								$code = 'error.statuses.post_too_fast';
								break;
							case -6:
								$msg = $this->lang->error->statuses->post_same_content;
								$code = 'error.statuses.post_same_content';
								break;
							default:
								$msg = $this->lang->error->statuses->post_need_check;
								$code = 'error.statuses.post_need_check';
								break;
						}						

					if ($source=='msn' || $source=='gtalk') {
						$this->xmlRoot = 'robot';

						$this->data[$this->xmlRoot] = $this->api->getTranslator('robot_msg')->translate(array(
							'data' => array(
								'command' => 'update',
								'result' => 'false',
								'error' => '',
								'msg' => $msg,
								),
							));
					} else {
						$this->error($code);
					}
				} else {
					if ($source=='msn' || $source=='gtalk') {
						$this->xmlRoot = 'robot';
						$msg = $this->langAll->robot->post_success;
						
						$this->data[$this->xmlRoot] = $this->api->getTranslator('robot_msg')->translate(array(
							'data' => array(
								'command' => 'update',
								'result' => $bid ? 'true' : 'false',
								'error' => '',
								'msg' => $msg,
								'bid' => $bid ? $bid : '',
								),
							));
					} else {
						$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
							'data' => array_merge($blog['blog'], $blog['user']),
							'userInfo' => &$blog['user'],
							));
					}
				}
			}
		}		
		
		$this->output();
	}
	
	/**
	 * 帐号绑定操作
	 * 
	 * @return
	 */
	public function bindAction()
	{
		switch ($this->todo) {
			case 'mobile':
				$this->_bindMobile();
				break;
			case 'im':
				$this->_bindIm();
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE_;
				$this->error('error.request.not_found');
		}
		
		$this->output();
	}
	
	/**
	 * 绑定手机
	 * 
	 * @return
	 */
	private function _bindMobile()
	{
		$this->xmlRoot = 'user';
		
		$code = $this->getRequest()->getParam('code', '');
		$identify = $this->getRequest()->getParam('identify', '');
		//Better_Log::getInstance()->Loginfo($code."|".$identify,'xxxxxxxx');
		if ($code && $identify) {
			
			//	如果是用户主动请求绑定
			if (preg_match('/^bd([0-9]{1,20})$/i', $code)) {
				$code = intval(trim(preg_replace('/^bd([0-9]{1,20})$/i', '\1', $code)));
			
				$uid = Better_User_Bind_Cell::hasRequest($identify);
				if ($uid) {
					$user = Better_User_Bind_Cell::getInstance($uid)->bind($identify);
					
					if ($user instanceof Better_User) {
						$userInfo = $user->getUser();
						$this->data[$this->xmlRoot] = $this->api->getTranslator('user')->translate(array(
							'data' => &$userInfo,
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.cell_bind_failed');
					}						
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.cell_not_in_request');
				}				
			} else if (preg_match('/^([a-zA-Z0-9]){32}$/', $code)) {
				//	如果是自动一键绑定
				$uid = Better_User_Bind_Cell::hasSeq($code);
				
				if ($uid) {
					//检查手机号是否已经绑定过了
					$ck = Better_User_Bind_Cell::getInstance($uid)->checkCell($identify);
					if (!$ck) {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.cell_bind_failed_cellno');						
					}
					
					$user = Better_User_Bind_Cell::getInstance($uid)->bind($identify, true);
					
					if ($user instanceof Better_User) {
						$userInfo = $user->getUser();
						$this->data[$this->xmlRoot] = $this->api->getTranslator('user')->translate(array(
							'data' => &$userInfo,
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.cell_bind_failed');
					}						
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.cell_not_in_request');					
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE_;
				$this->error('error.s2s.params_invalid');
			}

		} else {
			$this->errorDetail = __METHOD__.':'.__LINE_;
			$this->error('error.s2s.params_invalid');
		}	
		
	}
	
	/**
	 * 绑定IM
	 * 
	 * @return
	 */
	private function _bindIm()
	{
		$source = $this->getRequest()->getParam('source', '');
		$identify = $this->getRequest()->getParam('identify', '');
		$this->xmlRoot = 'user';
		
		if ($identify) {
			if (in_array($source, Better_User_Bind_Im::$allowedProtocols)) {
				$uid = Better_User_Bind_Im::hasRequest($identify);
				if ($uid) {
					$user = Better_User_Bind_Im::getInstance($uid)->bind($identify);
					
					if ($user instanceof Better_User) {
						$userInfo = $user->getUser();
						$this->data[$this->xmlRoot] = $this->api->getTranslator('user')->translate(array(
							'data' => &$userInfo,
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE_;
						$this->error('error.s2s.identify_not_valid');
					}										
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE_;
					$this->error('error.s2s.im_not_in_request');
				}

			} else {
				$this->errorDetail = __METHOD__.':'.__LINE_;
				$this->error('error.s2s.source_not_permitted');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE_;
			$this->error('error.s2s.user_auth_failed');
		}		
	}

	/**
	 * 短信注册2 -- 请求串号及Modem号码
	 */
	public function smsreg2Action()
	{
		$this->needPost();
		$this->xmlRoot = 'register';

		$from = $this->getRequest()->getParam('from', '');
		$msg = $this->getRequest()->getParam('content', '');
		$pat = '#^\#(reg)\#([0-9a-f]{32})$#';
		$r = preg_match($pat, $msg, $m);

		if ($r && $m[2]) {
		  // 2011-09-14: 限制同一token发多次引起的bug
		  $regcell = Better_DAO_Cell::getInstance()->get($msg);
		  if ($regcell && count($regcell) > 0 && $regcell['flag'] == 1 && !$regcell['cell']) {
		    Better_DAO_Cell::getInstance()->updateByCond(array(
		    				'cell' => $from,
		    				'flag' => 2,
		    ), array(
		    				'token' => $msg,
		    ));
		  }
		}
		$this->output();
	}
}