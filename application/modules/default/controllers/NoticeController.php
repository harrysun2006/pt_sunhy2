<?php

/**
 * 通知
 * @package Controllers
 */

class NoticeController extends Better_Controller_Front 
{
	
	protected $dispUser = null;
	protected $dispUserInfo = array();
	protected $params = array();

	public function init()
	{
		parent::init();
		
		$this->needLogin();
		$this->commonMeta();

    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/notice.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	
    	$userInfo = $this->user->getUserInfo();
    	$this->dispUserInfo = $userInfo;
		$this->dispUser = $this->user;
    	
    	$this->view->userInfo = $this->dispUserInfo;

    	if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		');
    	} 
    	
	}
	

	
	public function indexAction()
	{
		if ($this->uid && $this->dispUserInfo['uid']==$this->uid) {
			
		$this->view->friq_count = $this->user->notification()->friendRequest()->count(array(
			'type' => 'friend_request',
			'act_result' => 0
			));
			
		$this->view->floq_count = $this->user->notification()->followRequest()->count(array(
			'type' => 'follow_request',
			'act_result' => 0
			));
				
	    //page one
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;
		
//		$msg = $this->user->notification()->invitationTodo();
//		$msg = $this->user->notification()->directMessage();
		$directMessages = $this->user->notification()->directMessage()->getReceiveds(array(
			'page' => 1,
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
//		echo 'test';die;
		krsort($_output['rows']);//按照时间排序
		
		if (count($rds)>0 && $this->config->dm_ppns && BETTER_PPNS_ENABLED) {
			//	已读私信推送给客户端
			$this->user->notification()->all()->pushReadStateToPpns($rds);
		}
		
		$this->user->cache()->set('direct_message_count', 0);
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['count'] = $directMessages['count']+$invitationMessages['count'];
		$_output['page'] = 1;
		$_output['pages'] = Better_Functions::calPages( $directMessages['count']+$invitationMessages['count']);
	
		$msg_jsonPage1 = json_encode($_output);   
	
		
		$sJs = " var _msg_page1 = $msg_jsonPage1;";
		
		
		//friend_request 
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;	

		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->friendRequest()->getReceiveds(array(
			'type' => 'friend_request',
			'page' => 1,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$_output[$k] = $v;
		}
		
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['pages'] = Better_Functions::calPages($rows['count'], $count);
		$_output['page'] = 1;
		unset($rows);
		
		$friend_request_jsonPage1 = json_encode($_output); 	
		$sJs .= "var _friend_request_page1 = $friend_request_jsonPage1;";
		
		//follow_request 
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;	

		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'follow_request',
			'page' => 1,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$_output[$k] = $v;
		}
		
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['pages'] = Better_Functions::calPages($rows['count'], $count);
		$_output['page'] = 1;
		unset($rows);

		$follow_request_jsonPage1 = json_encode($_output); 	
		$sJs .= "var _follow_request_page1 = $follow_request_jsonPage1;";		
		
	    //end				
				
		} else {
			$sJs = '';
			$this->view->friq_count = 0;
			$this->view->floq_count = 0;
		}

		$this->userRightBar();
		
		$spec = 0;
		if ($this->config->sys_spec && $this->dispUserInfo['uid']==BETTER_SYS_UID && $this->uid!=BETTER_SYS_UID) {
			$spec = 1;
		}
		
    	$this->view->headScript()->prependScript('
    		var Better_Kai_Spec = ' . $spec . ';' .
    		$sJs . '
    		var needRef_msg = false;
    		var needRef_friend_request = false;
    		var needRef_follow_request = false;
    		'
    		);		
    	$this->view->kai_spec = $spec;
		$this->view->needCheckinJs = false;
	}
	
}
