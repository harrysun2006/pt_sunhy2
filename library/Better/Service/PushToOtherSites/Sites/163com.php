<?php
/**
 * 
 * @author Jeff
 *
 */


class Better_Service_PushToOtherSites_Sites_163com extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 't.163.com';
	public $_akey;
	public $_skey;
	
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
		$this->_api_url = 'http://api.t.163.com/statuses/update.json';
		$this->_accecss_token = $accecss_token;
		$this->_accecss_token_secret = $accecss_token_secret;	
		$this->_akey = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$this->_skey = Better_Config::getAppConfig()->oauth->key->t163_skey;
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
		if ($attach) {
			$_imgUrl = $this->upload($attach);
			$msg .= ' ' . $_imgUrl;			
		}
		
		$param['status'] = $msg;
		$param['source'] = '<a href="http://k.ai/" >开开</a>';
		$text = $this->oauth->post($url , $param);
		$this->html = $text;
		
		$flag = false;
		$flag = $this->checkPost($text);	
		return $flag;	
	}
	
	/**
	 * 上传图片
	 */
	public function upload($attach)
	{
//return 'http://126.fm/9CNsx';
		$akey = $this->_akey;
		$skey = $this->_skey;
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		$url = 'http://api.t.163.com/statuses/upload.json';
		$param = array();
		$param['pic'] = "@$attach";
		$text = $this->oauth->post($url, $param, true);
		$j = json_decode($text);
		return $j->upload_image_url ? $j->upload_image_url : '';	
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
		
		$url = "http://api.t.163.com/statuses/destroy/$id.json";
		$html = $this->oauth->post($url);			
		return $this->oauth->http_code == 200 ? true : false;
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
	public function fakeLogin()
	{	
		$this->oauth = new Better_Oauth_Weibo( $this->_akey , $this->_skey , $this->_accecss_token , $this->_accecss_token_secret );
		if ( APPLICATION_ENV != 'production' ) {
			$this->oauth->proxy = 'http://10.10.1.254:1080';
			$this->oauth->proxy_type = CURLPROXY_SOCKS5;
		}		
		$url = "http://api.t.163.com/account/verify_credentials.json";
		$html = $this->oauth->get($url);
		
		if ($this->oauth->http_code == 200) {
			$json = json_decode($html);
			$this->userinfo_json = $json;
			$this->tid = $json->id;
		        return true;
	        }
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#checkPost($return)
	 */
	public function checkPost($text)
	{
		$json = json_decode($text);
		if ($json->id) {
			return true;
		}
		return false;
	}
	
	/**
	 * [status] => stdClass Object
        (
            [id] => 6865184175786715565
            [source] => <a target="_blank" href="http://k.ai">寮€寮€_鎴戠殑瓒宠抗</a>
            [text] => 杩樻槸璁拌€� @鍚磋弫Jing 鐨勫彿鍙姏寮哄晩 浠婂ぉ鏀跺埌涓€鏉′氦閫氳繚绔犵煭淇� 锛坧s锛氭垜濂藉儚娌℃湁杩濈珷锛� NND
            [created_at] => Thu Jul 21 12:11:14 +0800 2011
            [in_reply_to_screen_name] => 
            [in_reply_to_status_id] => 
            [in_reply_to_user_id] => 
            [in_reply_to_user_name] => 
            [truncated] => 
        )

    [following] => 
    [blocking] => 
    [followed_by] => 
    [name] => 鎴戠湡鏄啹鍚屽
    [location] => ,
    [id] => 6598722534732050938
    [description] => 
    [email] => fengjun.net@gmail.com
    [gender] => 0
    [verified] => 
    [url] => 
    [screen_name] => 8243206948
    [profile_image_url] => http://oimagec4.ydstatic.com/image?w=48&h=48&url=http%3A%2F%2F126.fm%2F2FYeTG
    [created_at] => Mon Nov 29 16:30:13 +0800 2010
    [darenRec] => 
    [favourites_count] => 0
    [followers_count] => 11
    [friends_count] => 3
    [geo_enable] => 
    [icorp] => 0
    [realName] => 涓嶅憡璇変綘
    [statuses_count] => 483
    [sysTag] => 
    [userTag] =>
	 */
	public function getUserInfo()
	{
		$userinfo = array();
		$login = $this->fakeLogin();
		if ($login) {
			$userinfo['email'] = $this->userinfo_json->email;
			$userinfo['gender'] = $this->userinfo_json->gender;
			$userinfo['id_ma'] = $this->userinfo_json->id;
			$userinfo['id'] = $this->userinfo_json->screen_name; //这个为什么是用户id了啊 网易很神奇哦
			$userinfo['screen_name'] = $this->userinfo_json->screen_name;
			$userinfo['name'] = $this->userinfo_json->name;
			$userinfo['nickname'] = $this->userinfo_json->name;
			$userinfo['profile_image_url'] = $this->userinfo_json->profile_image_url;
			$userinfo['image_url'] = $this->userinfo_json->profile_image_url;
		}
		return $userinfo;
	}

	
	/**
	 * 
	 * @return unknown_type
	 */
	public function getInfo()
	{
		return $this->getUserInfo();
	}
	
}