<?php

/**
 * 网站验证码
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ScodeController extends Better_Controller_Front 
{
	
	public function init()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();
	}

	public function indexAction()
	{
		$code = Better_Functions::randomNum();
		Better_Registry::get('sess')->set('authCode', $code);

		Better_Image::genSCode($code);

		exit(0);
	}
}

?>