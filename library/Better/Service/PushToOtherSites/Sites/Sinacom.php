<?php

class Better_Service_PushToOtherSites_Sites_Sinacom extends Better_Service_PushToOtherSites_Common
{
	protected $apiKey = '';
	public $_accecss_token = '';
	public $_accecss_token_secret = '';
	public $tid = '';
	
	public function __construct($username='', $password='', $accecss_token='' , $accecss_token_secret='')
	{
		$this->setUserPwd($username, $password);
		
		$this->apiKey = Better_Config::getAppConfig()->sina_api->key;
		$this->apiKeySecret = Better_Config::getAppConfig()->sina_api->secret;
    	$this->_accecss_token = $accecss_token;
        $this->_accecss_token_secret = $accecss_token_secret;		
		
		$this->_login_url = 'http://api.t.sina.com.cn/account/verify_credentials.json?source='.$this->apiKey;
		$this->_api_url = 'http://api.t.sina.com.cn/statuses/update.json';
				
		$this->_login_find_key = 'name';
		
		$this->_protocol = 'sina.com';
	}
	
	/**
	 * 提交的参数赋值
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		if ($this->_accecss_token) {
			$_api_url = 'http://api.t.sina.com.cn/statuses/update.json';
			$param['status'] = $msg;
			
			if ($geo) {
				$param['long'] =  $geo['lon'];
				$param['lat'] =  $geo['lat'];
			}
			
			if ($attach) {
				$param['pic'] = '@' . $attach;
				$_api_url = 'http://api.t.sina.com.cn/statuses/upload.json';
			}
			
			$this->html = $this->oauthPost($_api_url, $param, 'post');
			if ($this->html !== false) {
				return true;	
			} 
			
			return false;
		}
		
		
		$this->_request = array(
			'status' => $msg,
			'source' => $this->apiKey,
			);
		if ($geo) {
			$this->_request['long'] = $geo['lon'];
			$this->_request['lat'] = $geo['lat']; 
		}
			
		if ($attach) {
			$this->_api_url = 'http://api.t.sina.com.cn/statuses/upload.json';
			$this->_file = 'pic';
		}	
				
		return parent::post($msg, $attach, $poiId='', $geo='');
	}
	
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		if ($this->_accecss_token) {
			$_api_url = "http://api.t.sina.com.cn/statuses/destroy/$id.xml";
			$param = array();
			$result = $this->oauthPost($_api_url, $param, 'post');
			
			if ($result !== false) {
				return true;
			}
			
			return false;
		}
		
		$this->_api_url = "http://api.t.sina.com.cn/statuses/destroy/$id.xml";
		
		$this->_request = array(
			'source' => $this->apiKey,
			);
			
		return parent::delete($id);
	}
	
	/**
	 * 检查返回值
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#checkPost($return)
	 */
	public function checkPost($return)
	{
		$flag = false;
		
		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXml($return);
			$items = $dom->query('update');
			foreach($items as $item) {
				$flag = $item->nodeValue=='true' ? true : false;
				break;
			}
		} catch(Exception $e) {
		}
		
		return $flag;
	}
	
	/**
	 * 
	 */
	
	public function getInfo()
	{
		$data = array();
		$data['nickname'] = $this->userinfo_json->screen_name;
		$data['gender'] = $this->userinfo_json->gender == 'm' ? 'male' : 'female';
		$data['image_url'] = $data['imgurl'] = $this->userinfo_json->profile_image_url;
		$data['is_vip'] = $this->userinfo_json->verified;
		
		return $data;
	}
	
	
	/**
	 * 还未解析数据哦
	 * http://api.t.sina.com.cn/statuses/show/:id.(json|xml)
	 */
	public function getStatuses($id)
	{
		$_api_url = "http://api.t.sina.com.cn/statuses/show/$id.json";
		$result = $this->oauthPost($_api_url, array(), 'get');
		if ($result !== false) {
			$json = json_decode($result);
			$ids = $json->ids;
		} else {
			$ids = array();
		}
		
		return $ids;
	}
	
	
	/**
	 * 寻找好友
	 */
	public function getFriends()
	{
		if ($this->_accecss_token) {
			$_api_url = 'http://api.t.sina.com.cn/friends/ids.json';
			$param['count'] = 5000;
			$result = $this->oauthPost($_api_url, $param, 'get');
			if ($result !== false) {
				$json = json_decode($result);
				$ids = $json->ids;
			} else {
				$ids = array();
			}
			
			return $ids;
		}
		
		$_api_url = 'http://api.t.sina.com.cn/friends/ids.json?source='.$this->apiKey;
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$this->_request['count'] = 5000;
		$client->setParameterGet($this->_request);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			$ids = $json->ids;
		} else {
			$ids = array();
		}
		
		return $ids;
	}
	
	/**
	 * 粉丝数
	 */
	public function getFollowerids()
	{
		if ($this->_accecss_token) {
			$_api_url = 'http://api.t.sina.com.cn/followers/ids.json?source=';
			$param['count'] = 5000;
			$result = $this->oauthPost($_api_url, $param, 'get');
			if ($result !== false) {
				$json = json_decode($result);
				$ids = $json->ids;				
			} else {
				$ids = array();
			}
			
			return $ids;
		}
		
		
		$_api_url = 'http://api.t.sina.com.cn/followers/ids.json?source=' . $this->apiKey;
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$this->_request['count'] = 5000;
		$client->setParameterGet($this->_request);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			$ids = $json->ids;
		} else {
			$ids = array();
		}
		
		return $ids;		
	}
	
	/**
	 * 得到可以下载的头像的Url
	 * 
	 * http://tp2.sinaimg.cn/1653904053/50/1283204287/1
		http://tp2.sinaimg.cn/1653904053/180/1283204287/1.jpg
	 */
	public function getAvatar()
	{
		$url = '';
		$url = $this->userinfo_json->profile_image_url;
		$url = str_replace('/50/', '/180/', $url);
		$url .= '.jpg';
		
		return $url;
	}
	
	/**
	 * 添加关注
	 */
	public function followKai($id='1744180662')
	{
		if ($this->_accecss_token) {
			$_api_url = 'http://api.t.sina.com.cn/friendships/create.json';
			$param['user_id'] = $id;
			$result = $this->oauthPost($_api_url, $param, 'post');
			if ($result !== false) {
				return $json->name;
			}
			return false;
		}
		
		$_api_url = 'http://api.t.sina.com.cn/friendships/create.json?source=' . $this->apiKey;
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$this->_request['user_id'] = $id;
		$client->setParameterPost($this->_request);
		$client->request(Zend_Http_Client::POST);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			return $json->name;
		}
		
		return false;
	}	
	
	/**
	 * 
	 */
	public function getToken()
	{
		$_url = "http://api.t.sina.com.cn/oauth/access_token";
		
		$param['x_auth_username'] = $this->_username;
		$param['x_auth_password'] = $this->_password;
		$param['x_auth_mode'] = 'client_auth';
		
		$oauth = new Better_Oauth_Weibo( $this->apiKey , $this->apiKeySecret);
		$r = $oauth->post($_url , $param, false );	
		if ($oauth->http_code == '200') {
			parse_str($r, $_array);
			$this->_accecss_token = $_array['oauth_token'];
	 		$this->_accecss_token_secret = $_array['oauth_token_secret'];
			$this->tid = $_array['user_id'];
			return $_array;
		} else {
			$filename = $this->_protocol;
			$boby = $oauth->http_code . '||' . $this->_username . '||' . $this->_password . '||' . $r;
			$this->_log($boby, $filename);				
		}
		
		return false;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see library/Better/Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#fakeLogin($uid)
	 */
	public function fakeLogin($uid=0) 
	{
		if ($uid) return true;
		$token = $this->getToken();
		if ($token !== false) {
			$v = $this->verify_credentials();
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * 
	 */
	public function oauthPost($url, $param, $mode='post')
	{
		$oauth = new Better_Oauth_Weibo( $this->apiKey , $this->apiKeySecret, $this->_accecss_token, $this->_accecss_token_secret);
		
		$multi = $param['pic'] ? true : false;
		if ($mode == 'get') {
			$r = $oauth->get($url, $param);
		} else {
			$r = $oauth->post($url, $param, $multi);
		}
		
		if ($oauth->http_code == 200) {
			return $r;
		} else {
			//日志
			$filename = $this->_protocol;
			$boby = $oauth->http_code . '||' . $this->_username . '||' . $this->_password . '||' . $r;
			$this->_log($boby, $filename);			
		}
		
		return false;
	}
	
	
	/**
	 * 
	 */
	public function verify_credentials()
	{
		$_url = "http://api.t.sina.com.cn/account/verify_credentials.json";
		$r = $this->oauthPost($_url, array(), 'get');		
		if ($r) {
			$json = json_decode($r);
			$this->userinfo_json = $json;
			$json->id && $this->tid = $json->id;			
		}
		
		return $r;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#getThirdUserName($username, $password)
	 */
	public function getThirdUserName($parms)
	{	
		$return_username = '';
		$accecss_token = $parms['oauth_token'];
		$accecss_token_secret = $parms['oauth_token_secret'];
		$oauth = new Better_Oauth_Weibo( $this->apiKey , $this->apiKeySecret, $accecss_token, $accecss_token_secret);
		$_url = "http://api.t.sina.com.cn/account/verify_credentials.json";
		$r = $oauth->get($_url, array());
		if ($r) {
			$json = json_decode($r);
			$return_username = $json->screen_name;
		}		
	
		return $return_username;
	}	
	
	/**
	 * 
	 */
	public function getStatusesFriends()
	{
		$oauth = new Better_Oauth_Weibo( $this->apiKey , $this->apiKeySecret, $this->_accecss_token, $this->_accecss_token_secret);
		$url = 'http://api.t.sina.com.cn/statuses/friends.xml';
		$r = $oauth->get($url, array('count'=>200));
		var_dump($r);exit;
	}
}