<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_UserController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/user.js?ver='.BETTER_VER_CODE);
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/usermanage.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		$result=Better_Admin_Usermanage::getUsers($params);
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function rpAction()
	{
		$params = $this->getRequest()->getParams();
		
		
		
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$result=Better_Admin_User::getUserRp($params);

	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		
		$this->view->count = $result['count'];
	}
	
	public function karmaAction()
	{
		$params = $this->getRequest()->getParams();
		
		
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$result=Better_Admin_User::getUserKarma($params);
		
	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		
		$this->view->count = $result['count'];
	}
	
	
	public function editrpAction()
	{
		$params = $this->getRequest()->getParams();
		$this->view->uid = $params['uid'];		
	}
	
	public function updaterpAction()
	{
		$params = $this->getRequest()->getParams();
		if($params['active']==1){
			$uid = $params['uid'];
			$note = $params['note'];
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			$rp = $params['rp'];
			$user->rp()->update(array(
					'rp' => $rp,
					'category' => 'Admin',
					'note' => $note,
					'co_uid' => Better_Registry::get('sess')->admin_uid,
					'active' => 1,
					)); 
			$lang = Better_Language::loadIt($userInfo['language'] ? $userInfo['language'] : 'zh-cn');
			$message =$lang->dmessage->admin_karma_change;		
			$message = str_replace("{REASON},",$note,$message);
			$message = str_replace("{KARMA}",$rp,$message);				
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($message, $uid);
		}
		$this->_redirect('/admin/user/rp?uid='.$uid);	
	}
	/**
	 * 删除头像
	 */
	public function delavatarAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			$result = Better_Admin_User::delAvatars($uids);
		}
		$this->sendAjaxResult($result);
	}
	/**
	 * 当该用户成为掌门是推荐到首页显示
	 */
	public function recommendedAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			$result = Better_Admin_User::recommendedAvatars($uids);
		}
		$this->sendAjaxResult($result);
	}
	
	/**
	 * 取消对选择用户的推荐
	 */
	public function unrecommendedAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			$result = Better_Admin_User::unrecommendedAvatars($uids);
		}
		$this->sendAjaxResult($result);
	}
	
	public function resetnameAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			Better_Admin_User::resetName($uids) && $result=1;
		}
		$this->sendAjaxResult($result);
	}
	
	
	public function resetnicknameAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			Better_Admin_User::resetNickName($uids) && $result=1;
		}
		$this->sendAjaxResult($result);
	}
	
	
	public function resetselfintroAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['bids'];
		
		if(is_array($uids) && count($uids)>0){
			Better_Admin_User::resetSelfIntro($uids) && $result=1;
		}
		$this->sendAjaxResult($result);
	}
	
	public function sendmsgAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uids = $params['uids'];
		$content = trim($params['content']);
						
		if (empty($uids)) {
			$result = '收信人不能为空';
		} else if (empty($content)) {
			$result = '私信内容不能为空';
		} else {
			if(is_array($uids)){
				foreach($uids as $uid){
					if ($uid) {
						$msg_id = Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $uid);
					}
				}
			}else{
				$msg_id = Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $uids);
			}
			
			$result = 1;	
		}
		$this->sendAjaxResult($result);		
	}
	
	/**
	 * 查看一个用户的基本信息
	 */
	public function viewuserAction()
	{
		$uid = $this->getRequest()->getParam('uid',0);	
		if($uid){
			$user = Better_DAO_User::getInstance()->getByUid($uid);
			$user['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $user);			;
			$this->view->user = $user;			
			$sites = array( 'sina.com'=> '新浪微博', 
	    	    					    'qq.com'=> '腾讯微博', 
	    	    					    'msn.com'=> 'MSN',
	    	    					    'kaixin001.com'=> '开心网',
	    	    					    'renren.com'=> '人人网',
	    	    						'douban.com'=> '豆瓣',
	    	    						'fanfou.com'=> '饭否',
	    	    						'facebook.com'=> 'Facebook',
	    	    						'twitter.com'=> 'Twitter',
	    	    						'4sq.com'=> 'Foursquare',
	    	    						'sohu.com'=> '搜狐微博',
	    	    						'163.com'=> '网易微博',
	    	    						'139.com'=> '139说客',
	    	    						'digu.com'=> '嘀咕',
	    	    						'follow5.com'=> 'Follow5',
	    	    						'zuosa.com'=> '做啥'
	    	    		);
			$keys =array_keys(Better_User_Syncsites::getInstance($uid)->getSites());
			$syncSites=array();
			foreach($keys as $key){
				if(array_key_exists($key,$sites)){
					$syncSites[$key] =  $sites[$key];
				}
			}
			$this->view->syncSites =	$syncSites;
		}
	}
}