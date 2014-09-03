<?php

/**
 * 重设密码
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ResetpwdController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		$this->commonMeta();

		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/resetpwd.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function indexAction()
	{
		$this->_helper->getHelper('Redirector')->gotoUrl('/resetpwd/form');
	}

	public function doAction()
	{

		$post = $this->getRequest()->getPost();

		echo json_encode(Better_User_Resetpwd::request(trim($post['email'])));
		exit(0);
		
	}

	public function formAction()
	{
		$h = $this->getRequest()->getParam('h');
		
		if ($h) {
			list($uid, $hash) = explode('_', $h);
			$ruid = Better_User_Resetpwd::hasRequest($uid, $hash);
			if ($ruid>0) {
				Better_Registry::get('sess')->set('uid', $ruid);
				
				$this->_helper->getHelper('Redirector')->gotoUrl('/setting/password?h='.$hash);
				exit(0);
			}
		}

		//	抛出异常
	}
	
}

?>