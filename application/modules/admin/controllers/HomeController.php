<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_HomeController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$this->view->title="开开后台管理";
	}
	

}