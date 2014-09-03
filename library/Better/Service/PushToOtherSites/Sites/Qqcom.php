<?php
/**
 * 
 * @author Jeff
 *
 */


class Better_Service_PushToOtherSites_Sites_Qqcom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'open.t.qq.com';
	public $_akey;
	public $_skey;
	public $_uid;
	public $userinfo_json;
	
	/**
	 * 
	 * @param $username
	 * @param $password
	 * @param $accecss_token
	 * @param $accecss_token_secret
	 * @return unknown_type
	 */
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		$this->setUserPwd($username,$password);
		$this->_api_url = 'http://open.t.qq.com/api/t/add';
		$this->_accecss_token = $accecss_token;
		$this->_accecss_token_secret = $accecss_token_secret;	
		$this->_akey = Better_Config::getAppConfig()->oauth->key->qq_akey;
		$this->_skey = Better_Config::getAppConfig()->oauth->key->qq_skey;
		
		$this->_protocol = 'qq.com';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{		
		$akey = $this->_akey;
		$skey = $this->_skey;
		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		$url = $this->_api_url;
		$param = array();
		$param['format'] = 'json';
		$param['content'] = $msg;
		$_ip = Better_Registry::get('ip');
		$param['clientip'] = $_ip ? $_ip : $this->_makeIp($this->_uid);
print_r($param);		
//		$param['jing'] = '';
//		$param['wei'] = '';
//		if ($geo) {
//			$param['jing'] = $geo['lon'];
//			$param['wei'] = $geo['lat'];
//		} 
		if ($attach) {
			$url = 'http://open.t.qq.com/api/t/add_pic';
			$param['pic'] = "@$attach";	
			$text = $this->oauth->post($url , $param, true);
		} else {
			$text = $this->oauth->post($url , $param);
		}				
		
		$this->html = $text;
		
		$flag = false;
		$flag = $this->checkPost($text);	
		
		return $flag;	
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#delete($id, $mode)
	 */
	public function delete($id)
	{
		$akey = $this->_akey;
		$skey = $this->_skey;
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		
		$url = "http://open.t.qq.com/api/t/del";
		$param = array();
		$param['format'] = 'json';
		$param['id'] = $id;
			
		$text = $this->oauth->post($url , $param);
		$this->html = $text;
		
		$flag = false;
		$flag = $this->checkPost($text);	
		return $flag;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#login()
	 */
	public function login()
	{
		return $this->fakeLogin();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#fakeLogin($uid)
	 */
	public function fakeLogin($uid=0)
	{	
		if ($uid) $this->_uid = $uid;
		
		$this->getUserInfo($this->_akey, $this->_skey, $this->_accecss_token, $this->_accecss_token_secret);
		return true;
	}
	
	/**
	 * 虚假的IP
	 * @param $uid
	 * @return unknown_type
	 */
	public function _makeIp($uid)
	{
		$long = ip2long('221.224.254.254');
		return long2ip($long - $uid);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#checkPost($return)
	 */
	public function checkPost($text)
	{
		
		$json = json_decode($text);
		if ($json->msg == 'ok') {
			return true;
		}
var_dump($text);		
		return false;
	}
	
	/**
	 * 得到第三方id 不同的解析由各个协议中实现
	 *
	 */
	public function get3rdId()
	{
		$json = json_decode($this->html);	
		$id = $json->data->id;
		
		$this->third_id = $id;
		
		return $id;
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#getThirdUserName($username, $password)
	 */
	public function getUserInfo($username, $password, $accecss_token, $accecss_token_secret)
	{
		$akey = $this->_akey;
		$skey = $this->_skey;		

		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		
		$url = "http://open.t.qq.com/api/user/info";
		$param = array();
		$param['format'] = 'json';
		$text = $this->oauth->get($url , $param);
		$json = json_decode($text);	
		if ($json->msg == 'ok') {
			$this->userinfo_json = $json->data;
			$head = $json->data->head;
			$head = substr($head, -1) == '/' ? $head : $head . '/';
			$this->userinfo_json->profile_image_url = $head;
			return $this->userinfo_json;
		} 

		return false;
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#getThirdUserName($username, $password)
	 */
	public function getThirdUserName($parms)
	{	
		$return_username = '';
		
		$username = $parms['username']; 
		$password = $parms['password']; 		
		$accecss_token = $parms['oauth_token'];
		$accecss_token_secret = $parms['oauth_token_secret'];		
		
		$json = $this->getUserInfo($username, $password, $accecss_token, $accecss_token_secret);
		if ($json) {
			$return_username = $json->name;
		}		

		return $return_username;
	}
	
	
	public function getThirdUserNick($username, $password, $accecss_token, $accecss_token_secret)
	{
		$return_username = '';
		$json = $this->getUserInfo($username, $password, $accecss_token, $accecss_token_secret);
		if ($json) {
			$return_username = $json->nick;
		}		

		return $return_username;
	}
	
	/**
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		$followers_count = 0;
		$akey = $this->_akey;
		$skey = $this->_skey;
		$accecss_token = $this->_accecss_token;		
		$accecss_token_secret = $this->_accecss_token_secret;;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		$url = "http://open.t.qq.com/api/user/info";
		$param = array();
		$param['format'] = 'json';
		$text = $this->oauth->get($url , $param);
		$json = json_decode($text);
		if ($json->msg == 'ok') {
			$followers_count = $json->data->fansnum;
		}		
		
		return $followers_count;		
	}
	
	/**
	 * 得到好友
	 */
	public function getFriends()
	{
		$ids = array();
		
		$akey = $this->_akey;
		$skey = $this->_skey;
		$accecss_token = $this->_accecss_token;		
		$accecss_token_secret = $this->_accecss_token_secret;;
		$param = array();
		$param['format'] = 'json';
		$param['reqnum'] = 10;
		$page = 1;
		$hasnext = 0; //有数据 未完
		
		while (1) {
			$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
			$url = "http://open.t.qq.com/api/friends/idollist";
			$param['startindex'] = ($page - 1) * $param['reqnum'];
			$text = $this->oauth->get($url , $param);
			$json = json_decode($text);
	
			if ($json->msg == 'ok') {
				foreach ($json->data->info as $v) {
					$ids[] = $v->name;
				}
				$hasnext = $json->data->hasnext;
				$page++;				
				if ($hasnext == 1) break;
			} else {
				break;
			}		
		}
		
		return $ids;
	}
	
	/**
	 * 
	 */
	public function getFollowerids()
	{
		$ids = array();
		
		$akey = $this->_akey;
		$skey = $this->_skey;
		$accecss_token = $this->_accecss_token;		
		$accecss_token_secret = $this->_accecss_token_secret;;
		$param = array();
		$param['format'] = 'json';
		$param['reqnum'] = 10;
		$page = 1;
		$hasnext = 0; //有数据 未完
		
		while (1) {
			$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
			$url = "http://open.t.qq.com/api/friends/fanslist";
			$param['startindex'] = ($page - 1) * $param['reqnum'];
			$text = $this->oauth->get($url , $param);
			$json = json_decode($text);
	
			if ($json->msg == 'ok') {
				foreach ($json->data->info as $v) {
					$ids[] = $v->name;
				}
				$hasnext = $json->data->hasnext;
				$page++;				
				if ($hasnext == 1) break;
			} else {
				break;
			}		
		}
		
		return $ids;		
	}	
	
	
	
	/**
	 * 增加关注用户
	 */
	public function friendsAdd($name='kaierkaier')
	{
		$akey = $this->_akey;
		$skey = $this->_skey;				
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $this->_accecss_token , $this->_accecss_token_secret ); 
		
		$url = "http://open.t.qq.com/api/friends/add";
		$param = array();
		$param['format'] = 'json';
		$param['name'] = $name;
		$text = $this->oauth->post($url , $param);
		$json = json_decode($text);
		if ($json->msg == 'ok') {
			return true;
		}
		
		return false;		
	}
	
	/**
	 * http://app.qlogo.cn/mbloghead/3b2511b98bcd8a1e60f6
       http://app.qlogo.cn/mbloghead/3b2511b98bcd8a1e60f6/

	 * 
	 * @return unknown_type
	 */
	public function getAvatar()
	{
		$url = '';
		$url = $this->userinfo_json->profile_image_url;		
		return $url;
	}	
	
	
	/**
	 *     [birth_day] => 8
    [birth_month] => 2
    [birth_year] => 1909
    [city_code] => 5
    [country_code] => 1
    [edu] => Array
        (
            [0] => stdClass Object
                (
                    [departmentid] => 19394
                    [id] => 24037
                    [level] => 6
                    [schoolid] => 11722
                    [year] => 1997
                )

        )

    [email] => 6086716@qq.com
    [fansnum] => 43
    [head] => http://app.qlogo.cn/mbloghead/3b2511b98bcd8a1e60f6
    [idolnum] => 42
    [introduction] => 浠嬬粛:
    [isent] => 0
    [isvip] => 0
    [location] => 姹熻嫃 鑻忓窞
    [name] => jeffeng
    [nick] => Jeff
    [province_code] => 32
    [sex] => 1
    [tag] => 
    [tweetnum] => 442
    [verifyinfo] => 
    [profile_image_url] => http://app.qlogo.cn/mbloghead/3b2511b98bcd8a1e60f6/
	 * @return unknown_type
	 */
	public function getInfo()
	{
		$third_info = array();
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$json = $this->getUserInfo('', '', $accecss_token, $accecss_token_secret);
		if ($json) {
			$third_info['nickname'] = $json->name;
			$third_info['image_url'] = $json->profile_image_url;
			$third_info['is_vip'] = $json->isvip;
			$this->tid = $json->name;
		}
	
		return $third_info;
	}
}