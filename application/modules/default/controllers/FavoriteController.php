<?php

/**
 * 收藏
 *
 * @package Controllers
 */

class FavoriteController extends Better_Controller_Front 
{
	
	protected $dispUser = null;
	protected $dispUserInfo = array();
	protected $params = array();

	public function init()
	{
		parent::init();
		
		$this->needLogin();
		$this->commonMeta();

    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/favorite.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	
    	$userInfo = $this->user->getUserInfo();
    	$this->dispUserInfo = $userInfo;
		$this->dispUser = $this->user;
    	
    	$this->view->userInfo = $this->dispUserInfo;

    	if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		');
    	} 
    	
		$this->userRightBar();
		
		$spec = 0;
		if ($this->config->sys_spec && $this->dispUserInfo['uid']==BETTER_SYS_UID && $this->uid!=BETTER_SYS_UID) {
			$spec = 1;
		}
		
    	$this->view->headScript()->prependScript('
    		var Better_Kai_Spec = ' . $spec . ';'
    	);	
		
    	$this->view->kai_spec = $spec;
		$this->view->needCheckinJs = false;
	}
	
	public function __call($method, $params)
	{
		$this->indexAction();
		$this->render('index');
	}		
	

	public function indexAction()	{		$this->view->headScript()->prependScript('			$("#relation_favorite").addClass("on");    	');
	}
	
}
