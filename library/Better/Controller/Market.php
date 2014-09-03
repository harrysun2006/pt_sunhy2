<?php

/**
 * 市场部活动控制器
 * 
 * @package Better.Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Controller_Market extends Better_Controller_Front
{
	public function init()
	{
		parent::init();
		
		$this->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');
		$this->getResponse()->sendHeaders();

	}
}