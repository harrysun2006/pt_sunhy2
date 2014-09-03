<?php

/**
 * 互联网大会
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_MeetingController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
		define('IN_MEETING', true);
	}	
	
	public function checkAction()
	{
		$nickname = trim(urldecode($this->getRequest()->getParam('nickname', '')));
		$email = trim(urldecode($this->getRequest()->getParam('email', '')));
		
		$exists = Better_User_Exists::getInstance()->email($email);
		$this->output['exists'] = $exists ? 1 : 0;
		
		$this->output();
	}
	
	/**
	 * 互联网大会注册入口
	 * 
	 * @return
	 */
	public function meetingAction()
	{
		$post = $this->getRequest()->getPost();
		
		if (is_array($post) && count($post)>0) {
			
		} else {
			$this->output['exception'] = $this->lang->global->invalid_usage;
		}
		
		$this->output();
	}
}