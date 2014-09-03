<?php

/**
 * SignupController
 * 
 * @author
 * @version 
 */

#require_once 'Zend/Controller/Action.php';
require_once 'Better/Mobile/Front.php';

class Mobile_SignupController extends Better_Mobile_Front {
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction()
	{
		
	}
	
	public function termAction(){
		
	}
	
	/**
	 * Perform the actual registration action if nothing is wrong.
	 *
	 * @return null
	 */
	public function doAction()
	{
		$post = $this->getRequest()->getPost();	
		$post['partner'] = 'wap';
		
		$err = $this->_check(false);			
		if (count($err)>0) {
			$this->view->err = $err;
			$this->view->post = $post;			
			echo $this->view->render('signup/index.phtml');
			exit(0);
		} else {
			Better_Log::getInstance()->logInfo(serialize($post),'wapsignup');
			$uid = Better_User_Signup::wapSignup( $post );
						
			if (!$uid) {
				//throw new Better_Exception($this->lang->error->signup->reg_failed);
				$this->view->errresult = $this->lang->error->signup->reg_failed;
				$this->view->post = $post;
				echo $this->view->render('signup/index.phtml');
				exit(0);
			}	else {											
				$this->view->username = $post['email'];
				$this->view->err = array( 'has_err' => 0, 'err'=>'' );
				$this->view->err['has_err'] = 1; 
				$this->view->err['err'] = str_replace("( %s )","(".$post['email'].")",$this->lang->signup->active->tips);
				Better_Registry::get('sess')->set('uid', $uid);
				Better_User_AutoLogin::getInstance($uid)->putCookie();
				Better_Registry::get('sess')->stick();
				$this->_helper->getHelper ( 'Redirector' )->gotoUrl ( '/mobile/signup/step2' );
				//echo $this->view->render('home/index.phtml');
				exit(0);
			}			
		}			
	}
	
	public function step2Action()
	{		
		$post = $this->user->getUserInfo();
		$cityInfo =Better_Functions::getip2city();				
		$data = array(						
			'live_province' =>$cityInfo['live_province'],
			'live_city' =>$cityInfo['live_city']
			);						
		$this->user->updateUser($data);
		$this->view->post = $post;
	}
	
	public function step2doAction()
	{		
		$post = $this->getRequest()->getPost();
		Better_Log::getInstance()->logInfo(serialize($post),'wapsignup');
		if (count($post)==0) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/signup/step3');
			$post = $this->user->getUserInfo();
		} else {
			$btn = $post['btn'];
			$hasErr = false;
			$err = array();
			
			if ($btn!=$this->lang->global->ignore->title) {
				$username = $post['username'];
				$birthday = $post['birthday'];
				$selfIntro = $post['self_intro'];
				$avatar = '';
				
				if (!Better_User_Validator::birthday($birthday)) {
					$err['err_birthday'] = $this->lang->global->invalid_birthday;
					$hasErr = true;
				}

				if (is_array($_FILES) && isset($_FILES['attach'])) {
					$_FILES['myfile'] = &$_FILES['attach'];
					if($_FILES['attach']['size']!=0){
						$status = $this->user->avatar()->upload();						
						if (is_array($status) && isset($status['url'])) {	
							$avatar = $status['id'];		
						} else {
							$err['err_avatar'] = $this->lang->api->error->profile->avatar->invalid_file; 
							$hasErr = true;
						}
					}
				} else {
					$hasErr = true;
					$err['err_avatar'] = $this->lang->api->error->profile->avatar->invalid_file;
				}
				
				if ($hasErr===false) {
					$cityInfo =Better_Functions::getip2city();				
					$data = array(
						'self_intro' => $selfIntro,
						'birthday' => $birthday,
						'live_province' =>$cityInfo['live_province'],
						'live_city' =>$cityInfo['live_city']
						);						
					$this->user->updateUser($data);
					$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/signup/step3');
				} else {
					$this->view->err = $err;
				}
			} else {
				$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/signup/step3');
			}
		}
		
		$this->view->post = $post;
	}
	
	
	public function step3Action()
	{
		$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/home');
	}
	
	public function step4Action()
	{
		
	}
	
	/**
	 * 检查用户提交的注册数据
	 *
	 * @param $output 是否直接输出结果（json）
	 * @return unknown_type
	 */
	private function _check( $post ){
		$post = $this->getRequest()->getPost();	
			
		$err = Better_User_Signup::wapCheck( $post );		
		return $err;
	}
}

