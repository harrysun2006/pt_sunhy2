<?php
/**
 * 一些第三方服务
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_ServiceController extends Better_Controller_Ajax
{
	protected $user = null;
	
	public function init()
	{
		parent::init();	
		$this->user = Better_Registry::get('user');
	}		
	
	public function unsyncAction()
	{
		$key = '';
		$protocol = $this->getRequest()->getParam('protocol', '');
		$username = $this->getRequest()->getParam('username');
		$result = 0;
		
		switch($protocol) {
			case 'foursquare':
			case '4sq':
				$key = '4sq.com';
				break;			
			case '9911':
				$key = '9911.com';
				break;
			case 'zuosa':
				$key = 'zuosa.com';
				break;
			case '51':
				$key = '51.com';
				break;
			case 'follow5':
				$key = 'follow5.com';
				break;

			case '139':
				$key = '139.com';
				break;				
				
			case 'kaixin':
				$key = 'kaixin.com';
				break;
			case 'kaixin001':
				$key = 'kaixin001.com';
				break;
			case 'renren':
				$key = 'renren.com';
				break;
			case 'sina':
				$key = 'sina.com';
				break;
			case 'digu':
				$key = 'digu.com';
				break;
			case 'tongxue':
				$key = 'tongxue.com';
				break;
			case 'plurk':
				$key = 'plurk.com';
				break;
			case 'douban':
				$key = 'douban.com';
				break;
			case 'twitter':
				$key = 'twitter.com';
				break;
			case 'facebook':
				$key = 'facebook.com';
				break;
			case 'foursquare':
				$key = 'foursquare.com';
				break;	
			case 'sohu':
				$key = 'sohu.com';
				break;		
			case 'msn':
				$key = 'msn.com';
				break;
			case 'fanfou':
				$key = 'fanfou.com';
				break;
			case '163':
				$key = '163.com';
				break;
			case 'qq':
				$key = 'qq.com';
				break;				
			case 'qqsns':
				$key = 'qqsns.com';
				break;				
			case 'bedo':
				$key = 'bedo.cn';
				break;
				
										
		}
		
		if ($key!='') {
			Better_User_Syncsites::getInstance($this->uid)->delete($key);
			$this->output['result'] = '1';
		}		
		
		$this->output();
	}
	
	public function syncAction()
	{
		
		$key = '';
		$protocol = $this->getRequest()->getParam('protocol', '');
		$username = $this->getRequest()->getParam('username');
		$password = $this->getRequest()->getParam('password');
		$followkai = $this->getRequest()->getParam('followkai');
		
		switch($protocol) {
			case 'foursquare':
			case '4sq':
				$key = '4sq.com';
				break;
			case '9911':
				$key = '9911.com';
				break;
			case '139':
				$key = '139.com';
				break;					
			case 'zuosa':
				$key = 'zuosa.com';
				break;
			case '51':
				$key = '51.com';
				break;
			case 'follow5':
				$key = 'follow5.com';
				break;
			case 'kaixin':
				$key = 'kaixin.com';
				break;
			case 'kaixin001':
				$key = 'kaixin001.com';
				break;
			case 'renren':
				$key = 'renren.com';
				break;
			case 'sina':
				$key = 'sina.com';
				break;
			case 'digu':
				$key = 'digu.com';
				break;
			case 'tongxue':
				$key = 'tongxue.com';
				break;
			case 'plurk':
				$key = 'plurk.com';
				break;
			case 'douban':
				$key = 'douban.com';
				break;
			case 'twitter':
				$key = 'twitter.com';
				break;
			case 'facebook':
				$key = 'facebook.com';
				break;				
			case 'foursquare':
				$key = 'foursquare.com';
				break;
			case 'sohu':
				$key = 'sohu.com';
				break;
			case 'douban':
				$key = 'douban.com';
				break;	
			case 'msn':
				$key = 'msn.com';
				break;
			case 'fanfou':
				$key = 'fanfou.com';	
				break;
			case '163':
				$key = '163.com';
				break;	
			case 'bedo':
				$key = 'bedo.cn';
exit;
				break;															
		}

		if ($key!='') {			
			if ($key == 'douban.com' || $key == 'twitter.com'  || $key == 'facebook.com') {
				ini_set('max_execution_time', 60);
				
				$service = Better_Service_PushToOtherSites::factory($key, $username, $password);	
				$tokens = $service->getToken();
				$oauth_token = $tokens['oauth_token'];
				$oauth_token_secret = $tokens['oauth_token_secret'];
				
				if ($oauth_token && $oauth_token_secret) {
					$this->user->syncsites()->add($key, $username, $password, $oauth_token, $oauth_token_secret);
					$logined = true;
				}
			} elseif ($key == '139.com') {
				$service = Better_Service_PushToOtherSites::factory($key, $username, $password);
										
			} else {
				$service = Better_Service_PushToOtherSites::factory($key, $username, $password);
				$ck = $service->checkAccount($this->user->uid, $key, $username);
				
				if ($ck) {
					$logined = $service->fakeLogin();
					if ($logined) {
						$bind_user = false;
						$_accecss_token = $_accecss_token_secret = '';
						if ($key == 'sina.com') {
							$_accecss_token = $service->_accecss_token;	
							$_accecss_token_secret = $service->_accecss_token_secret;
							$bind_user = Better_DAO_ThirdBinding::getBindUser($key, $service->tid);
						}
						
						if (!$bind_user) {
							$this->user->syncsites()->add($key, $username, $password, $_accecss_token, $_accecss_token_secret, $service->tid);
							if ($followkai == 'true') $service->followKai();	
						} else {
							$logined = 2;//重复绑定
						}
					}						
				} else {
					$logined = 2;//重复绑定
				}
			}
			

		}
		
		
		if ($logined !== true) {
			$log_str = $username . '||' . $password . '||' . $logined;
			Better_Log::getInstance()->logAlert($log_str, 'bindsns_fail');
		}
		
		$this->output['logined'] = $logined;			
		
		$this->output();
	}
	
	public function emailcontactsAction()
	{
		$username = $this->getRequest()->getParam('username');
		$password = $this->getRequest()->getParam('password');
		$domain = $this->getRequest()->getParam('domain');
		
		$this->output['rows'] = array();
		$this->output['count'] = 0;
		$this->output['code'] = 0;

		$service = new Better_Service_EmailContacts($username, $password, $domain);
		
		$result = $service->search();

		$cacher = Better_Cache::remote();
		
		if ($result==1) {
			$results = $service->getResults();
			
			$finded_uids = array();
			foreach($results['rows'] as $row){
				$finded_uids[] = $row['uid'];
			}
			//email好友缓存
			$this->user->cache()->set('findedUids_email', $finded_uids);		
			
			$this->output['rows'] = &$results['rows'];
			$this->output['count'] = count($results['rows']);
			
			$cacher->set('emails_finded_'.$this->uid, $results['emails'], 60);
		}
		$this->output['code'] = $result;		
		
		$this->output();
	}
	
	//sns寻找好友
	public function snscontactsAction()
	{
		$username = $this->getRequest()->getParam('username', '');
		$password = $this->getRequest()->getParam('password', '');
		$domain = $this->getRequest()->getParam('domain', '');
		$bind = $this->getRequest()->getParam('bind', 'false')=='true' ? true : false;
		
		$this->output['rows'] = array();
		$this->output['count'] = 0;
		$this->output['code'] = 0;
		
		
		if($bind){
			$service = Better_Service_PushToOtherSites::factory($domain, $username, $password);
			$ck = $service->checkAccount($this->uid, $domain, $username);
			
			if ($ck) {
				$logined = $service->fakeLogin();
				if ($logined) {
					$this->user->syncsites()->add($domain, $username, $password, '', '', $service->tid);
				}						
			} else {
				$logined = 2;//重复绑定
			}
		}

		
		$oauth_token = $oauth_token_secret = '';
	
		if ('sina.com' == $domain) {
			$syncSites = (array)Better_User_Syncsites::getInstance($this->uid)->getSites();
			$oauth_token = $syncSites[$domain]['oauth_token'];
			$oauth_token_secret = $syncSites[$domain]['oauth_token_secret'];						
		}
		
		$service = new Better_Service_SnsContacts($username, $password, $domain, $oauth_token, $oauth_token_secret);
		
		$result = $service->search();

		if ($result==1) {
			$results = $service->getResults();
			$_data = array();
			
			$finded_uids = array();
			foreach($results['rows'] as $k=>$row){
				$is_friend = $this->user->isFriend($row['uid']);			
				if ($is_friend) continue;

				$finded_uids[] = $row['uid'];
				
				list($refusername, $refimageurl) = $this->__getThirdInfo($row['uid'], $domain);
				if ($refusername) $row['nickname'] = $refusername;
				if ($refimageurl) $row['avatar_small'] = $refimageurl;
				
				$_data[] = $row;
			}
			
			//sns好友缓存
			$this->user->cache()->set('findedUids_sns', $finded_uids);	
			
			$this->output['rows'] = &$_data;
			$this->output['count'] = count($_data);
			
		}
		$this->output['code'] = $result;		
		
		$this->output();
	}
	
	
	public function msnfriendsAction()
	{
		$msn = $this->getRequest()->getParam('msn');
		$password = $this->getRequest()->getParam('password');
		$this->output['rows'] = array();
		$this->output['code'] = 0;
		$this->output['count'] = 0;
						
		$service = new Better_Service_MsnFriends($msn, $password);
		$result = $service->search();
		
		$cacher = Better_Cache::remote();
		
		if ($result==1) {
			$results = $service->getResults();
			$this->output['rows'] = &$results['rows'];
			$this->output['count'] = count($results['rows']);
			$cacher->set('msn_emails_finded_'.$this->uid, $results['emails'], 60);
		}
		$this->output['code'] = $result;		
		
		$this->output();
	}
	
	
	public function invitemsnfriendsAction()
	{
		$msn = $this->getRequest()->getParam('email', $this->getRequest()->getParam('msn', ''));
		$password = $this->getRequest()->getParam('password');
		$useLastCache = $this->getRequest()->getParam('use_last_cache', '0');
		
		$this->output['rows'] = array();
		$this->output['code'] = 0;
		$this->output['count'] = 0;
		$cacher = Better_Cache::remote();
		$finded = $cacher->get('msn_emails_finded_'.$this->uid);
		
		if ($useLastCache && (is_array($finded) && count($finded)>0)) {
			$result = 1;
			$emails = $cacher->get('msn_emails_'.$this->uid);
			
			foreach ($emails as $email) {
				!in_array($email, $finded) && $this->output['rows'][$email] = array(
					'email' => $email,
					'name' => $email,
					);
			}
			$this->output['count'] = count($this->output['rows']);
		} else {
			$service = new Better_Service_MsnFriends($msn, $password);
			$result = $service->revertSearch();
			
			if ($result==1) {
				$this->output['rows'] = $service->getResults();
				$this->output['count'] = count($this->output['rows']);
			}
		}
		
		$this->output['code'] = $result;		
		
		$this->output();
	}
	
	public function inviteemailfriendsAction()
	{
		$username = $this->getRequest()->getParam('username');
		$password = $this->getRequest()->getParam('password');
		$domain = $this->getRequest()->getParam('domain');
		$useLastCache = $this->getRequest()->getParam('use_last_cache', '0');
		
		$this->output['code'] = 0;
		$this->output['rows'] = array();
		$this->output['count'] = 0;
		
		$finded = Better_Cache::remote()->get('emails_finded_'.$this->uid);
		for($i=0;$i<count($finded);$i++){
			$finded[$i]= strtolower($finded[$i]);
		}
		if ($useLastCache && (is_array($finded) && count($finded)>0)) {
			
			$this->output['code'] = 1;
			$emails = Better_Cache::remote()->get('email_contacts_with_name'.$this->uid);
			
			foreach ($emails as $email) {
				!in_array($email['email'], $finded) && $this->output['rows'][$email['email']] = $email;
			}
						
			$this->output['count'] = count($this->output['rows']);
		} else {
			if ($domain=='') {
				list($username, $domain) = explode('@', $this->getRequest()->getParam('email'));
			}
	
			$service = new Better_Service_EmailContacts($username, $password, $domain);
			$result = $service->revertSearch();
	
			if ($result==1) {
				$this->output['rows'] = $service->getResults();
				$this->output['count'] = count($this->output['rows']);
			}
			$this->output['code'] = $result;		
		}
		
		$this->output();
	}
	
	public function sendinviteemailAction()
	{
		$post = $this->getRequest()->getPost();

		$emails = explode('|', $post['emails']);
		$userInfo = Better_Registry::get('user')->getUser();
		$sents = 0;
		$this->output['uid'] = $this->uid;

		foreach ($emails as $email) {
			Better_Email_Invite::send($email, $userInfo);
			$sents++;
		}
		
		if ($sents>0) {
			$this->output['has_err'] = 0;
		} else {
			$this->output['has_err'] = 1;
		}		
		
		$this->output();
	}
	
	public function emailbookcontactsAction()
	{
		$mailliststr = $this->getRequest()->getParam('mailliststr');		
		$nameliststr = $this->getRequest()->getParam('nameliststr');
		$this->output['rows'] = array();
		$this->output['count'] = 0;
		$this->output['code'] = 0;

		$service = new Better_Service_EmailContacts();
		$result = $service->searchMaillist($mailliststr,$nameliststr);
		
		$cacher = Better_Cache::remote();
		
		if ($result==1) {
			$results = $service->getResults();
			$this->output['rows'] = &$results['rows'];
			$this->output['count'] = count($results['rows']);
			
			$cacher->set('emails_finded_'.$this->uid, $results['emails'], 60);
		}
		$this->output['code'] = $result;		
		
		$this->output();
	}
	
	public function inviteemailbookfriendsAction()
	{
		$mailliststr = $this->getRequest()->getParam('mailliststr');
		$nameliststr = $this->getRequest()->getParam('nameliststr');		
		$useLastCache = $this->getRequest()->getParam('use_last_cache', '0');
		
		$this->output['code'] = 0;
		$this->output['rows'] = array();
		$this->output['count'] = 0;
		
		$finded = Better_Cache::remote()->get('emails_finded_'.$this->uid);
		
		if ($useLastCache && (is_array($finded) && count($finded)>0)) {
			
			$this->output['code'] = 1;
			$emails = Better_Cache::remote()->get('email_contacts_'.$this->uid);
			
			foreach ($emails as $email) {
				!in_array($email, $finded) && $this->output['rows'][$email] = array(
					'email' => $email,
					'name' => $email,
					);
			}						
			$this->output['count'] = count($this->output['rows']);
		} else {
			$service = new Better_Service_EmailContacts();
			$result = $service->revertsearchMaillist($mailliststr,$nameliststr);
			if ($result==1) {
				$this->output['rows'] = $service->getResults();
				$this->output['count'] = count($this->output['rows']);
			}
			$this->output['code'] = $result;		
		}
		
		$this->output();
	}
	
	
	
	/**
	 * 
	 * @param $uid
	 * @param $protocol
	 * @return unknown_type
	 */
	public function __getThirdInfo($uid, $protocol)
	{
		$refusername = $refimageurl = '';
		$syncSites = (array)Better_User_Syncsites::getInstance($uid)->getSites();
		$_info = $syncSites[$protocol];
		$service_new = Better_Service_PushToOtherSites::factory($protocol, $_info['username'], $_info['password'], $_info['oauth_token'], $_info['oauth_token_secret']);
	
		if ( $service_new->fakeLogin() && $service_new->userinfo_json) {
			$refusername = $service_new->userinfo_json->name;
			$refimageurl = $service_new->userinfo_json->profile_image_url;
		}
		return array($refusername, $refimageurl);		
	}
	
}