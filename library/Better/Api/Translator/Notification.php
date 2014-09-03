<?php

/**
 * 
 * 通知数据转换
 * 
 * @package Better.Api.Translate
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Notification extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{		
		$result = array();
		$data = &$params['data'];
		$invitedpoi = isset($data['invitedpoi']) ? $data['invitedpoi'] : array();
		$reply_msg_id = isset($invitedpoi['reply_msg_id']) ? $invitedpoi['reply_msg_id'] : 0;

		if (isset($data['game_poi'])) {
			$gamePoi = &$data['game_poi'];
		} else {
			$gamePoi = $data['poi_id'] ? Better_Poi_Info::getInstance((int)$data['poi_id'])->getBasic() : array();
		}

		if (isset($data['msg_id'])) {
			$userInfo = &$params['userInfo'];
			$receiverUserInfo = Better_User::getInstance($data['uid'])->getUserInfo();
			$senderUserInfo = (is_array($data['userInfo']) && count($data['userInfo'])>0) ? $data['userInfo'] : Better_User::getInstance($data['from_uid'])->getUserInfo();

			$result['id'] = $data['msg_id'];
			$step = $data['step'] ? $data['step'] : '';
			
			switch ($data['type']) {
				case 'game_invite':
				case 'game_accept':
				case 'game_reject':
				case 'game_interactive':
				case 'game_over':
				case 'game_sync':
				case 'game_result':
					$category = 'gm';
					list($c, $sc) = explode('_', $data['type']);
					$subcategory = $sc;
					break;
				case 'game_invite_wait_response':
					$category = 'gm';
					$subcategory = 'invite_wait_response';
					break;
				case 'follow_request':
					$category = 'rm';
					$subcategory = 'following';
					break;
				case 'friend_request':
					$category = 'rm';
					$subcategory = 'friend';
					break;
				case 'notification_readed':
					$category = 'nr';
					$subcategory = '';
					break;
				case 'invitation_todo':
					$category = 'it';
					$subcategory = $reply_msg_id > 0 ? 'todo-reply' : 'todo';
					break;
				case 'direct_message':
				default:
					/*if ($senderUserInfo['uid']==BETTER_SYS_UID) {
						$category = 'tm';	
					} else {
						$category = 'dm';
					}*/
					$category = 'dm';
					$subcategory = '';
					break;
			}
			$result['category'] = $category;
			
			$result['subcategory'] = $subcategory;
			$result['text'] = $data['msg'] ? $data['msg'] : ($data['text'] ? $data['text'] : $data['content']);
			$result['create_at'] = parent::time($data['dateline']);
			
			$result['text'] = str_replace('<br />', '', $result['text']);
			$result['text_at'] = array();
			$ats = Better_Blog::apiParseBlogAt($result['text']);
			if (count($ats)>0) {
				foreach ($ats as $atNickname=>$atUid) {
					$result['text_at'][] = array(
						'at' => Better_Api_Translator::getInstance('status_at')->translate(array(
							'data' => array(
								'uid' => $atUid,
								'nickname' => $atNickname
								)
							))
						);
				}
			}
			// 2011-9-2: sunhy, 增加我想去邀请和回复节点的地点信息, at_ext
			if (count($invitedpoi) > 0 && $invitedpoi['poi_id'] > 0 && isset($invitedpoi['poi_name'])) {
				$result['text_at'][] = array(
					'at_ext' => array(
						'category' => 'poi',
						'id' => $invitedpoi['poi_id'],
						'text' => $invitedpoi['poi_name'],
					)
				);
			}

			$result['sender'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$senderUserInfo,
				'userInfo' => &$userInfo,
				));
			$result['recipient'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$receiverUserInfo,
				'userInfo' => &$userInfo,
				));
								
			if ($category=='gm') {
				$sessId = $data['sid'];
				
				$sessInfo = Better_DAO_Game_Session::getInstance()->get($sessId);
				$timeTotal = $timeElapsed = 0;
				if ($sessInfo['ended'] && $sessInfo['expired']=='0') {
					$timeTotal = (int)Better_Config::getAppConfig()->game->pickup_timeout;
					$spend = time()-$sessInfo['end_time'];
					$timeElapsed = $spend>$timeTotal ? $timeTotal : $spend;
				} else if ($sessInfo['expired']=='0' && $sessInfo['start_time']!='0' && $sessInfo['ended']=='0') {
					$timeTotal = $sessInfo['end_time'] - $sessInfo['start_time'];
					$spend = time() - $sessInfo['start_time'];
					$timeElapsed = $spend>$timeTotal ? $timeTotal : $spend;
				} else {
					$timeTotal = (int)Better_Config::getAppConfig()->game->invite_timeout;
					$spend = time()-$sessInfo['create_time'];
					$timeElapsed = $spend>$timeTotal ? $timeTotal : $spend;
				}

				$gameResult = array();
				if ($sessInfo['ended']) {
					
					$tid = $data['uid']==$sessInfo['starter_uid'] ? $sessInfo['starter_treasure'] : $sessInfo['coplayer_treasure'];
					$treasure = Better_Treasure::getInstance($tid);
					$treasureInfo = $treasure->getInfo();
					
					$tmp = array();
					$tmp[] = array(
						'result' => array(
							'treasure' => Better_Api_Translator::getInstance('treasure')->translate(array(
								'data' => &$treasureInfo,
								'userInfo' => &$userInfo,
								)),
							'mine' => 'true',
							),
						);
						
					$hisUid = $sessInfo['starter_uid']==$data['uid'] ? $sessInfo['coplayer_uid'] : $sessInfo['starter_uid'];
					$hisTid = $data['uid']==$sessInfo['starter_uid'] ? $sessInfo['coplayer_treasure'] : $sessInfo['starter_treasure'];
					$hisTreasure = Better_Treasure::getInstance($hisTid);
					$hisTreasureInfo = $hisTreasure->getInfo();
					
					$tmp[] = array(
						'result' => array(
							'treasure' => Better_Api_Translator::getInstance('treasure')->translate(array(
								'data' => &$hisTreasureInfo,
								'userInfo' => &$userInfo,
								)),
							'mine' => 'false',
							),
						);

					$gameResult = &$tmp;
				}
				
				$result['game'] = array(
					'session_id' => $sessId,
					'progress' => array(
						'time_total' => $timeTotal,
						'time_elapsed' => $timeElapsed,
				//		'step' => $step,
						),
					'results' => $gameResult,
					'poi' => Better_Api_Translator::getInstance('poi_simple')->translate(array(
						'data' => &$gamePoi,
						'userInfo' => &$userInfo
						)),
					);
			}
		}
		
		return $result;
	}
}