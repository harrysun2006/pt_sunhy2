<?php

/**
 * 兼容mobile的wap控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class WapController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$this->_helper->getHelper('Redirector')->gotoUrl('/mobile');
		exit(0);
	}
}
