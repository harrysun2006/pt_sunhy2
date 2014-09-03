<?php

/**
 * SettingController
 * 
 * @author
 * @version 
 */

//require_once 'Zend/Controller/Action.php';

class Polo_SettingController extends Better_Mobile_Front {	

	
	
	public function init()
	{
		parent::init();
		$this->poloneedLogin();		
		$this->view->cityArray =Better_Citycenterll::$cityArray;
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() { 
	}
	
	public function avatarAction(){
	}
	
	public function passwordAction(){
		
	}
	
	public function basicAction(){	
		$this->view->isValidating = $this->user->needValidate() ? 1 : 0;
		$this->view->lastRequestEmail = $this->view->isValidating ? Better_User_Bind_Email::getInstance($this->uid)->lastRequestEmail() : '';
	}
	
	public function privacyAction(){
		
	}
	public function provinceAction(){
		
	}
	public function cityAction(){
		foreach($this->view->cityArray as $rows){	
			if($rows['0']==$this->userInfo['live_province']){
				$temp_city = split("\|",$rows[1]);				
				$this->view->city = $temp_city;
				break;
			}			
		}	
	}
	public function updateAction()
	{
		$post = $this->getRequest()->getPost();

		if ($this->getRequest()->isPost()) {
			//zend_debug::dump($post['todo']);
			
			switch($post['todo']) {
				case 'basic' :	//	更新基本资料
					$this->view->err = $this->_updateBasicInfo();	
					//Zend_debug::dump($this->view->err);
					
					if( $this->view->err['has_err'] ){									
						echo $this->view->render('setting/basic.phtml');
						exit(0);
					}else{
						echo $this->view->render('setting/index.phtml');									
						exit(0);
					}					
					break;
				case 'avatar':	//	更新头像
					$this->view->err = $this->_updateAvatar();	
					echo $this->view->render('setting/index.phtml');
					exit(0);		
					break;
				case 'del_avatar':	//	删除头像								
					$this->view->err = $this->_delAvatar();					
					echo $this->view->render('setting/index.phtml');
					exit(0);
					break;
				case 'password':		//	修改密码
					$this->view->err = $this->_updatePwd();		
									
					if( $this->view->err['has_err'] ){
						echo $this->view->render('setting/password.phtml');
						exit(0);
					}else{						
						echo $this->view->render('setting/index.phtml');	
						exit(0);					
					}				
					break;
				case 'privacy':			//	修改隐私策略
					$this->view->err = $this->_updatePrivacy();	
							
					if($this->view->err['has_err'] ){
						echo $this->view->render('setting/privacy.phtml');
						exit(0);	
					} else {
						echo $this->view->render('setting/index.phtml');
						exit(0);
					}
					break;
				case 'province':		
					$this->view->err = $this->_updateProvince();
					if( !$this->view->err['has_err'] ){
						echo $this->view->render('setting/index.phtml');
						exit(0);	
					}
					break;	
				case 'city':			
					$this->view->err = $this->_updateCity();
					if( !$this->view->err['has_err'] ){
						echo $this->view->render('setting/index.phtml');
						exit(0);	
					}
					break;	
				default:
					$this->view->err = array();
					$this->view->err['message'] = "Unknown command";
					echo $this->view->render('setting/index.phtml');
			}
		}
		
		else {
			echo $this->view->render('setting/index.phtml');
		}
		
		exit(0);		
		
	}
	
	private function _updatePrivacy()
	{
		$post = $this->getRequest()->getPost();
		
		$now = time();
		$update_priv_blog_times = Better_User::getInstance($this->uid)->cache()->get('update_priv_blog_times');
		
		if($now-$update_priv_blog_times<Better_Config::getAppConfig()->update_priv_blog_time_limit){			
			return array('has_err'=>1, 'err'=>$this->lang->setting->privacy->priv_blog_time_limit);			
		} else {		
			$data = array(
				'priv_blog' => ($post['priv_blog']=='on')?1:0,
			);
			$rows['priv_blog'] = $data['priv_blog'];
			Better_User::getInstance($this->uid)->cache()->set('update_priv_blog_times',$now);
			Better_Registry::get('user')->updateUser($data);
			return array('has_err'=>0, 'err'=>$this->lang->setting->privacy->success);
		}		
	
		
		
	}
	
	private function _updateProvince()
	{
		$post = $this->getRequest()->getPost();
		$return = array('has_err'=>0, 'err'=>'');		
		$data = array(
			'live_province' => $post['live_province'],			
		);
		$checked = 0;
		foreach($this->view->cityArray as $rows){		
			if($rows['0']==$post['live_province']){
				$checked = 1;
				break;
			}
		}		
		if($checked){
			Better_Registry::get('user')->updateUser($data);
			$return['has_err'] = 0;
			$return['err'] = $this->lang->setting->province->success;
			$this->_redirect('/polo/setting/city');
		} else {
			$return['has_err'] = 1;
			$return['err'] = $this->lang->setting->province->false;
		}
		return $return;
	}
	private function _updateCity()
	{
		$post = $this->getRequest()->getPost();
		$return = array('has_err'=>0, 'err'=>'');		
		$data = array(
			'live_city' => $post['live_city'],			
		);
		$checked = 0;		
		foreach($this->view->cityArray as $rows){	
			if($rows['0']==$this->userInfo['live_province']){
				$temp_city = split("\|",$rows[1]);				
				for($j=0;$j<count($temp_city);$j++){
					$a =strpos($post['live_city'],$temp_city[$j]);				
					if($a === false)
					{
						
					} else {
						$checked = 1;
						break;
					}
				}				
			}			
		}
		if($checked){
			Better_Registry::get('user')->updateUser($data);
			$return['has_err'] = 0;
			$return['err'] = $this->lang->setting->city->sucess;			
		} else {
			$return['has_err'] = 1;
			$return['err'] = $this->lang->setting->city->false;
		}
		return $return;
		
	}
	
	private function _updatePwd()
	{
		$post = $this->getRequest()->getPost();
		$pwd = $post['pass'];
		$repwd = $post['repass'];
		$return = array('has_err'=>0, 'err'=>'');		

		if (strlen($pwd)<6 ) {
			$return['has_err'] = 1;
			$return['err'] = $this->lang->signup->password_to_short;		
		} else if (strlen($pwd)>20 ){
			$return['has_err'] = 1;
			$return['err'] = $this->lang->signup->password_too_long;	
		} else if (trim($pwd)==''){
			$return['has_err'] = 1;
			$return['err'] = $this->lang->mobile->global->notspace;
		} 
		 else if ($repwd!=$pwd) {
			$return['has_err'] = 1;
			$return['err'] = $this->lang->signup->password_not_match;
		} else {
			Better_User::getInstance($this->uid)->updateUser(array(
				'password' => md5($pwd)
				));
			
			$return['has_err'] = 0;
			$return['err'] = $this->lang->api->account->password_modified;
		}			
		return $return;
	}
	
	private function _delAvatar()
	{
		$return = array(
			'has_err' => 1,
			'err' => '',
			'ref' => '/polo/setting/avatar'
		);
		Better_User::getInstance($this->uid)->avatar()->delete();
		$return['has_err'] = 0;
		$return['err'] = $this->lang->profile->avatar->deleted;
		
		return $return;
	}
	
	private function _updateAvatar()
	{
		/*
		$d = array();
		$post = $this->getRequest()->getPost();
		$avatar = $post['avatar'];
		$id = Better_User_Avatar::getInstance($this->uid)->upload();
        $return = array('has_err'=>1);
              
        if (is_array($id) && isset($id['url'])) {
        	$return['data']['url'] = $id['url'];
        	$return['data']['thumb'] = $id['url'];
        	$return['data']['tiny'] = $id['url'];
        	$return['data']['file_id'] = $id['file_id'];
        	$return['has_err'] = 0;
        	
        } else {
        	$return['err'] = $id;
        }

       	return $return;*/
		$result = array(
			'has_err' => 1,
			'err' => '',
		);
		
		if (is_array($_FILES) && isset($_FILES['attach'])) {
			$_FILES['myfile'] = &$_FILES['attach'];
			$status = $this->user->avatar()->upload();
			
			if (is_array($status) && isset($status['url'])) {
				$result['has_err'] = 0;
				$result['err'] = $this->lang->api->profile->avatar->updated;				
			} else {
				$result['err'] = $this->lang->api->error->profile->avatar->invalid_file; 
			}
		} else {
			$result['err'] = $this->lang->api->error->profile->avatar->invalid_file;
		}
		
		return $result;
	}
	
	private function _updateBasicInfo()
	{
		$return = array(
			'has_err' => 1,
			'err' => array(),
			);
					
		$err = array();
		$post = $this->getRequest()->getPost();
		$post['uid'] = $this->uid;
		$post['passby_pass'] = true;
		$post['passby_cell'] = true;
		$post['email'] || $post['email'] = $this->userInfo['email'];
		
		$result = Better_User_Signup::quickCheck($post);
		
		$code = $result['code'];
		$codes = &$result['codes'];
		
		switch ($code) {
			case $codes['USERNAME_REQUIRED']:
				$err['err_username'] = $this->lang->setting->basic->username->empty;
				break;
			case $codes['NICKNAME_REQUIRED']:
				$err['err_nickname'] = $this->lang->signup->nickname->empty;
				break;
			case $codes['EMAIL_INVALID']:
				$err['err_email'] = $this->lang->error->email_invalid;
				break;
			case $codes['EMAIL_EXISTS']:
				$err['err_email'] = $this->lang->error->email_exists;
				break;
			case $codes['NICKNAME_TOO_SHORT']:
				$err['err_nickname'] = $this->lang->signup->nickname->too_short;
				break;
				
			case $codes['NICKNAME_TOO_LONG']:
				$err['err_nickname'] = $this->lang->signup->nickname->too_long;
				break;
			case $codes['NICKNAME_FORBIDEN_WORD']:
				$err['err_nickname'] = $this->lang->signup->nickname->forbidden_words;
				break;
			case $codes['NICKNAME_EXISTS']:
				$err['err_nickname'] = $this->lang->signup->nickname->already_taken;
				break;
				
			case $codes['USERNAME_TOO_LONG']:
				$err['err_username'] = $this->lang->signup->username->too_long;
				break;
			case $codes['USERNAME_FORBIDEN_WORD']:
				$err['err_username'] = $this->lang->signup->username->forbidden_specialChar;
				break;
			case $codes['USERNAME_EXISTS']:
				$err['err_username'] = $this->lang->setting->basic->username->already_taken;
				break;
			case $codes['USERNAME_TOO_SHORT']:
				$err['err_username'] = $this->lang->signup->username->too_short;
				break;
			case $codes['BAN_WORDS']:
				$err['err_intro'] = $this->lang->signup->selfintro->ban_words;
				break;
			case $codes['PASSWORD_INVALID']:
			case $codes['CELL_INVALID']:
			case $codes['CELL_EXISTS']:
			case $codes['FAILED']:
			case $codes['SUCCESS']:				
			default:
				break;
		}		
			
		if (count($err)>0) {
			$return['err'] = $err;
		} else {
			//zend_debug::dump($post);
			if (Better_Registry::get('user')->updateUser($post)) {
				$return['has_err'] = '0';
				$return['err'] = $this->lang->setting->basic->success;
			}
		}		
		return $return;
	}
	
	private function _verifyBirthday( $birthday ){
		
	}
}
