<?php

/**
 * Ajax校验器
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */

class Ajax_ValidatorController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init(false);	
	}		
	
	public function codeAction()
	{
		$code = $this->getRequest()->getParam('code', '');
		
		$this->output['result'] = Better_Registry::get('sess')->get('authCode')==$code ? 1 : 0;
		
		$this->output();
	}

	/**
	 * 校验Email
	 * 
	 */
	public function emailAction()
	{
		$email = $this->getRequest()->getParam('email', '');
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		
		$result = Better_User_Validator::email($email, $uid);
		
		$this->output = array_merge($result, $this->output);
		
		$this->output();
	}
	
	/**
	 * 校验姓名
	 * 
	 */
	public function nicknameAction()
	{
		$email = $this->getRequest()->getParam('nickname', '');
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		
		$result = Better_User_Validator::nickname($email, $uid);
		
		$this->output = array_merge($result, $this->output);
		
		$this->output();		
	}
	
	/**
	 * 校验用户名
	 * 
	 */
	public function usernameAction()
	{
		$email = $this->getRequest()->getParam('username', '');
		
		$result = Better_User_Validator::username($email, $this->uid);
		
		$this->output = array_merge($result, $this->output);
		
		$this->output();			
	}
}