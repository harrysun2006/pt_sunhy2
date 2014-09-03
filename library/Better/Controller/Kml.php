<?php

/**
 * KML控制器
 * 
 * @package Better.Controller
 * @author leip <leip@peptalk.cn>
 *
 */

abstract class Better_Controller_Kml extends Better_Controller_Front
{
	protected $lang = null;
	protected $uid = 0;
	protected $user = null;
	protected $dispUser = null;
	protected $userInfo = array();
	protected $dispUserInfo = array();
	protected $poiId = 0;
	protected $cbFiles = array();
	
	protected $page = 1;
	
	/**
	 * 前台控制器初始化
	 *
	 */
	public function init()
	{
		parent::init();
		
		$this->page = (int)$this->getRequest()->getParam('page', 1);
	}
	
}