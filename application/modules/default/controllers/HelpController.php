<?php

/**
 * 帮助页面
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class HelpController extends Better_Controller_Front
{

	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		
	}
	
	public function msnAction()
	{
		
	}
	
	public function syncAction()
	{
		
	}
	
	public function cellAction()
	{
		
	}
	
	public function learnmoreAction(){
		 $this->view->headScript()->appendFile($this->jsUrl.'/controllers/help.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	//用户被封号页面
	public function accountbannedAction(){
		
	}
}