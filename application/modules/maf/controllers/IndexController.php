<?php

/**
 * 用户未登录时的主页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class Maf_IndexController extends Better_Controller_Front
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	/*
    	$forceRedirect = $this->getRequest()->getParam('force_redirect', 0);
	
		if (!$forceRedirect && Better_Functions::isWap()) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/mobile');
			exit(0);
		}
		 */  	
    	$this->commonMeta();    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/maf.js?ver='.BETTER_VER_CODE); 
    	
    }

    public function indexAction()
    {

    	$from_id = $this->getRequest()->getParam('from', 0);
    	$visit_time = time();
    	if($from_id){
    		$data = array('partner_id'=> $from_id,
    				  'visit_time'=> $visit_time,
    				  'visit_ip'=> Better_Functions::getIP()
    				   );
    		Better_DAO_Frompartner::getInstance()->insert($data);
    		
    		Better_Registry::get('sess')->set('web_from', $from_id);
    	}
    	//登陆操作  
		if ($this->getRequest()->isPost()) {		
			$filters = array(
				'email' => 'StringTrim',
				);
			$validation = array(
				'email' => array(
									array('StringLength', 4, 50),
								),
				);
			$post = $this->getRequest()->getPost();

			$zfi = new Zend_Filter_Input($filters, $validation, $post);
			$pwdMd5 = (isset($post['pwd_plain']) && $post['pwd_plain']=='1') ? false : false;
			$loginMsg = '';

			if ($zfi->isValid() && $post['password']!='') {

				$remember = (isset($post['rememberme']) && $post['rememberme']=='1') ? true : false;
				$login_type = isset($post['login_type']) ? $post['login_type']: 'local';				
				$result = Better_User_Login::newlogin($post['email'], $post['password'],$login_type, $pwdMd5, $remember);
				
				switch ($result) {
					case Better_User_Login::INVALID_PWD:
						$loginMsg = $this->lang->error->login->password_incorrect;
						break;
					case Better_User_Login::NEED_VALIDATED:
						$loginMsg = $this->lang->error->login->account_not_actived;
						break;
					case Better_User_Login::ACCOUNT_BANNED:
						$loginMsg = $this->lang->error->login->account_banned;
						break;
					case Better_User_Login::FORCE_VALIDATING:
						$loginMsg = $this->lang->error->login->force_validating;
						break;
					default:
						$loginMsg = $this->lang->error->login->unknown;
						break;
				}
			} else {
				$loginMsg = $this->lang->error->login->password_incorrect;
			}	
			//Zend_Debug::dump($result);
			//Zend_Debug::dump($loginMsg);
				//exit;		
			if ($result==Better_User_Login::LOGINED) {
				$ref_url = $post['ref_url'] ? $post['ref_url'] : Better_Registry::get('sess')->get('ref_url');
				$ref_url = base64_decode($ref_url);

				$this->_helper->getHelper('Redirector')->gotoUrl('/maf/index/step2');
				//}
			} else {	
							
				$this->view->headScript()->prependScript("
    	var Better_LoginMsg = '".addslashes($loginMsg)."';
    	");
			}
	
		}
		//登陆操作结束
    	
    	// 如果已登录，则定向到home页
        if ($this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoUrl('/maf/index/step2');
        	exit(0);
        }
    }
    public function step2Action(){
    	if (!$this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoUrl('/maf/index');
        	exit(0);
        }
    	$havereg = Better_Mafcard::getMafCardUser();
    	$minecard = Better_Mafcard::getMyMafCard($this->uid);
    	//Zend_Debug::dump($minecard);  
    	$user = Better_User::getInstance($this->uid);
		$userInfo = $user->getUser();		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);		
    	$card_num = Better_Config::getAppConfig()->maf_card_num;
		$endtime = Better_Config::getAppConfig()->maf_time;		
		if(time()>=Better_Config::getAppConfig()->maf_time){
			$this->view->mafcard = 1;		//超过活动时间	
		} else if(count($havereg)>=$card_num){
			$this->view->mafcard = 2;		//发放完毕	
		} else if(count($minecard)>0){
			$this->view->mafcard = 3;		//已经拥有了
		} else if(in_array(28,$gotBids)){
			$this->view->mafcard = 4;		//可以拥有
		} else {
			$this->view->mafcard = 5;      //不可以拥有
		}
		$this->view->headScript()->appendScript('
    			var mafcardnum = '.$this->view->mafcard.';
    		');   	
    }
    public function step3Action(){
   		if (!$this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoUrl('/maf/index');
        	exit(0);
        }
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/maf/step3.js?ver='.BETTER_VER_CODE);
    	$this->view->card=(json_decode(Better_Registry::get('sess')->get('mafcard'),true));    	
    }
    public function viewAction(){
    	if (!$this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoUrl('/maf/index');
        	exit(0);
        }
    	if ($this->getRequest()->isPost()) {     		
    		$post = $this->getRequest()->getPost(); 
    		$params = array(
    			"receive_name" => $post['receive_name'],
    			"receive_address" => $post['receive_address'],
    			"receive_zipcode" => $post['receive_zipcode'],
    			"post_name" => $post['post_name'],
    			"post_address" => $post['post_address'],
    			"post_zipcode" => $post['post_zipcode'],
    			"message" =>$post['message'],
    			"uid" => $this->uid
    		);
    		$mafcard = json_encode($params);
    		$this->view->headScript()->appendScript('
    			var cardInfo = '.$mafcard.';
    		');
    		//Zend_Debug::dump($mafcard);
    		Better_Registry::get('sess')->set('mafcard',$mafcard);
    		$this->view->message = $post;
    		$this->view->card = $mafcard;    		
    	}
    }    
}

