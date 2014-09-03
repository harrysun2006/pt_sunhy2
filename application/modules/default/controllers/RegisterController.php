<?php

/**
 * 
 */

class RegisterController extends Better_Controller_Front 
{
	
	public function init()
	{
		if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])){ 
			Header("WWW-Authenticate: Basic"); 
			Header("HTTP/1.0 401 Unauthorized"); 
			echo "Enter username and password"; 
			exit; 
		}else{ 
			if (!($_SERVER['PHP_AUTH_USER']=="better_kaikai" && $_SERVER['PHP_AUTH_PW']=="better@kaikai123") ){ 
	
				Header("WWW-Authenticate: Basic"); 
				Header("HTTP/1.0 401 Unauthorized"); 
				echo "ERROR : username or password is invalid."; 
				exit; 
			} 
		}
		
		
		parent::init();
		$this->commonMeta();
		
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/register.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	$this->view->css = 'index';
    	$this->view->post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
	}
	
	/**
	 * 注册表单页面显示
	 *
	 * @return null
	 */
	public function indexAction()
	{
		
		$ref = $this->getRequest()->getParam('ref');
		if ($ref) {
			Better_Registry::get('sess')->set('ref_uid', $ref);
		}
		
	}

	
	
	/**
	 * 执行注册操作
	 *
	 * @return null
	 */
	public function doAction()
	{
		$err = $this->_check(false);
		$post = &$this->view->post;
		$this->view->post = $post;

		$invitecode = $post['invitecode'];
		
		if (count($err)>0) {
			$this->view->err = $err;
			echo $this->view->render('register/index.phtml');
			exit(0);
		} else {
			$uid = Better_User_Signup::registe($post);

			if (!$uid) {
				throw new Better_Exception($this->lang->error->signup->reg_failed);
			}else{
				if($invitecode){
					$result=Better_Invitecode::getInstance()->deleteInvitecode($invitecode);
					if(!$result){
						throw new Better_Exception('');
					}
				}
			}
		}

	}
	
	/**
	 * 检查用户注册时提交的数据
	 *
	 * @return null
	 */
	public function checkAction()
	{
		$this->_check();
	}
	
	/**
	 * 检查用户提交的注册数据
	 *
	 * @param $output 是否直接输出结果（json）
	 * @return unknown_type
	 */
	private function _check($output=true)
	{
		$post = $this->getRequest()->getPost();
		$err = Better_User_Signup::checkregiste($post);
		
		if ($output==true) {
			echo json_encode(array(
					'has_error' => count($err),
					'err' => $err,
					));
			exit(0);
		}
		
		return $err;
	}
	
}

?>