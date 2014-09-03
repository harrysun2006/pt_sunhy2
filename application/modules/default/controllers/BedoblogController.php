<?php
class BedoblogController extends Better_Controller_Front
{
	protected $dispUser = null;
	protected $dispUserInfo = array();
	
	public function init()
	{
		date_default_timezone_set("Asia/Shanghai");
		parent::init();
		$this->commonMeta();
		
		$itemid = $this->getRequest()->getParam('itemid', '');
		$page = $this->getRequest()->getParam('page', '0');
		if (!$itemid) {
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
			exit(0);
		}
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/user.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
   			
		
		$this->needLogin();
		
		$data = Better_DAO_Bedo::getInstance()->getBlogInfo($itemid);
		
		$uid = $data['kaiUid'];
		
		if (!$uid) {
			$uid = $this->uid;
		}
		
		$this->dispUser = Better_User::getInstance($uid);
    	$this->dispUserInfo = $this->dispUser->getUser();
    	
    	$userInfo = $this->user->getUserInfo();
    		
    	if ($this->dispUserInfo['uid']<=0) {
    		throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    	}
    	
    	if ($data) {
	    	$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$sql = "SELECT * FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."' and dateline='{$data['postdate']}'";
			$rs = Better_DAO_BASE::squery($sql, $rdb);
			if (!$rs->fetch()) {
				$data = null;
//				throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
			}
    	}
    	if ($data == null) {
    		$this->view->tipInfo = $this->lang->bedoblog->emtpyBlog;
    	}
    	
		
    	$showUid = $uid;
    	$photoData = Better_DAO_Bedo::getInstance()->getBlogPhoto($itemid);
		
    	$user_state = $this->dispUserInfo['state'];
    	if($user_state=='banned'){
			 $this->_helper->getHelper('Redirector')->gotoSimple('accountbanned', 'help');
			 exit();   		
    	}
    	
    	$this->view->userInfo = $this->dispUserInfo;
    	
//    	echo $this->dispUserInfo['uid']. '<br />' . $userInfo['uid'];exit;
    	
		if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		var inOther_trace = false;
    		');
    	} else {
    		if ($data && $data['ifhide'] == 1) {
    			$this->view->tipInfo = $this->lang->bedoblog->privateBlog;
    		}
			$this->view->headScript()->prependScript('
			var dispUser = new BetterUser("'.$this->dispUserInfo['uid'].'", "'.$this->dispUserInfo['username'].'", "'.addslashes($this->dispUserInfo['nickname']).'", "", "");
			dispUser.priv_blog = '.intval($this->dispUserInfo['priv_blog']).';
			dispUser.priv = "'.$this->dispUserInfo['priv'].'";
			dispUser.avatar_small = "'.$this->dispUserInfo['avatar_small'].'";
			dispUser.friend_sent_msg = "'.$this->dispUserInfo['friend_sent_msg'].'";
			var inOther_trace = true;
			');
    	}
    	
    	if ($data) {
			$this->view->subject = $data['subject'];
			$this->view->data = date('Y-m-d H:i', $data['postdate']);
			$this->view->pfrom = $data['pfrom'] == 'pc' ? $this->lang->bedoblog->fromPC : $this->lang->bedoblog->fromMobile;
			$this->view->content = $data['content'];
			$this->view->typeName = $data['name'] == '' && $data['dirid'] == 0 ? $this->lang->bedoblog->notype : $data['name'];
			$this->view->scoreaverage = $data['scoreaverage'];
			$this->view->scores = $data['scores'];
			$this->view->replies = $data['replies'];
			$this->view->hits = $data['hits'];
			$this->view->itemid = $itemid;
			$this->view->photos = $photoData;
			
			$page = intval($page);
			$pageSize = 10;
			$pageCnt = ceil($data['replies'] / $pageSize);
			$page = $page < 1 ? 1 : ($page > $pageCnt ? $pageCnt : $page);
			
			$this->view->page = $page;
			$this->view->pageCnt = $pageCnt;
			if ($data['replies'] > 0) {
				$comments = Better_DAO_Bedo::getInstance()->getBlogComment($itemid, $page, $pageSize);
				$this->view->comments = $comments;
			}
    	} else {
    		$this->view->emptyData = true;
    	}
		
	}
	
	
	public function indexAction()
	{
		$itemid = $this->getRequest()->getParam('itemid', '');
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/bedoblog.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
			'defer' => 'defer',
			'charset=' => 'utf-8'
   		));
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
				
	    //page one
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;
		
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getReceiveds(array(
			'page' => 1,
			'count' => BETTER_PAGE_SIZE
			));
	
		$_output['rows'] = array();
		
		$rds = array();
		foreach($results['rows'] as $row){
			if ($row['msg_id'] && $row['uid']==$this->uid) {
				if($row['readed']==0){
					if (Better_User_DirectMessage::getInstance($this->uid)->readed($row['msg_id'])) {
						$row['readed'] = 1;
						$rds[] = $row['msg_id'];
					} else {
						$_output['error'] = $this->lang->error->system;
					}
				}
				
				$_output['rows'][] = array_merge((array)$row['userInfo'], $row);
			} else {
				$_output['error'] = $this->lang->error->rights;
			}
		}
		
		if (count($rds)>0 && $this->config->dm_ppns && BETTER_PPNS_ENABLED) {
			//	已读私信推送给客户端
			$this->user->notification()->all()->pushReadStateToPpns($rds);
		}
//		
//		$this->user->cache()->set('direct_message_count', 0);
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['count'] = $results['count'];
		$_output['page'] = 1;
		$_output['pages'] = Better_Functions::calPages($results['count']);
	
		$msg_jsonPage1 = json_encode($_output);   	
		$sJs = "var _msg_page1 = $msg_jsonPage1;";
		
		
		//friend_request 
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;	

		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->friendRequest()->getReceiveds(array(
			'type' => 'friend_request',
			'page' => 1,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$_output[$k] = $v;
		}
		
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['pages'] = Better_Functions::calPages($rows['count'], $count);
		$_output['page'] = 1;
		unset($rows);
		
		$friend_request_jsonPage1 = json_encode($_output); 	
		$sJs .= "var _friend_request_page1 = $friend_request_jsonPage1;";
		
		//follow_request 
		$_output['rows'] = array();
		$_output['pages'] = 0;
		$_output['count'] = 0;
		$_output['page'] = 1;	

		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'follow_request',
			'page' => 1,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		foreach ($rows as $k=>$v) {
			$_output[$k] = $v;
		}
		
		$_output['rows'] = Better_Output::filterMessages($_output['rows']);
		$_output['pages'] = Better_Functions::calPages($rows['count'], $count);
		$_output['page'] = 1;
		unset($rows);

		$follow_request_jsonPage1 = json_encode($_output); 	
		$sJs .= "var _follow_request_page1 = $follow_request_jsonPage1;";		
		
	    //end				
				
				
				
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
	
	public function __call($method, $params)
	{
		$this->indexAction();
		$this->render('index');
	}
}