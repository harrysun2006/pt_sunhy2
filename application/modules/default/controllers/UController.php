<?php

/**
 * 短地址首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class UController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
    	$this->commonMeta();
	}
	
	public function __call($m, $p)
	{
		$params = $this->getRequest()->getParams();
		$u = trim(urldecode($params['action']));
		
		if (strlen($u)) {
			$url = Better_Url::parse($u);
			
			if ($url) {
				header('Location: '.$url);
			} else {
				header('Location: '.BETTER_BASE_URL);
			}
		} else {
			header('Location: '.BETTER_BASE_URL);
		}
		exit(0);
	}
}