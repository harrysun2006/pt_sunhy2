<?php

/**
 * 用户空间首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class UserController extends Better_Controller_Front 
{
	
	protected $dispUser = null;
	protected $dispUserInfo = array();
	protected $params = array();

	public function init()
	{
		parent::init();
		
		$username = trim( $this->getRequest()->getParam('username', '' ) );
    	if(!$username){
    		$this->needLogin();
    		exit(0);
    	}
    	
		$this->commonMeta();

    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/user.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	
    	$userInfo = $this->user->getUserInfo();
    	
		if (preg_match('/^kai([0-9]+)$/is', $username)) {
			$uid = (int) str_replace('kai', '', $username);
		}
		
    	if ($uid) {
    		$this->dispUser = Better_User::getInstance($uid);
    		$this->dispUserInfo = $this->dispUser->getUser();
    		
    		if ($this->dispUserInfo['uid']<=0) {
    			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    		} else {
    			//$this->_helper->getHelper('Redirector')->gotoSimple('index',$this->dispUserInfo['username']);
    		}
    		
    	} else if ($username && $username!=$userInfo['username']) {
 			$this->dispUser = Better_User::getInstance();
			$this->dispUserInfo = $this->dispUser->getUserByUsername($username);
			if ($this->dispUserInfo['uid']<=0) {
				throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
			}
    	} else if ($username==$userInfo['username']) {
 	    	$this->dispUser = Better_Registry::get('user');
	    	$this->dispUserInfo = &$userInfo;
    	} else {
	    	$this->dispUser = Better_Registry::get('user');
	    	$this->dispUserInfo = &$userInfo;
			$this->_helper->getHelper('Redirector')->gotoUrl('/'.$this->dispUserInfo['username']);
    	}
		
    	$user_state = $this->dispUserInfo['state'];
    	if($user_state=='banned'){
			 $this->_helper->getHelper('Redirector')->gotoSimple('accountbanned', 'help');
			 exit();   		
    	}
    	
    	$this->view->userInfo = $this->dispUserInfo;

    	if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		var inOther_trace = false;
    		');
    	} else {
			$this->view->headScript()->prependScript('
			var dispUser = new BetterUser("'.$this->dispUserInfo['uid'].'", "'.$this->dispUserInfo['username'].'", "'.addslashes($this->dispUserInfo['nickname']).'", "", "");
			dispUser.priv_blog = '.intval($this->dispUserInfo['priv_blog']).';
			dispUser.priv = "'.$this->dispUserInfo['priv'].'";
			dispUser.avatar_small = "'.$this->dispUserInfo['avatar_small'].'";
			dispUser.friend_sent_msg = "'.$this->dispUserInfo['friend_sent_msg'].'";
			var inOther_trace = true;
			');
    	}
  	
    	//page one
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;
		
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		$flag = $userObj->canViewDoing($this->dispUserInfo['uid']);
		
		$without_me = ($userInfo['uid'] == $this->dispUserInfo['uid']) ? false : true;	
		$result = $userObj->status()->getSomebody(array(
			'page' => 1,
			'type' => array('normal', 'checkin', 'tips'),
			'page_size' => BETTER_PAGE_SIZE,
			'uid' => $this->dispUserInfo['uid'],
			'without_me' => $without_me,
			'ignore_block' => true
			));
		$_output['rows'] = Better_Output::filterBlogs($result['rows']);
		$_output['pages'] = &$result['pages'];
		$_output['count'] = $result['count'];
		$_output['cnt'] = $result['cnt'];
		$_output['page'] = $this->page;		
		$_output['rts'] = &Better_Output::filterBlogs($result['rts']);	

		$jsonPage1 = json_encode($_output);   
    	//end
    	$this->view->pagerhtml = $this->_getPager($result['cnt'], BETTER_PAGE_SIZE);
		
		$followers = $this->uid ? $this->user->follow()->getFollowers() : array();
    	$this->view->headScript()->prependScript('
    		betterUser.followers = '.((is_array($followers) && count($followers)>0) ? '["'.implode('","', $followers).'"]' : '[]').';
    		var personpage = true;
    		var needRef = false;
    		var _page_1 = ' . $jsonPage1 . ';
    		');

		$params = explode('/', $this->getRequest()->getParam(3));
		if (count($params)>1) {
			for ($i=1;$i<count($params);$i+=2) {
				$this->params[$params[$i]] = $params[$i+1];
			}
		}
	}
	
	
	public function _getPager($count, $page_size)
	{
		$pages = ceil($count / $page_size );
		
		//$html = '<li class="disablepage">上一页 </li>' . "";
		$html = '';
		$t1 = '';
		for($i=1; $i<=$pages; $i++) {
			$class = $i == 1 ? 'currentpage' : 0;
			if ( 1 == $i) {
				$class = 'currentpage';
				$a = "$i";
			} else {
				$class = '';
				$a = "<a href=\"#\" onClick=\"return Better_User_Pager1($i, $pages)\">$i</a>";
			}
			
			$t = "<li class=\"$class\">$a</li>";
			if ($i > 5 ) {
				if (!$t1) {
					$t1 = "<li>... </li>";
					$html .= $t1;
				}
			} else {
				$html .= $t;
			}
		}
		
		
		if ($pages >= 6 ) {
			$html .= "<a href=\"#\" onClick=\"return Better_User_Pager1($pages, $pages)\">$pages</a>";
		}
		//$html .= "<li class=\"nextpage\"><a href=\"#\" >下一页</a></li>";
		
		$html = "<div class=\"pagination\" id=\"pagination\"><ul>$html</ul></div>";
		
		return $html;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Zend_Controller_Action#__call($methodName, $args)
	 */
	public function __call($method, $params)
	{
		$this->indexAction();
		$this->render('index');
	}		
	
	/**
	 * 用户首页
	 *
	 * @return null
	 */
	public function indexAction()
	{
		$username = trim( $this->getRequest()->getParam('username', '' ) );

		if (!$username) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/'.$this->dispUserInfo['username']);
			exit(0);
		}
		
		if ($this->uid>0 && $this->dispUserInfo['uid']!=$this->uid && !Better_Registry::get('sess')->admin_uid) {
			Better_User_Visit::getInstance($this->dispUserInfo['uid'])->add($this->uid);
		}

		$this->view->poi_favorites = $this->uid ? $this->user->poiFavorites()->getFavorites() : array();		
		
		if ($this->uid != $this->dispUserInfo['uid']) {
			$this->view->you = $this->dispUserInfo['nickname'];
			$this->view->isyou = false;
			$this->view->dispUserInfo = $this->dispUserInfo;
			$this->view->dispUser = $this->dispUser;
		}
		
		if ($this->uid && $this->dispUserInfo['uid']==$this->uid) {
			$this->view->friq_count = $this->user->notification()->friendRequest()->count(array(
				'type' => 'friend_request',
				'act_result' => 0
				));
				
			$this->view->floq_count = $this->user->notification()->followRequest()->count(array(
				'type' => 'follow_request',
				'act_result' => 0
				));
			/**
			 * 20110907 sunhy: fix bug 0001318 -- 网站收到的未读私信自动变成已读
			 * 删除了main rev #42 line 221~324: direct_message/friend_request/follow_request的读取和js缓存
			 */ 
		} else {
			$sJs = '';
			$this->view->friq_count = 0;
			$this->view->floq_count = 0;
		}

		$this->userRightBar();
		//判断是否为好友
		$this->view->isfriend = false;
		foreach($this->view->friends as $friend){
			if($friend['uid'] == $this->uid){
				$this->view->isfriend =true;
				break;
			}
		}
		//判断该好友的动态是否在首页显示
		$homeShow = $this->user->friends()->getHomeShow($this->dispUserInfo['uid']);
		if($homeShow){
			$this->view->homeshow = 'true';//显示文字为提示用户隐藏在首页显示状态
		}else{
			$this->view->homeshow = 'false';//显示文字为提示用户显示在首页显示状态
		}
		$spec = 0;
		if ($this->config->sys_spec && $this->dispUserInfo['uid']==BETTER_SYS_UID && $this->uid!=BETTER_SYS_UID) {
			$spec = 1;
		}
		
    	$this->view->headScript()->prependScript('
    		var Better_Kai_Spec = ' . $spec . ';' .
    		$sJs . '
    		var needRef_msg = false;
    		var needRef_friend_request = false;
    		var needRef_follow_request = false;
    		'
    		);		
    	$this->view->kai_spec = $spec;
		$this->view->needCheckinJs = false;
	}

	/**
	 * 同indexAction
	 *
	 * @return null
	 */
	public function showAction()
	{
		$this->index();
	}

}
