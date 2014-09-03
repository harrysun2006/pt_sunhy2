<?php

/**
 * LoginController
 * 
 * @author Fu Shunkai(fusk@peptalk.cn)
 * @version 
 */

//require_once 'Zend/Controller/Action.php';
require_once 'Better/Mobile/Front.php';
require_once 'Better/User/Login.php';

class Polo_LoginController extends Better_Mobile_Front {
	
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$this->view->ref_url = $this->getRequest()->getParam('ref_url', '');
		$post = $this->getRequest ()->getPost ();
		
		if( isset($post['email']) || isset($post['password']) ){	
			$filters = array ('email' => 'StringTrim', 'password' => 'StringTrim' );
			$validation = array ('email' => array (array ('StringLength', 4, 50 ) ), 'password' => array (array ('StringLength', 4, 50 ) ) );
		
			$zfi = new Zend_Filter_Input ( $filters, $validation, $post );
			$pwdMd5 = (isset ( $post ['pwd_plain'] ) && $post ['pwd_plain'] == '1') ? false : true;
			$this->view->err = array( 'has_err' => 0, 'err'=>'' );
			$loginMsg;
			$this->view->username = $post['email'];
		
			if ($zfi->isValid ()) {
				$remember = (isset ( $post ['rememberme'] ) && $post ['rememberme'] == '1') ? true : false;
				$result = Better_User_Login::login ( $post ['email'], $post ['password'], false, $remember );
				
				switch ($result) {
					case Better_User_Login::INVALID_PWD :
						$loginMsg = $this->lang->error->login->password_incorrect;
						break;
					case Better_User_Login::NEED_VALIDATED :
						$loginMsg = $this->lang->error->login->account_not_actived;
						break;
					case Better_User_Login::ACCOUNT_BANNED :
						$loginMsg = $this->lang->error->login->account_banned;
						break;
					case Better_User_Login::FORCE_VALIDATING :
						$loginMsg = $this->lang->error->login->force_validating;
						break;
					default :
						$loginMsg = $this->lang->error->login->unknown;
						break;
				}
			} else {
				$loginMsg = $this->lang->error->login->password_incorrect;
			}
			
			if ($result == Better_User_Login::LOGINED) {
				$this->_helper->getHelper ( 'Redirector' )->gotoUrl ( '/polo/home' );
			} else if($result == Better_User_Login::JUMP_SIGNUP){
				
			}else {
				$this->view->err['has_err'] = 1; 
				$this->view->err['err'] = $loginMsg;
				$this->view->headScript ()->prependScript ( "
    				var Better_LoginMsg = '{$loginMsg}';
    			" );
			}
		}	
	}
	
	public function logoutAction(){
		Better_User_Login::logout();
		$this->_helper->getHelper('Redirector')->gotoUrl('/polo?force_redirect=1');
		exit(0);
	}
}

