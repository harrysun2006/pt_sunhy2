<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_FeedbackController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/feedback.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户反馈管理";	
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		$data = Better_Admin_Feedback::getFeedbacks($params);
		$rows=array();
		foreach($data['rows'] as $row){			
			if($row['uid']){
				$user = Better_DAO_User::getInstance()->getByUid($row['uid']);
				$row['username']=$user['username'];
				$row['nickname']=$user['nickname'];
			}
			$rows[]=$row;
		}
		
		$this->view->params = $params;
		$this->view->rows = $rows;
		$this->view->count = $data['count'];
		
	}

	
	public function delAction(){
		
		$result = 0;
		$post = $this->getRequest()->getPost();
		$ids = &$post['ids'];
		
		if (is_array($ids) && count($ids)>0) {
			Better_Admin_Feedback::delFeedback($ids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	public function replyAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		Better_DAO_Admin_Feedback::getInstance()->replyFeedback($params) && $result = 1;
		$this->sendAjaxResult($result);
	}
	
	public function sendAction()
	{
		
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uid = $params['uid'];
		$content = trim($params['content']);
		
		if (empty($uid)) {
			$result = '收信人不能为空';
		} else if (empty($content)) {
			$result = '私信内容不能为空';
		} else {
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $uid);
			$result = 1;	
		}
		$this->sendAjaxResult($result);
	}
	
	/**
	 * 查看管理员和某一个用户的私信的记录
	 */
	public function viewhistoryAction()
	{
		$params = $this->getRequest()->getParams();	
		$uid = $params['uid'];
		if($uid){
			$rows = Better_DAO_DmessageSend::getInstance($uid)->getAllHistory($uid);
			$this->view->params = $params;
			$this->view->rows = $rows;
			$this->view->counts = count($rows);
		}
	}
}