<?php

/**
 * 分享到Better控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ShareController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		
		$this->needLogin();
    	$this->commonMeta();

		$this->view->myfollowing = $this->user->followings;
		$this->view->myblocking = $this->user->blocks;

		$lon = floatval($this->getRequest()->getParam('lon', ''));
		$lat = floatval($this->getRequest()->getParam('lat', ''));

    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/share.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		
		$this->view->status_text = $this->getRequest()->getParam('status_text');
		
		$this->view->needCheckinJs = true;
	}

	public function indexAction()
	{
		
	}
}