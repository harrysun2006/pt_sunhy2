<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_KaiadminloginController extends Better_Controller_Admin
{
	public function init()
	{
		if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])){ 
			Header("WWW-Authenticate: Basic"); 
			Header("HTTP/1.0 401 Unauthorized"); 
			echo "Enter username and password"; 
			exit; 
		}else{ 
			if (!($_SERVER['PHP_AUTH_USER']=="better_kaikai_admin" && $_SERVER['PHP_AUTH_PW']=="better@kaikai123456") ){ 
	
				Header("WWW-Authenticate: Basic"); 
				Header("HTTP/1.0 401 Unauthorized"); 
				echo "ERROR : username or password is invalid."; 
				exit; 
			} 
		}
		
		parent::init(false);	
	}
	
	public function indexAction()
	{
		$this->view->title="开开管理员登陆";
	}
	
}