<?php

/**
 * 前台控制器
 * 
 * @package Better.Controller
 * @author leip <leip@peptalk.cn>
 *
 */

abstract class Better_Controller_Front extends Better_Controller
{
	protected $lang = null;
	protected $uid = 0;
	protected $user = null;
	protected $dispUser = null;
	protected $userInfo = array();
	protected $dispUserInfo = array();
	protected $dispUid = 0;
	protected $poiId = 0;
	protected $cbFiles = array();
	protected $sess = null;
	
	public function __destruct()
	{
	}
	
	/**
	 * 一个轻量级的初始化
	 * 
	 * @return
	 */
	public function simpleInit()
	{
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		parent::init();
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				
		@ob_start();
		if (strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {
			//	@ob_start('ob_gzhandler');
		}				
				
		//加载语言包
		$this->view->lang = Better_Language::load();
		$this->lang = &$this->view->lang;
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		$sess = Better_Session::factory();
		$sess->init();
		$this->sess = &$sess;
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);

		$this->uid = &Better_Registry::get('sess')->uid;
		$this->user = Better_User::getInstance($this->uid);
		
		if($this->uid){
				$user = $this->user->getUser();
				if($user['state']=='banned'){
					Better_Registry::get('sess')->set('uid', null);
					Better_User_AutoLogin::clear();
					$this->_helper->getHelper('Redirector')->gotoUrl('/');
					exit(0);	
				}
		}
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
		//将用户数据注册到View变量
		$viewUser = $this->uid ? $user : array();
		$this->view->user = $viewUser;
		$this->view->thisUser = $this->user;
		$this->userInfo = &$viewUser;

		$this->view->css = 'default';
		
		$this->view->config = $this->config;
		
		$this->view->shout_title = $this->lang->global->shout->title;
		$this->view->shout_type = 'normal';
		$this->view->shout_text = $this->lang->global->shout->text;
		
		$this->view->uid = $this->uid;
		$this->view->you = $this->lang->global->you;
		$this->view->isyou = true;
		$this->view->dispUserInfo = $this->userInfo;
		$this->view->dispUser = $this->user;
		$this->view->inHome = false;		

		$this->view->needCheckinJs = false;
		$this->view->needPostJs = true;
		
		$this->view->needCellJs = false;
		$this->view->needEmailJs = false;
		
		$this->view->kai_spec = 0;		
			
		if($this->uid){
			$cacher = $this->user->cache();
			
			//好友请求数
			$friend_request_count = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
				'type'=> array('friend_request'),
				'readed'=> 0,
			));
			
			$this->view->frCount = $friend_request_count;
			//私信数
			$msg_count = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
				'type'=> array('direct_message','invitation_todo'),
				'readed'=> 0,
			));
			$this->view->msCount = $msg_count;
			
			//未读被转发的数量
			//$rt_count = (int)$cacher->get('rt_blogs_count');
			$this->view->rtCount = 0;
			
			//新增粉丝数量
			$this->view->foCount = 0;
			
			$this->view->total = $friend_request_count+$msg_count;
			
			$sites = Better_User_Syncsites::getInstance($this->uid)->getSites();
			$this->view->site_count = count($sites);
		}		
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
		$this->sendSpecHeader();
		$this->sendSquidHeader((int)$this->uid);
	}
	
	/**
	 * 前台控制器初始化
	 *
	 */
	public function init()
	{				
		$this->simpleInit();
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
		$this->dispUser = $this->user;
		$this->dispUserInfo = $this->userInfo;
		
		//注册用户IP地址
		$this->view->remoteIp = Better_Functions::getIP();
		
		/*if ($this->uid) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->user->friends()->getFriends();
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->user->block()->getBlocks();
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->user->block()->getBlockedBy();
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->user->favorites()->getAllBids();
			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}*/

		$poiId = $this->getRequest()->getParam('poi_id', '');
		if ($poiId!='') {
			$this->poiId = (int)(BETTER_HASH_POI_ID ? Better_Poi_Info::dehashId($poiId) : $poiId);
		}

		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
	}
	
	/**
	 * 需要登录方可使用的控制器可在init方法内调用该方法，调用后浏览器将重定向到登录页面
	 *
	 */
	protected function needLogin()
	{
		if ($this->uid<=0) {
			$params = $this->getRequest()->getParams();
			$cs = Better_Config::getAppConfig()->routes->exclude_controllers;
			
			if (preg_match('/('.$cs.')/i', $params['controller'])) {
				Better_Registry::get('sess')->set('ref_url', base64_encode($_SERVER['REQUEST_URI']));
			}
			$this->_helper->getHelper('Redirector')->gotoSimple('index','index');
			exit(0);
		}
	}
	
	/**
	 * 通用的meta关键字，描述
	 *
	 */
	protected function commonMeta()
	{
		//$this->view->headMeta()->appendName('keywords', $this->config->meta->keywords);
	}
	
	protected function appendScript($file)
	{
		$this->view->headScript()->appendFile($file, 'text/javascript', array(
    		//'defer' => 'defer',
    		));
	}
	
	protected function parseRightBar()
	{
		$html = '';
		
		if ($this->view) {
			$view = clone($this->view);
			$view->setScriptPath(APPLICATION_PATH.'/modules/default/views/scripts/include');
			$this->userRightbar($view);
			$html = $view->render('rightbar.phtml');
		}

		return $html;
	}
	
	/**
	 * 我的右边栏
	 * 
	 * @return 
	 */
	protected function myRightBar(&$view)
	{
		
	}
	
	/**
	 * 别人的右边栏
	 * 
	 */
	protected function userRightBar($uid=0)
	{
		if ($this->uid && $this->dispUserInfo['uid']!=$this->uid) {
			$this->view->hasRequest = (bool)$this->dispUser->follow()->hasRequest($this->uid);
			$this->view->hasFriendRequest = (bool)$this->dispUser->friends()->hasRequest($this->uid);
		} else {
			$this->view->hasRequest = false;
			$this->view->hasFriendRequest = false;
		}

		$this->view->headScript()->prependScript('
		var hasFriendRequest = "'.(int)$this->view->hasFriendRequest.'";
		var hasMyFriendRequest = "'.(($this->uid && $this->dispUserInfo['uid']!=$this->uid) ? (int)$this->user->friends()->hasRequest($this->dispUserInfo['uid']) : 0).'";
		var hasMyFollowRequest = "'.(($this->uid && $this->dispUserInfo['uid']!=$this->uid) ? (int)$this->dispUser->follow()->hasRequestToMe($this->uid) : 0).'";
		');	
			
		$this->view->visitors = $this->dispUser->visit()->rightbar();
		
		$friends = &$this->dispUser->friends()->rightbar();		
		$this->view->friends = $friends;

		$badges = $this->dispUser->badge()->getMyBadges();
		$this->view->badges = $badges;

		$majors = &$this->dispUser->major()->rightbar();
		$this->view->majors = $majors;			
		
		return $this->view;
	}
	
	/**
	 * 出错页面
	 * @param unknown_type $msg
	 */
	protected function error($msg='')
	{
		$this->render('error/');
	}
}