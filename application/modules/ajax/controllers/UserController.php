<?php

/**
 * 用户相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_UserController extends Better_Controller_Ajax
{
	
	public function init()
	{
		parent::init();	
	}		
	
	/**
	 * 刷新用户首页的通知
	 * 
	 * @return
	 */
	public function refreshnotificationAction()
	{
		
	}
	
	/**
	 * 取某人所有的贴士
	 * 
	 * @return 
	 */
	public function tipsAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', $this->uid);
		
		$result = Better_User::getInstance($uid)->blog()->getAllTips($this->page, BETTER_PAGE_SIZE);

		$this->output['count'] = $result['count'];
		$this->output['pages'] = $result['pages'];
		$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
		$this->output['rts'] = Better_Output::filterBlogs($result['rts']);

		$this->output();
	}
	
	/**
	 * poi收藏
	 * 
	 * @return
	 */
	public function poifavoritesAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		if ($uid) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			if ($userInfo['uid']) {
				$results = $user->poiFavorites()->all($this->page, BETTER_PAGE_SIZE);
				
				$this->output['rows'] = Better_Output::filterPoiRows($results['rows']);
				$this->output['page'] = $this->page;
				$this->output['pages'] = Better_Functions::calPages($results['count'], BETTER_PAGE_SIZE);
				$this->output['count'] = $results['count'];
			}
		}
		
		$this->output();
	}
	
	/**
	 * 忽略请求
	 * 
	 * @return
	 */
	public function discardrequestAction()
	{
		$msgId = (int)$this->getRequest()->getParam('msg_id', 0);
		$requestType = trim($this->getRequest()->getParam('request_type', 'all'));
		$type = '';
		$tmp = explode('_', $requestType);
		foreach ($tmp as $str) {
			$type .= ucfirst(strtolower($str));
		}
		
		$className = 'Better_User_Notification_'.$type;
		if (class_exists($className)) {
			$nt = call_user_func($className.'::getInstance', $this->uid);
			$nt->discard($msgId);
		}
	}
	
	/**
	 * 用户好友列表
	 * 
	 * @return
	 */
	public function friendsAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		$pageSize = $this->getRequest()->getParam('pagesize',BETTER_PAGE_SIZE);
		$this->page = $this->getRequest()->getParam('page')?$this->getRequest()->getParam('page'):$this->page;
		/**
		 * 按条件搜索用户
		 */
		$keywords =  $this->getRequest()->getParam('keywords')?$this->getRequest()->getParam('keywords'):'';
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$user = Better_User::getInstance($userInfo['uid']);
			
			if($keywords){
				$result = $user->friends()->allbykeywords($this->page, $pageSize,$keywords);
			}else{//搜索所有好友
				$result = $user->friends()->all($this->page, $pageSize);
			}
			$this->output['rows'] = &Better_Output::filterUsers($result['rows']);

			$this->output['pages'] =$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;		
		
		}		
		$this->output();
	}
	
	/**
	 * 用户宝物列表
	 * 
	 * @return
	 */
	/*public function treasuresAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
		
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$user = Better_User::getInstance($userInfo['uid']);
			$result = $user->treasure()->getMyTreasures();

			$this->output['rows'] = &$result;
			$this->output['pages'] = count($result) ? 1 : 0;
			$this->output['count'] = count($result);
			$this->output['page'] = 1;		
		}			
		
		$this->output();
	}*/
	
	/**
	 * 用户勋章列表
	 * 
	 * @return
	 */
	public function badgesAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
		
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$user = Better_User::getInstance($userInfo['uid']);
			$result = $user->badge()->getMyBadges();
	
			$this->output['rows'] = &$result;
			$this->output['pages'] = count($result)>0 ? 1 : 0;
			$this->output['count'] = count($result);
			$this->output['page'] = count($result)>0 ? 1 : 0;		
		}			

		$this->output();
	}
	
	/**
	 * 掌门历史
	 * 
	 * @return
	 */
	public function majorhistoryAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = array();
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$user = Better_User::getInstance($userInfo['uid']);
			
			$flag = Better_User::getInstance($this->uid)->canViewDoing($userInfo['uid']);
			if ($flag) {
				$result = $user->major()->getAll($this->page);
				$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
				$this->output['pages'] = &$result['pages'];
				$this->output['count'] = count($result['rows']);
				$this->output['page'] = $this->page;						
			}
		}		
	
		$this->output();
	}
	
	/**
	 * 报道轨迹
	 * 
	 * @return 
	 */
	public function checkinhistoryAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = array();
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		$this->output['rts'] = array();
		
		if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$user = Better_User::getInstance($userInfo['uid']);
			
			$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
			$flag = $userObj->canViewDoing($userInfo['uid']);
		
			if ($flag) {
				$result = $userObj->status()->getSomebody(array(
					'page' => $this->page,
					'type' => 'checkin',
					'page_size' => BETTER_PAGE_SIZE,
					'uid' => $userInfo['uid'],
					'ignore_block' => true
					));

				$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
				$this->output['pages'] = &$result['pages'];
				$this->output['count'] = $result['count'];
				$this->output['page'] = $this->page;		
				$this->output['rts'] = &$result['rts'];					
			}
		}

		$this->output();
	}
	
	/**
	 * 用户动态
	 * 
	 * @return 
	 */
	
	public function userdoingAction()
	{
		$uid = trim($this->getRequest()->getParam('uid', $this->uid));
		
		$this->output['rows'] = array();
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		$flag = $userObj->canViewDoing($uid);
		$without_me = ($this->uid==$uid) ? false : true;
		$result = $userObj->status()->getSomebody(array(
			'page' => $this->page,
			'type' => array('normal', 'checkin', 'tips'),
			'page_size' => BETTER_PAGE_SIZE,
			'uid' => $uid,
			'without_me' => $without_me,
			'ignore_block' => true
			));

		$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
		$this->output['pages'] = &$result['pages'];
		$this->output['count'] = $result['count'];
		$this->output['cnt'] = $result['cnt'];
		$this->output['page'] = $this->page;		
		$this->output['rts'] = &Better_Output::filterBlogs($result['rts']);

		$this->output();
	}
	
	/**
	 * 用户想去的地方---取得的是blog记录
	 */
	public function usertodoAction()
	{
		$uid = trim($this->getRequest()->getParam('uid', $this->uid));
		
		$this->output['rows'] = array();
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		
		$without_me = ($this->uid==$uid) ? false : true;
		$result = $userObj->status()->getSomeTodo(array(
			'page' => $this->page,
			'type' => array('todo'),
			'page_size' => BETTER_PAGE_SIZE,
			'uid' => $uid
			));
		$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
		
		$this->output['pages'] = &$result['pages'];
		$this->output['count'] = $result['count'];
		$this->output['page'] = $this->page;		
		$this->output['rts'] = &Better_Output::filterBlogs($result['rts']);

		$this->output();
	}
	
	/**
	 * 关注行踪
	 * 
	 * @desc 暂时取消该功能
	 * @return 
	 *//*
	public function geofollowAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		$result = 0;
		
		if ($uid>0 && $uid!=$this->uid) {
			$tmp = $this->user->follow()->request($uid, true);
			$result = $tmp['result'];
			$this->output['codes'] = &$tmp['codes'];
		}
		
		$this->output['result'] = $result;
	}*/

	/**
	 * 好友请求列表
	 * 
	 * @return
	 */
	public function friendsrequestsAction()
	{
		$count = (int)$this->getRequest()->getParam('count', 20);
		$users = $this->user->friends()->allRequests($this->page, $count, true);
		
		$this->output['users'] = &$users;
		$this->output();
	}
	
	/**
	 * 拒绝好友请求
	 * 
	 * @return
	 */
	public function rejectfriendAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		$result = 0;
		
		if ($uid>0 && $uid!=$this->uid) {
			$return = $this->user->friends()->reject($uid);
			$result = $return;
		}		
		
		$this->output['result'] = $result;
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 拒绝所有好友请求
	 */
	public function rejectfriendsAction()
	{
		$this->output['result'] = 0 ;
		$count = 0;
		$rows = $this->user->friends()->allRequestsToMe($this->page, 20, true);		
		if($rows['count'] && $rows['count']>0){
			foreach($rows['rows'] as $row){
				$uid = $row['uid'];
				
				if ($uid>0 && $this->uid>0) {
					$return = $this->user->friends()->reject($uid);
				}
			}
			$this->output['result']  = 1 ;
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_friend_request;
		}
		
		$this->output();
	}
	
	/**
	 * 删除好友
	 * 
	 * @return
	 */
	public function removefriendAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		$result = 0;
		
		if ($uid>0 && $uid!=$this->uid) {
			$result = $this->user->friends()->delete($uid);
		}

		$this->output['result'] = $result;
		$this->output();
	}
	
	/**
	 * 发起加好友请求
	 * 
	 * @return
	 */
	public function friendrequestAction()
	{
		$this->output['uid'] = $this->uid;
		
		$uid = (int) $this->getRequest()->getParam('uid', 0);
		$result = 0;
		
		if ($uid>0 && $this->uid>0) {
			$return = $this->user->friends()->request($uid);
			$result = $return['result'];
			$this->output['codes'] = $return['codes'];
			$this->output['double_request'] = $return['double_request'];
		}
		
		$this->output['result'] = $result;
		$this->output();
	}
	
	/**
	 * 全部同意好友请求
	 */
	public function friendrequestsAction()
	{
		$this->output['result'] = 0 ;
		$count = 0;
		$rows = $this->user->friends()->allRequestsToMe($this->page, 20, true);
		//Better_Log::getInstance()->logInfo(serialize($rows),'listfriendsrequest');
		/*
		$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'friend_request',
			'page' => $this->page,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		*/
		if($rows['count'] && $rows['count']>0){
			$user_list = array();
			foreach($rows['rows'] as $row){
				$user_list[] = $row['uid'];				
				/*
				$uid = $row['from_uid'];
				if ($uid>0 && $this->uid>0) {
					$return = $this->user->friends()->request($uid);
				}
				*/
				
			}
			//Better_Log::getInstance()->logInfo(serialize($user_list),'listfriendsrequest');
			$return = $this->user->friends()->requests($user_list);
			//Better_Log::getInstance()->logInfo(serialize($return),'friendsrequest');
			$this->output['result']  = 1 ;
			$this->output['friends']  = $return['resultnum'];
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_friend_request;
		}
		$this->output();
	}
	
	/**
	 * 阻止某人
	 * 
	 * @return
	 */
	public function blockAction()
	{
		$this->output['uid'] = $this->uid;
		
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		$from = $this->getRequest()->getParam('from', '');
		$result = 0;

		if ($uid>0 && $this->uid>0) {
			$result = $this->user->block()->add($uid, $from);
		}
		
		$this->output['blocked_uid'] = $uid;
		$this->output['result'] = $result;	
		
		$this->processRightbar();
		$this->output();	
	}
	
	/**
	 * 所有阻止的用户
	 * 
	 * @return
	 */
	public function blocksAction()
	{
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		$uid = (int)$this->getRequest()->getParam('uid', $this->uid);
		
		if ($uid) {
			$result = Better_User::getInstance($uid)->block()->getBlocksWithDetail($this->page);
			
			$this->output['rows'] = &Better_Output::filterUsers($result['rows']);
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;		
		}		
		$this->output();
	}
	
	/**
	 * 取消阻止某人
	 * 
	 * @return
	 */
	public function unblockAction()
	{
		$uid = $this->getRequest()->getParam('uid', 0);
		$uid = (int)$uid;
		$result = 0;
		
		if ($uid>0) {
			$result = Better_User_Block::getInstance($this->uid)->delete($uid);
		}
		
		$this->output['unblocked_uid'] = $uid;
		$this->output['result'] = $result;		
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 绑定IM帐号
	 * 
	 * @return
	 */
	public function bindimAction()
	{
		$protocol = $this->getRequest()->getParam('protocol', '');
		$im = $this->getRequest()->getParam('im', '');
		$result = 'failed';
		
		if (in_array($protocol, Better_User_Bind_Im::$allowedProtocols) && Better_Functions::checkEmail($im)) {
			$bot = Better_User_Bind_Im::getInstance($this->uid)->request($protocol, $im);

			if (Better_Functions::checkEmail($bot)) {
				$result = 'success';
				$this->output['robot'] = $bot;
			} else if ($bot=='ROBOT_UNAVAILABLE') {
				$result = 'service_unavailable';
			} else if ($bot=='HAS_BINDED') {
				$result = 'exists';
			}
		}
		$this->output['result'] = $result;		

		$this->output();
	}
	
	/**
	 * 绑定手机号
	 * 
	 * @return
	 */
	public function bindcellAction()
	{
		$cell = $this->getRequest()->getParam('cell', '');
		$user = Better_Registry::get('user');
		
		$result = 'failed';
		if (preg_match(Better_User::CELL_PAT, $cell)) {
			$cell = '86'.$cell;
		}
		Better_User_Bind_Cell::getInstance($this->uid)->request($cell)==1 ? $result = 'success' : $result = 'exists';
		
		$this->output['result'] = $result;		
		
		$this->output();
	}
	
	/**
	 * 搜索用户
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$keyword = trim($this->getRequest()->getParam('keyword', ''));

		$result = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'keyword' => $keyword,
			))->search();
		$this->output['count'] = $result['count'];
		$this->output['rows'] = &Better_Output::filterUsers($result['rows']);		
		$this->output['pages'] = Better_Functions::calPages($result['total'], BETTER_PAGE_SIZE);
		
		$this->output();
	}
	
	/**
	 * 根据qbs搜索用户
	 * 
	 * @return
	 */
	public function searchqbsAction()
	{
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		$w = (int)$this->getRequest()->getParam('w', $this->qbsDefaultW);
		$h = (int)$this->getRequest()->getParam('h', $this->qbsDefaultH);

		$result = Better_Search::factory(array(
			'what' => 'user',
			'lon' => $lon,
			'lat' => $lat,
			'w' => $w,
			'h' => $h,
			'page' => $this->page,
			'method' => 'qbs'
			))->search();
		
		$this->output = array_merge($this->output, $result);
		$this->output['pages'] = Better_Functions::calPages($result['count']);
		$this->output['page'] = $this->page;		
		
		$this->output();
	}
	
	/**
	 * 用户关注的人
	 * 
	 * @return
	 */
	public function followingAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		/*if ($nickname) {
		
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			
			$result = Better_User_Follow::getInstance($userInfo['uid'])->getFollowingsWithDetail($this->page);

			$this->output['rows'] = &Better_Output::filterUsers($result['rows']);
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;		
		}*/
		
		$this->output();
	}
	
	/**
	 * 添加关注的人好友
	 */
	public function addfollowingAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
		
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			
			$result = Better_User_Follow::getInstance($userInfo['uid'])->getFollowingsWithEach($this->page);
			
			$this->output['rows'] = &Better_Output::filterUsers($result['rows']);
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = $result['count'];
			$this->output['page'] = $this->page;		
		}
		
		$this->output();
	}
	
	/**
	 * 我关注了哪些人的行踪
	 * 
	 * @desc 暂时取消该功能
	 * @return
	 *//*
	public function geofollowingAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			
			$result = Better_User_Follow::getInstance($userInfo['uid'])->getGeoFollowingsWithDetail($this->page);
	
			$this->output['rows'] = &$result['rows'];
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;		
		}
	}*/
		
	
	/**
	 * 关注请求
	 * 
	 * @return
	 */
	public function followrequestAction()
	{
		$pageSize = $this->getRequest()->getParam('pageSize', BETTER_PAGE_SIZE);
		
		$result = Better_User_Follow::getInstance($this->uid)->getRequest($this->page, $pageSize, true);
		
		$this->output['rows'] = &$result['rows'];
		$this->output['count'] = $result['count'];
		$this->output['page'] = $this->page; 
		$this->output['pages'] = Better_Functions::calPages($result['count'], $pageSize);		
		
		$this->output();
	}
	
	/**
	 * 所有粉丝
	 * 
	 * @return
	 */
	public function followerAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;

		/*if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$result = $user->follow()->getFollowersWithDetail($this->page);
			
			if($this->uid && $userInfo['uid']==$this->uid){
				$this->user->updateUser(array('last_my_followers'=>time()));
			}
			
			$this->output['rows'] = &Better_Output::filterUsers($result['rows']);
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;		
		}*/
		
		$this->output();
	}
	
	/**
	 * 所有关注行踪的粉丝
	 * 
	 * @desc 暂时取消该功能
	 * @return
	 *//*
	public function geofollowerAction()
	{
		$nickname = trim($this->getRequest()->getParam('nickname', ''));
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		if ($nickname) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($nickname);
			$result = $user->follow()->getGeoFollowersWithDetail($this->page);
			
			$this->output['rows'] = &$result['rows'];
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;				
		}
	}*/
	
	/**
	 * 请求关注某人
	 * 
	 * @return
	 */
	public function followAction()
	{

		$this->output['uid'] = $this->uid;
		
		$uid = (int)$this->getRequest()->getParam('uid', 0);

		if($this->uid>0){
			$this->output = $this->user->follow()->request($uid);
		}
		$this->output['followed_uid'] = $uid;		
		
		$this->output();
	}
	
	/**
	 * 确认他人加关注请求
	 * 
	 * @return
	 */
	public function confirmfollowAction()
	{
		$this->output['result'] = 0;
		
		$requestUid = $this->getRequest()->getParam('request_uid');
		
		$user = Better_User::getInstance($requestUid);
		$requestUserInfo = $user->getUser();

		if ($requestUserInfo['uid']) {
			$this->output['result'] = $this->user->follow()->agree($requestUid);
		}

		$this->output['request_uid'] = $requestUid;		
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 同意所有关注
	 */
	public function confirmallfollowAction()
	{
		$this->output['result'] = 0;
		
		/*$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'follow_request',
			'page' => $this->page,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		
		if($rows['count'] && $rows['count']>0){
			foreach($rows['rows'] as $row){
				$uid = $row['from_uid'];
				if ($uid>0 && $this->uid>0) {
				$return = $this->user->follow()->agree($uid);
				}
			}
			$this->output['result']  = 1 ;
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_follow_request;
		}*/

		$this->output();
	}
	
	
	/**
	 * 拒绝加关注请求
	 * 
	 * @return
	 */
	public function rejectfollowAction()
	{
		$this->output['result'] = 0;
		
		/*$requestUid = (int)$this->getRequest()->getParam('request_uid');
		if ($requestUid) {
			$this->output['result'] = Better_User_Follow::getInstance($this->uid)->reject($requestUid);
			
			$this->processRightbar();
		}	*/	
		
		$this->output();
	}
	
	/**
	 * 全部拒绝关注请求
	 */
	public function rejectallfollowAction()
	{
		$this->output['result'] = 0;
		
		/*$rows = $this->user->notification()->followRequest()->getReceiveds(array(
			'type' => 'follow_request',
			'page' => $this->page,
			'count' => $count,
			'keep' => 1,
			'act_result' => 0
			));
		
		if($rows['count'] && $rows['count']>0){
			foreach($rows['rows'] as $row){
				$uid = $row['from_uid'];
				if ($uid>0 && $this->uid>0) {
					$return = Better_User_Follow::getInstance($this->uid)->reject($uid);
				}
			}
			$this->output['result']  = 1 ;
		}else{
			$this->output['error'] = $this->lang->javascript->messages->no_follow_request;
		}*/

		$this->output();
	}
	
	
	
	/**
	 * 取消关注
	 * 
	 * @return
	 */
	public function unfollowAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', 0);
		
		$this->output['result'] = Better_User_Follow::getInstance($this->uid)->delete($uid);
		$this->output['unfollowed_uid'] = $uid;
		
		$this->processRightbar();
		
		$this->output();
	}
	
	
	/**
	 * 更新最后一次查看粉丝时间
	 */
	public function updatelastfollowerAction(){
		/*if($this->uid){
			$this->user->updateUser(array('last_my_followers'=>time()));
			$this->output['result'] = 1;
		}*/
		$this->output();
	}
	
	
	/**
	 * 获得某人的行踪
	 */
	public function usertraceAction()
	{
		$uid = trim($this->getRequest()->getParam('uid', ''));
		//$uid = trim($_POST['uid']);
		$days = trim($this->getRequest()->getParam('days', 0));
		
		$this->output['rows'] = array();
		$this->output['user'] = array();
		$this->output['clusters'] = array();
		
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		if ($uid) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			if (!$userInfo['uid']) $this->output();

			$result = $userObj->checkin()->someDaysCheckinedPois(array(
				'uid' => $uid,
				'days'=> $days,
				'reg_time'=> $userInfo['regtime']
				));
			
			$clusters = Better_Trace::clusterMarkers($result['rows']);

			$this->output['rows'] = &$result['rows'];
			$this->output['user'] = &$userInfo;
			$this->output['clusters'] = &$clusters;
		}
		
		$this->output();
	}
	
	
	/**
	 * 设置在不在首页显示
	 */
	public function homeshowAction(){
		$this->output['result'] = 0;
		
		$show = $this->getRequest()->getParam('show', '');
		$fuid = $this->getRequest()->getParam('fuid', '');
		
		$show = $show==='true' ? true: false;
		
		if($fuid!=''){
			$this->output['result'] = $this->user->friends()->setHomeShow($fuid, $show);
		}
		
		$this->output();
	}
	
	
	//设置是否同步勋章
	public function syncbadgeAction(){
		$this->output['result'] = 0;
		$protocol = $this->getRequest()->getParam('protocol', '');
		$sync = $this->getRequest()->getParam('sync', '');
		
		if($protocol!=='' && $sync!==''){
			$flag = Better_DAO_ThirdBinding::getInstance($this->uid)->setSyncBadge($protocol, $sync);
			$flag && $this->output['result'] = 1;
		}
		
		$this->output();
	}
	
}
