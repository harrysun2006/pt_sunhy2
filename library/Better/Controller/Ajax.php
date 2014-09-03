<?php

/**
 * Ajax控制器基类
 * 
 * @package Better.Controller
 * @author leip
 *
 */

class Better_Controller_Ajax extends Better_Controller
{
	protected $output = array();
	protected $user = null;
	protected $userInfo = array();
	protected $do = '';
	protected $error = '';
	protected static $NEED_LOGIN = 'NEED_LOGIN';
	protected static $INVALID_DATA = 'INVALID_DATA';
	protected static $DATA_ERROR = 'DATA_ERROR';
	protected static $SUCCESS = 'success';
	protected $qbsDefaultW = 50000;
	protected $qbsDefaultH = 50000;
	protected $page = 1;
	protected $outputed = false;

	public function __destruct()
	{

	}
		
	public function init($needLogin=true)
	{
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();

		$sess = Better_Session::factory();
		$sess->init();

		//将用户数据注册到View变量
		$this->uid = &Better_Registry::get('sess')->get('uid');
		$this->view->user = $this->uid ? Better_User::getInstance($this->uid)->getUser() : array();
		
		//加载语言包
		$this->view->lang = Better_Language::load();
		$this->lang = &$this->view->lang;
			
		$needLogin && $this->needLogin();

		if ($this->uid) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->user = Better_User::getInstance($this->uid);
			$this->userInfo = $this->user->getUserInfo();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->follow()->getFollowings();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->follow()->getFollowers();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->friends()->getFriends();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->block()->getBlocks();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->block()->getBlockedBy();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->favorites()->getAllBids();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);			
		}

		$this->do = $this->getRequest()->getParam('do');
		$this->qbsDefaultW = Better_Config::getAppConfig()->qbs->default_w;
		$this->qbsDefaultH = Better_Config::getAppConfig()->qbs->default_h;
		$this->page = (int)$this->getRequest()->getParam('page', 1);
		
		$needLogin && $this->output['uid'] = $this->uid;
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
	}
	
	/**
	 * 输出一个需要登录的错误
	 *
	 * @see controllers/FrontController#needLogin()
	 */
	protected function needLogin()
	{
		if (!$this->uid) {
			$this->error(self::$NEED_LOGIN);
		}
	}


	/**
	 * 输出错误
	 *
	 * @param string $msg
	 * @return null
	 */
	protected function error($msg)
	{
		$this->sendSquidHeader((int)$this->uid);
		$this->sendSpecHeader();
		
		$this->outputed = true;
		
		$this->output['exception'] = $msg;
		$this->output['uid'] = $this->uid;
		echo json_encode($this->output);
		exit(0);
	}
	
	protected function outputHtml()
	{
		echo $this->output;
		exit(0);
	}
	
	/**
	 * 输出json数据
	 *
	 * @return null
	 */
	protected function output()
	{
		$this->outputed = true;
		
		$this->output['achievement'] = '';
		
		if (APPLICATION_ENV!='production') {
			$this->output['exec_time'] = $this->view->execTime();
		}
		
		if ($this->error) {
			$this->output['exception'] = $this->error;
		}

		if ($this->uid) {
			$as = $this->user->achievement()->parse();
			if (count($as)) {
				$this->output['achievement'] = implode(',', $as);
			}
		}
		
		$this->output['checkin_exception'] = Better_Registry::get('checkin_msg') ? 1 : 0;
		
		$output = json_encode($this->output);

		if ($this->acceptGz()) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			header('Content-Encoding: gzip');
			$output = gzencode($output, 2, FORCE_GZIP);
			header('Content-Length: '.strlen($output));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}		
		
		$this->getResponse()->sendHeaders();
		$this->sendSquidHeader((int)$this->uid);
		$this->sendSpecHeader();

		echo $output;
		exit(0);
	}
		
	/**
	 * 生成右侧长栏
	 * 
	 * @return 
	 */
	protected function processRightbar($home=false)
	{
		return '';
		$html = '';
		
		$this->userInfo = $this->user->parseUser(array(), false, true);
		
		$view = new Zend_View();
		$view->lang = $this->lang;

		$view->hasRequest = $this->user->follow()->hasRequest($this->uid);
		$view->hasFriendRequest = $this->user->friends()->hasRequest($this->uid);

		$view->visitors = $this->user->visit()->getVisitors();		
		$data = $this->user->friends()->all(1, 18);
		$view->friends = $data['rows'];
		
		$view->hasRequests = $this->user->follow()->hasRequests();
		
		$data = $this->user->badge()->getMyBadges();
		$view->badges = $data;
		
		$data = $this->user->major()->getAll(1, 10);
		$view->majors = $data['rows'];				
		
		$data = $this->user->notification()->all()->getReceiveds(array(
			'type' => array('friend_request', 'follow_request'),
			'readed' => 0,
			'page' => 1,
			'count' => 3,
			));
		$view->ns = $data['rows'];
		$view->nsCount = $data['count'];
		
		$view->uid = $this->uid;
		$view->you = $this->lang->global->you;
		$view->isyou = true;
		$view->dispUserInfo = $this->userInfo;
		$view->dispUser = $this->user;
		
		$view->inHome = $home;
		
		$view->setScriptPath(APPLICATION_PATH.'/modules/default/views/scripts/include');
		$html = $view->render('rightbar.phtml');
		
		$this->output['bar'] = $html;
	}
}