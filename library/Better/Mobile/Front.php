<?php
/**
 * Mobile Front Controller
 * 
 * @package Better.Controller
 * @author Fu shunkai <fusk@peptalk.cn>
 *
 */

abstract class Better_Mobile_Front extends Better_Controller
{
	protected $lang = null;
	protected $uid = 0;
	protected $user = null;
	protected $userInfo = array();
	//protected $lastcheckin = null;
	
	public function __destruct()
	{
		$language = Better_Registry::get('language');
		if($language!='en'){
			$language = 'zh-cn';
		}
		if ($this->uid && $language) {
			Better_User::getInstance($this->uid)->updateUser(array(
				'language' => $language
				));
		}		
	}	
	
	/**
	 * 前台控制器初始化
	 *
	 */
	public function init()
	{		
		parent::init();
		
		$pLanguage = $this->getRequest()->getParam('_language_');
		if($pLanguage!='en'){
			$pLanguage = 'zh-cn';
		}
		if ($pLanguage) {
			setcookie('lan', $pLanguage);			
			$_COOKIE['lan'] = $pLanguage;
		}
		
		$sess = Better_Session::factory();
		$sess->init();
		
		$this->uid = &Better_Registry::get('sess')->uid;
		$this->user = Better_User::getInstance($this->uid);
	
		//将用户数据注册到View变量
		$this->view->user = $this->uid ? Better_User::getInstance($this->uid)->getUser() : array();
		$this->userInfo = &$this->view->user;
		
		if ($this->uid) {
			$lastcheckin = Better_User_Checkin::getInstance($this->uid)->history(1,1);
		} else {
			$lastcheckin = array(
				'rows' => array()
				);
		}
		$this->lastcheckin = $lastcheckin['rows'][0];
		$this->view->lastcheckin = $this->uid ? $this->lastcheckin : array();
		
		//加载语言包
		$this->view->lang = Better_Language::load();
		$this->lang = &$this->view->lang;
		
		//$this->view->css = 'default';
		
		$this->sendSquidHeader($this->uid);
	}
	
	/**
	 * 需要登录方可使用的控制器可在init方法内调用该方法，调用后浏览器将重定向到登录页面
	 *
	 */
	protected function needLogin()
	{
		if ($this->uid<=0) {
			if (preg_match('/('.$cs.')/i', $params['controller'])) {
				Better_Registry::get('sess')->set('ref_url', base64_encode($_SERVER['REQUEST_URI']));
			}
			//$this->_helper->getHelper('Redirector')->gotoSimple('index','login');
			//$this->_helper->getHelper('Redirector')->gotoSimple('index','/mobile/login');
			$this->_redirect('/mobile/login');
			exit(0);
		}
	}
	protected function poloneedLogin()
	{
		if ($this->uid<=0) {
			if (preg_match('/('.$cs.')/i', $params['controller'])) {
				Better_Registry::get('sess')->set('ref_url', base64_encode($_SERVER['REQUEST_URI']));
			}
			//$this->_helper->getHelper('Redirector')->gotoSimple('index','login');
			//$this->_helper->getHelper('Redirector')->gotoSimple('index','/mobile/login');
			$this->_redirect('/polo/login');
			exit(0);
		}
	}
	
	/**
	 * 通用的meta关键字，描述
	 *
	 */
	protected function commonMeta()
	{
		$this->view->headMeta()->appendName('keywords', 'Better is best');
	}
	
	protected function appendScript($file)
	{
		$this->view->headScript()->appendFile($file, 'text/javascript', array(
    		'defer' => 'defer',
    		));
	}
	
	protected function parseAchievement()
	{
		$ac = '';
		
		if ($this->uid) {
			$as = $this->user->achievement()->parse($prefix);
			
			if (count($as)) {
				$ac = implode(', ', $as); 
			}
		}
		
		$checkinMsg = Better_Registry::get('checkin_msg');
		if (trim($checkinMsg)!='') {
			$return = trim($checkinMsg);
		} else {
			$lang = Better_Language::loadIt(Better_Registry::get('language'));
			$prefix = $lang->javascript->sketch->noshowaction->delta->action.' ';
			$return = trim($ac)!='' ? $prefix.$ac : '';
		}
		Better_Registry::get('sess')->set('last_achievement', $return);
		/*
		if ($this->uid) {
			$as = $this->user->achievement()->parse();
			if (count($as)) {
				$lang = Better_Language::loadIt(Better_Registry::get('language'));
				$prefix = $lang->javascript->sketch->noshowaction->delta->action.' ';
				Better_Registry::get('sess')->set('last_achievement',  $prefix.implode(',', $as));
			}
		}
		*/		
	}	
}