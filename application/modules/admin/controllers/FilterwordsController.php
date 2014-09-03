<?php

/**
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_FilterwordsController extends Better_Controller_Admin
{

	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/filterwords.js');
		$this->view->title="可疑词管理";		
	}	
	
	public function indexAction()
	{
		
	}
	
	public function banAction()
	{
		
	}
	
	
	public function replaceAction()
	{	
		
	}
	
	public function checkAction()
	{
	
	}
	
	public function passAction()
	{
	
	}
	
	public function poiAction(){
		
	}


}