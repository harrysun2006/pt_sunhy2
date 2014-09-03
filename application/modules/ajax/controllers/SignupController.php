<?php

/**
 * ajax注册
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_SignupController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
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