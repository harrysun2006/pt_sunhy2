<?php
/**
 * 用户关系
 *
 * @package Controllers
 */
class RelationController extends Better_Controller_Front 
{
	protected $dispUser = null;
	protected $dispUserInfo = array();
	protected $params = array();

	public function init()
	{		parent::init();
		$this->commonMeta();
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/relation.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	$userInfo = $this->user->getUserInfo();
    	$uid = trim($this->getRequest()->getParam('uid', ''));
		$uid = $uid? $uid: $this->uid;

    	if ($uid) {
    		$this->dispUid = $uid;
    		$this->dispUser = Better_User::getInstance($uid);
    		$this->dispUserInfo = $this->dispUser->getUser();
    		if ($this->dispUserInfo['uid']<=0) {
    			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    		} 

    	}else{
    		throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    	} 

    	//账号状态
    	$user_state = $this->dispUserInfo['state'];
    	if($user_state=='banned'){
			 $this->_helper->getHelper('Redirector')->gotoSimple('accountbanned', 'help');
			 exit();   		
    	}
    	
    	$this->view->userInfo = $this->dispUserInfo;

    	if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		');
    	} else {
    		$this->view->you = $this->dispUserInfo['nickname'];
			$this->view->isyou = false;
			$this->view->dispUserInfo = $this->dispUserInfo;
			
			$this->view->headScript()->prependScript('
			var dispUser = new BetterUser("'.$this->dispUserInfo['uid'].'", "'.$this->dispUserInfo['username'].'", "'.addslashes($this->dispUserInfo['nickname']).'", "", "");
			dispUser.priv_blog = '.intval($this->dispUserInfo['priv_blog']).';
			dispUser.priv = "'.$this->dispUserInfo['priv'].'";
			dispUser.avatar_small = "'.$this->dispUserInfo['avatar_small'].'";
			');
    	}    	   	
		$this->userRightBar();

		$spec = 0;
		if ($this->config->sys_spec && $this->dispUserInfo['uid']==BETTER_SYS_UID && $this->uid!=BETTER_SYS_UID) {
			$spec = 1;
		}

    	$this->view->headScript()->prependScript('
    		var Better_Kai_Spec = ' . $spec . ';'
    	);			
    	$this->view->kai_spec = $spec;
		$this->view->needCheckinJs = false;
	}
	
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
		//page one
		/*$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;
		
		$userObj = Better_User::getInstance($this->dispUid);
		
		$result = $userObj->friends()->all(1, BETTER_PAGE_SIZE);

		$_output['rows'] = Better_Output::filterUsers($result['rows']);
		$_output['pages'] = &$result['pages'];
		$_output['count'] = $result['total'];
		$_output['page'] = 1;		

		$jsonPage1 = json_encode($_output); 

		$this->view->headScript()->prependScript('
    		var personpage = true;
    		var needRef = false;
    		var _page_1 = ' . $jsonPage1 . ';
    	');*/
	}
		/**
	 * 好友
	 */
	public function friendsAction(){
		$this->indexAction();		$this->view->headScript()->prependScript('			$("#relation_friends").addClass("on");    	');
	}
	
	/**
	 * 关注
	 */
	public function followingsAction(){		exit(0);		$this->view->headScript()->prependScript('			$("#relation_followings").addClass("on");    	');
	}
	
	
	/**
	 * 粉丝
	 */
	public function followersAction(){		exit(0);		$this->view->headScript()->prependScript('			$("#relation_followers").addClass("on");    	');
	}
	
	
	/**
	 * 掌门
	 */
	public function mayorsAction(){
	}
	
	/**
	 * 黑名单
	 */
	public function blocksAction(){		$this->view->headScript()->prependScript('			$("#relation_blocks").addClass("on");    	');
	}

}
