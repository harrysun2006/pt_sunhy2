<?php

/**
 * 私信相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
final class Ajax_MessagesController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}	
	
	/**
	 * 已读私信
	 * 
	 * @return
	 */
	public function readedAction()
	{
		$msg_id = intval($this->getRequest()->getParam('msg_id'));
		$this->output['result'] = 0;
		
		if ($msg_id) {
			$data = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
			if ($data['msg_id'] && $data['uid']==$this->uid) {
				if (Better_User_DirectMessage::getInstance($this->uid)->readed($msg_id)) {
					$this->output['result'] = 1;
				} else {
					$this->output['error'] = $this->lang->error->system;
				}
			} else {
				$this->output['error'] = $this->lang->error->rights;
			}
		} else {
			$this->output['error'] = $this->lang->error->message->wrong_id;
		}		
		
		$this->output();
	}
	
	/**
	 * 已读全部私信
	 */
	public function readallAction(){
		$this->output['result'] = 0;
		
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getReceiveds(array(
			'page' => 1,
			'count' => BETTER_MAX_LIST_ITEMS
		));
		
		if($results['count'] && $results['count']>0){
			foreach($results['rows'] as $row){
				if ($row['msg_id'] && $row['uid']==$this->uid) {
					if($row['readed']==0){
						if (Better_User_DirectMessage::getInstance($this->uid)->readed($row['msg_id'])) {
							
						} else {
							$this->output['error'] = $this->lang->error->system;
						}
					}
					$this->output['result'] = 1;
				} else {
					$this->output['error'] = $this->lang->error->rights;
				}
			}
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_message_received ;
		}
		
		$this->output();
	}
	
	/**
	 * 发新私信
	 * 
	 * @return
	 */
	public function newAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		$content = trim($this->getRequest()->getParam('content', ''));

		$this->output['error'] = '';
		$this->output['result'] = 0;
	
		if ($nickname=='' && !$uid) {
			$this->output['error'] = $this->lang->error->message->empty_receiver;
		} else if ($content=='') {
			$this->output['error'] = $this->lang->error->message->empty_content;
		} else if ($nickname==$this->userInfo['nickname']) {
			$this->output['error'] = $this->lang->error->message->cant_to_self;
		} /*else if ($this->userInfo['karma']<0) {
			$this->output['error'] = $this->lang->error->message->karma_too_low;
		}*/ else {
			$receiverUserInfo = $uid? Better_User::getInstance($uid)->getUser() : Better_User::getInstance()->getUserByNickname($nickname);
			
			if($receiverUserInfo['uid']){
				$receiverUser = Better_User::getInstance($receiverUserInfo['uid']);
				
				if($this->uid==BETTER_SYS_UID || $receiverUserInfo['friend_sent_msg']=='0' || ($receiverUserInfo['friend_sent_msg']=='1' && in_array($this->uid, $receiverUser->friends))){
					if ($receiverUserInfo['uid']==$uid) {
						if (in_array($receiverUserInfo['uid'], $this->user->blockedby)) {
							$this->output['error'] = $this->lang->error->message->blocked;
						} else {
							$result = $this->user->notification()->directMessage()->send(array(
								'content' => $content,
								'receiver' => $uid
								));
							
							foreach ($result as $k=>$v) {
								$this->output[$k] = $v;
							}
							
							if ($result['code']==$result['codes']['SUCCESS']) {
								$this->output['result'] = 1;
							}else if($result['code']==$result['codes']['WORDS_R_BANNED']){
								$this->output['error'] = $this->lang->error->message->ban_words;
							}
						}				
		
					} else {
						$this->output['error'] = $this->lang->error->message->wrong_receiver;
					}
				}else{
					$this->output['error'] = '对方只接收好友的私信';
				}
			}else{
				$this->output['error'] = $this->lang->error->message->wrong_receiver;
			}
		}

		$this->processRightbar();
		
		$this->output();
		
	}
	
	/**
	 * 所有好友请求
	 */
	public function friendsrequestsAction()
	{
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->friendRequest()->getReceiveds(array(
			'type' => 'friend_request',
			'page' => $this->page,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$this->output[$k] = $v;
		}
		
		$this->output['rows'] = Better_Output::filterMessages($this->output['rows']);
		$this->output['pages'] = Better_Functions::calPages($rows['count'], $count);
		$this->output['page'] = $this->page;
		unset($rows);
		
		$this->output();			
	}
	
	/**
	 * 所有关注请求
	 */
	public function followrequestsAction()
	{
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'follow_request',
			'page' => $this->page,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$this->output[$k] = $v;
		}
		unset($rows);
		
		$this->output['rows'] = Better_Output::filterMessages($this->output['rows']);
		$this->output['pages'] = Better_Functions::calPages($this->output['count'], $count);
		
		$this->output();			
	}	
	
	/**
	 * 已接收的私信
	 * 
	 * @return
	 */
	public function receivedAction()
	{
		$directMessages = $this->user->notification()->directMessage()->getReceiveds(array(
			'page' => $this->page,
			'count' => BETTER_PAGE_SIZE
			));
//		$invitationMessages = $this->user->notification()->invitationTodo()->getReceiveds(array(
//			'page' => $this->page,
//			'count' => BETTER_PAGE_SIZE
//			));
		$_output['rows'] = array();		
		$rds = array();
		foreach($directMessages['rows'] as $row){
			if($row['type'] == 'invitation_todo'){
				$row['isinvited']=true;
				if($row['invitedpoi']['reply_msg_id']>0){//邀请的反馈
					Better_User_DirectMessage::getInstance($this->uid)->readed($row['invitedpoi']['msg_id']);
					$row['isreply']=true;
				}else{
					$row['isreply']=false;
				}
				if ($row['msg_id'] && $row['uid']==$this->uid) {
					$_output['rows'][$row['dateline']."_".$row['msg_id']] = array_merge((array)$row['userInfo'], $row);
				}else{
					$_output['error'] = $this->lang->error->rights;
				}
			}else{
				if ($row['msg_id'] && $row['uid']==$this->uid) {
					if($row['readed']==0){
						if (Better_User_DirectMessage::getInstance($this->uid)->readed($row['msg_id'])) {
							$row['readed'] = 1;
							$rds[] = $row['msg_id'];
						} else {
							$_output['error'] = $this->lang->error->system;
						}
					}
					$_output['rows'][$row['dateline']."_".$row['msg_id']] = array_merge((array)$row['userInfo'], $row);//按照时间排序
				}else{
					$_output['error'] = $this->lang->error->rights;
				}
			}
		}
//		foreach($invitationMessages['rows'] as $row){
//			$row['isinvited']=true;
//			if($row['invitedpoi']['reply_msg_id']>0){//邀请的反馈
//				Better_User_DirectMessage::getInstance($this->uid)->readed($row['invitedpoi']['msg_id']);
//				$row['isreply']=true;
//			}else{
//				$row['isreply']=false;
//			}
//			if ($row['msg_id'] && $row['uid']==$this->uid) {
//				$_output['rows'][$row['dateline']."_".$row['msg_id']] = array_merge((array)$row['userInfo'], $row);
//			}else{
//				$_output['error'] = $this->lang->error->rights;
//			}
//		}	
		krsort($_output['rows']);//按照时间排序
		
		if (count($rds)>0 && $this->config->dm_ppns && BETTER_PPNS_ENABLED) {
			//	已读私信推送给客户端
			$this->user->notification()->all()->pushReadStateToPpns($rds);
		}
		
		$this->user->cache()->set('direct_message_count', 0);
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['count'] = $directMessages['count'];
		$_output['page'] = 1;
		$_output['pages'] = Better_Functions::calPages($directMessages['count']);
		$this->output = $_output;

		$this->output();
	}
	
	/**
	 * 已发送的私信
	 * 
	 * @return
	 */
	public function sentAction()
	{
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getSents($this->page, BETTER_PAGE_SIZE);

		$this->output['rows'] = array();
		foreach ($results['msgs'] as $row) {
			$this->output['rows'][] = array_merge((array)$results['users'][$row['to_uid']], $row);
		}
		
		$this->output['count'] = $results['count'];
		$this->output['page'] = $this->page;
		$this->output['pages'] = Better_Functions::calPages($this->userInfo['sent_msgs']);	

		$this->output();
	}
	
	/**
	 * 删除已收到的私信
	 * 
	 * @return
	 */
	public function deletereceivedAction()
	{
		$msg_id = intval($this->getRequest()->getParam('msg_id',0));
		$this->output['error'] = '';
		$this->output['result'] = 0;
		
		if ($msg_id<=0) {
			$this->output['error'] = 'Invalid msg_id';
		}
		
		$data = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
		if (isset($data['msg_id']) && $data['uid']==$this->uid) {
			Better_User_DirectMessage::getInstance($this->uid)->delReceived($msg_id);
			$this->output['result'] = 1;
		} else {
			$this->output['error'] = 'not permitted';
		}		
		
		$this->processRightbar();
		
		$this->output();
	}
	
	
	/**
	 * 删除所有私信
	 */
	public function deleteallreceivedAction(){
		$this->output['result'] = 0;
		$this->output['error'] = '';
		
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getReceiveds(array(
			'page' => 1,
			'count' => BETTER_MAX_LIST_ITEMS
		));
		
		if($results['count'] && $results['count']>0){
			foreach($results['rows'] as $row){
				if (isset($row['msg_id']) && $row['uid']==$this->uid) {
					Better_User_DirectMessage::getInstance($this->uid)->delReceived($row['msg_id']);
					$this->output['result'] = 1;
				} else {
					$this->output['error'] = 'not permitted';
				}	
			}
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_message_received ;
		}
		
		$this->output();
	}
	
	/**
	 * 删除已发送的私信
	 * 
	 * @return
	 */
	public function deletesentAction()
	{
		$msg_id = intval($this->getRequest()->getParam('msg_id',0));
		$this->output['error'] = '';
		$this->output['result'] = 0;
		
		if ($msg_id<=0) {
			$this->output['error'] = 'Invalid msg_id';
		}
		
		$data = Better_User_DirectMessage::getInstance($this->uid)->getSent($msg_id);
		if (isset($data['msg_id']) && $data['uid']==$this->uid) {
			Better_User_DirectMessage::getInstance($this->uid)->delSent($msg_id);
			$this->output['result'] = 1;
		} else {
			$this->output['error'] = 'not permitted';
		}		
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 *  群发地点邀请，邀请的形式是以发私信的方式发送
	 *  @param fuids the uid to send
	 */
	public function sendgroupAction()
	{
		$fuids = trim($this->getRequest()->getParam('fuids', '')); //175623,175625,175624,175629
		$content = trim($this->getRequest()->getParam('content', ''));
		$fuids = explode(',',$fuids);
		$poiid= $this->getRequest()->getParam('poiid', 0);
		$poiname = $this->getRequest()->getParam('poiname', '');
		$mailadds = $this->getRequest()->getParam('mailadds', '');//"email1,email2,email3"		
		$this->output['error'] = '';
		$mailcounts=0;
		if($mailadds!=""){
			$mailadds =  explode(',',$mailadds);
			$lan = 'zh-cn';
			$title="{$this->userInfo['nickname']} 邀请您使用开开 ";
			$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/invite_friends_tpl.html';			
			foreach($mailadds as $email){
				if($email!=""){				
					$mailer = new Better_Email($this->uid);		
					$mailer->setSubject($title);
					$mailer->setTemplate($template);		
					$mailer->addReceiver($email, "");	
					$mailer->set(array(
										'POINAME' => $poiname,
										'USERNAME'=>$this->userInfo['nickname'],
										'POIID'=> $poiid
										));					
					 $mailer->send2();
					 $mailcounts++;	
					 unset($mailer);
				}
			}			
		}
		$this->output['mailcounts'] =  $mailcounts;
		if (empty($fuids)) {
			$this->output['error'] = $this->lang->error->message->empty_receiver;
		} else if ($content=='') {
			$this->output['error'] = $this->lang->error->message->empty_content;
		} else if (in_array($this->userInfo['uid'],$fuids)) {
			$this->output['error'] = $this->lang->error->message->cant_to_self;
		} /*else if ($this->userInfo['karma']<0) {
			$this->output['error'] = $this->lang->error->message->karma_too_low;
		}*/ else {
			$i=0;
			$content = "邀请你一起去 $poiname : ".$content;
		    foreach($fuids as $uid){
		    	if($uid!=""){
			    	$i++;
			    	$receiverUserInfo = Better_User::getInstance($uid)->getUser();	
			    	$this->output['item'][$i]['uid'] = $uid;
					if($receiverUserInfo['uid']){
						$receiverUser = Better_User::getInstance($receiverUserInfo['uid']);					
						if($this->uid==BETTER_SYS_UID || $receiverUserInfo['friend_sent_msg']=='0' || ($receiverUserInfo['friend_sent_msg']=='1' && in_array($this->uid, $receiverUser->friends))){
							if ($receiverUserInfo['uid']==$uid) {
								if (in_array($receiverUserInfo['uid'], $this->user->blockedby)) {
									$this->output['item'][$i]['error'] = $this->lang->error->message->blocked;
								} else {
									$result = $this->user->notification()->invitationTodo()->send(array(
										'content' => $content,
										'receiver' => $uid
										));
									foreach ($result as $k=>$v) {
										$this->output['item'][$i][$k] = $v;
									}
									
									if ($result['code']==$result['codes']['SUCCESS']) {
										//发送私信成功，需要记录POI信息
										$msg_id = $result['id'];
										$data = array(
											'msg_id'=>$msg_id,
											'poi_id'=>$poiid,
											'poi_name'=>$poiname,		
											'dateline'=>time()
											);
										Better_DAO_Todopoi::getInstance($uid)->insert($data);
										$this->output['item'][$i]['result'] = 1;
									}else if($result['code']==$result['codes']['WORDS_R_BANNED']){
										$this->output['item'][$i]['error'] = $this->lang->error->message->ban_words;
									}
								}				
				
							} else {
								$this->output['item'][$i]['error'] = $this->lang->error->message->wrong_receiver;
							}
						}else{
							$this->output['item'][$i]['error'] = '对方只接收好友的私信';
						}
					}else{
						$this->output['item'][$i]['error'] = $this->lang->error->message->wrong_receiver;
					}
		 		}
		     }
		}
		$this->output();		
	}
	
//	/**
//	 * 发送邮件
//	 */
//	public function sendmailAction()
//	{
//		$params = $this->getRequest()->getParams();
//		$email="fengxianmeng0512@163.com";
//		$mailadds = $params['mailadds'];//"email1,email2,email3"		
//		$lan = 'zh-cn';
//		$uid=$this->uid;
//		$this->output['uid']=$uid;
//		$title="yanglei1 邀请您使用开开 ";
//		$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/invite_friends_tpl.html';
//		$mailer = new Better_Email($uid);		
//		$mailer->setSubject($title);
//		$mailer->setTemplate($template);		
//		$mailer->addReceiver($email, "");		
//		$poiname = '苏州动物园';
//		$username = 'yanglei1'; 
//		$mailer->set(array(
//							'POINAME' => $poiname,
//							'USERNAME'=>$username
//							));					
//		$yrn = $mailer->send2();	
//		if($yrn){		
//			$this->output['code'] = 'sucess';
//		}else{
//			$this->output['code'] = 'failure';
//		}
//		$this->output['params'] = $params;
//		$this->output();
//				
//	}
	/**
	 * 或略这一次邀请
	 * 
	 * @return
	 */
	public function refuseinvitationAction()
	{
		$msg_id = intval($this->getRequest()->getParam('msg_id',0));
		$this->output['error'] = '';
		$this->output['result'] = 0;
		
		if ($msg_id<=0) {
			$this->output['error'] = 'Invalid msg_id';
		}
		
		$data = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
		if (isset($data['msg_id']) && $data['uid']==$this->uid && $data['type']=="invitation_todo") {
			$r = Better_User_DirectMessage::getInstance($this->uid)->delReceived($msg_id);			
			Better_DAO_Todopoi::getInstance($this->uid)->delete($msg_id,'msg_id');						
			$this->output['result'] = 1;
		} else {
			$this->output['error'] = 'not permitted';
		}				
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 同意这一次邀请,发表一条我想去，内容是【被邀请人】：想去【邀请POI】：接受【邀请人】的邀请，打算和他一起去【POINAME】
	 * 
	 * @return
	 */
	public function agreeinvitationAction()
	{
		$msg_id = intval($this->getRequest()->getParam('msg_id',0));//43428		
		$this->output['error'] = '';
		$this->output['result'] = 0;		
		if ($msg_id<=0) {
			$this->output['error'] = 'Invalid msg_id';
		}		
		
		$invitationInfo = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
		$uid = $invitationInfo['from_uid'];//邀请人的id
		$poiInfo =Better_DAO_Todopoi::getInstance($this->uid)->getByMsgId($msg_id);	
		$receiverUserInfo = Better_User::getInstance($uid)->getUser();
		//更新改邀请的状态为已读
		//发送通知给邀请人，通知的类型为邀请类型，置状态为回复类型
		//需要告诉好友这条邀请的相关poi信息
		$message = '接受 {NICKNAME} 的邀请，打算和TA一起去{POINAME}。';
		$message = str_replace('{NICKNAME}',"@".$receiverUserInfo['nickname'],$message);
		$message = str_replace('{POINAME}',$poiInfo['poi_name'],$message);
		//判断是否在该POI发表过我想去，若发表过，则直接发送私信到邀请者，否则先发表我想去，然后发送私信
	//	$bid =  Better_User_Blog::getInstance($this->uid)->getBidByCond($this->uid,$poiInfo['poi_id'],'todo');
	//	if($bid==null || $bid==0){
			$post = array('message'=>$message,
								'upbid'=>0,
								'priv'=>'public',
								'poi_id'=>$poiInfo['poi_id'],
								'type'=>'todo',
								'passby_spam'=>1,
								'need_sync'=>0);		
			$bid = Better_User_Blog::getInstance($this->uid)->add($post);
	//	}
		if((float)$bid > 0){			
			$content = "{NICKNAME}已经同意和你一起去{POINAME}，约个时间一起去吧。 ";
			$content = str_replace('{NICKNAME}',' @'.$this->userInfo['nickname'].' ',$content);
			$content = str_replace('{POINAME}',	' '.$poiInfo['poi_name'].' ',$content);
			
			$this->output['error'] = '';
			$this->output['result'] = 0;		
			if($receiverUserInfo['uid']){
				//发送私信
					$receiverUser = Better_User::getInstance($receiverUserInfo['uid']);			
					if ($receiverUserInfo['uid']==$uid) {						
						$result = Better_User::getInstance(BETTER_SYS_UID)->notification()->invitationTodo()->send(array(
							'content' => $content,
							'receiver' => $uid
							));
					foreach ($result as $k=>$v) {
							$this->output[$k] = $v;
					}	
					if ($result['code']==$result['codes']['SUCCESS']) {
						$msg_id = $result['id'];
						$data = array(
							'msg_id'=>$msg_id,
							'poi_id'=>$poiInfo['poi_id'],
							'poi_name'=>$poiInfo['poi_name'],
							'reply_msg_id'=>	$poiInfo['msg_id'],
							'dateline'=>time()
							);
					     Better_DAO_Todopoi::getInstance($uid)->insert($data);//新插入一条记录邀请POI id的记录
					     Better_User_DirectMessage::getInstance($this->uid)->readed(	$poiInfo['msg_id']);//标记为已读
						$this->output['result'] = 1;
					}else if($result['code']==$result['codes']['WORDS_R_BANNED']){
						$this->output['error'] = $this->lang->error->message->ban_words;
					}
				}		
			}else{
				$this->output['error'] = $this->lang->error->message->wrong_receiver;
			}
		}else{
			$this->output['error'] = "发表想去失败！";
		}		
		$this->output();
	}
	
}
