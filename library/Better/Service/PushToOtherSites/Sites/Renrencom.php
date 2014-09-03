<?php
/**
 * 
 * @author Jeff
 *
 */


class Better_Service_PushToOtherSites_Sites_Renrencom extends Better_Service_PushToOtherSites_Common
{
	public $_akey;
	public $_skey;
	public $_uid;
	public $userinfo_json = '';

	public $_expires_in = 0;
	public $_session_key = '';
	public $_session_secret = '';
	public $tid;
	
	public $_accecss_token = '';
	public $_accecss_token_secret = '';
	
		
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
		$this->_api_url = 'http://api.renren.com/restserver.do';
		$this->_accecss_token = $accecss_token;
		$this->_accecss_token_secret = $accecss_token_secret;	
		$this->_akey = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$this->_skey = Better_Config::getAppConfig()->oauth->key->renren_skey;
		
		$this->_protocol = 'renren.com';
	}
	
	
	/**
	 * 去session_key
	 * 
	 */
	public function _getSessionKey()
	{
		$_access_token = $this->_accecss_token;
		$_url = "http://graph.renren.com/renren_api/session_key";
		$client = new Zend_Http_Client();
		$client->setUri($_url);
		$client->setParameterGet(array(
				    'oauth_token'  => $_access_token,
				));
		
		$client->request();
		$html = $client->getLastResponse()->getBody();
		$status = $client->getLastResponse()->getStatus();
		if ($status != 200 ) return false;
		
		$json = json_decode($html);
		$this->_uid = $json->user->id;
		$this->_session_key = $json->renren_token->session_key;
		$this->_session_secret = $json->renren_token->session_secret;
		$this->_expires_in = $json->renren_token->expires_in;
		
		return $this->_session_key;
	}

	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#fakeLogin($uid)
	 */
	public function fakeLogin($uid=0)
	{	
		if ($uid) return true;
		if ($this->_getSessionKey()) {
			return true;
		}
		return false;
	}	
	

	/**
	 * 
	 * @param $param
	 * @param $secret
	 * @return unknown_type
	 */
	public function _buildQuery($param, $secret) 
	{ 
		$param['api_key'] = $this->_akey;
		$param['call_id'] = microtime(true);
		$param['v'] = '1.0';
		$param['session_key'] = $this->_session_key;
		$param['format'] = 'json';		
		
		ksort($param);	
		$request_str = ''; 
		foreach ($param as $key => $value) { 
			$request_str .= $key . '=' . $value; // 没有分割符 
		} 
		$sig = $request_str . $secret; 
		$sig = md5($sig); 
		$param['sig'] = $sig; 
		return $param; 
	} 

	/**
	 * users.getInfo
	 */
	public function usersGetInfo($uid='')
	{
		if (!$uid) $uid = $this->_uid;
		$data = array();
		$data['method'] = 'users.getInfo';
		$data['uids'] = $uid;
		
		$param = $this->_buildQuery($data, $this->_skey);
		$r = self::_httpPost($this->_api_url, $param);		
		if ($r === false) return false;
		
		$json = json_decode($r);
		return $json;		
	}
	
	
	/**
	 * places.create
	 */
	public function placesCreate($poiInfo)
	{
		$data = array();
		$data['method'] = 'places.create';
		$data['poi_id'] = $poiInfo['poi_id'];
		$data['name'] = $poiInfo['name'];
		$data['address'] = $poiInfo['address'] ? $poiInfo['address'] : '地址不详';
		$data['longitude'] = $poiInfo['lon'];
		$data['latitude'] = $poiInfo['lat'];
		
		$param = $this->_buildQuery($data, $this->_skey);
		$r = self::_httpPost($this->_api_url, $param);
var_dump($r);		
		if ($r === false) return false;
		
		$json = json_decode($r);
		return $json->place->id;
	}
	
	
	/**
	 * checkin
	 */
	public function checkinsCheckin($id, $message='', $lon='', $lat='')
	{
		$data = array();
		
		$data['method'] = 'checkins.checkin';
		$data['place_id'] = $id;
		$data['privacy'] = 0;
		$message && $data['message'] = $message;
		$lon && $data['longitude'] = $lon;
		$lat && $data['latitude'] = $lat;
		
		$param = $this->_buildQuery($data, $this->_skey);		
		$r = self::_httpPost($this->_api_url, $param);
		if ($r === false) return false;
		
		$json = json_decode($r);
		return $json->checkin->id;
	}
	
	
	/**
	 *status.set 
	 */
	public function statusSet($message, $place_id='')
	{
		$data = array();
		
		$data['method'] = 'status.set';		
		$data['status'] = $message;	
		$place_id && $data['place_id'] = $place_id;	

		$param = $this->_buildQuery($data, $this->_skey);		
		$r = self::_httpPost($this->_api_url, $param);
		if ($r === false) return false;	
		$json = json_decode($r);
		return $json->result;
	}
	
	
	/**
	 * photo_upload
	 */
	public function photosUpload($img, $text, $place_id='')
	{
		$data = array();
		
		$data['method'] = 'photos.upload';
		$data['caption'] = $text;
		$place_id && $data['place_id'] = $place_id;
		
		$param = $this->_buildQuery($data, $this->_skey);
		$param['upload'] = $img;			
		$r = self::_httpPost($this->_api_url, $param, true);
		if ($r === false) return false;
		
		$json = json_decode($r);
		
		return $json->pid;
	}
	
	
	/**
	 * 这个没有用的接口
	 * feed.publish
	 * 
	 */
	public function feedPublish($message, $desc, $url='', $template_id=1, $action=array())
	{
		$data = array();
		
		$data['method'] = 'feed.publish';		
		$data['template_id'] = $template_id;
		$data['title_data'] = json_encode(array('name'=>$message));
		$data['body_data'] = json_encode(array('content'=>$desc));
		$url && $data['attachment'] = json_encode(array('src' => $url, 'href' => $url));
		$action && $data['action_links'] = json_encode(array('href'=> $action['href'] , 'text'=> $action['text']));
		
		$param = $this->_buildQuery($data, $this->_skey);
		$r = self::_httpPost($this->_api_url, $param);
//file_put_contents('e:/temp/ff.xml', $r);			
		if ($r === false) return false;	
		$json = json_decode($r);
		return $json->result;
		
	}
	
	
	/**
	 * http post
	 */
	public static function _httpPost($url, $data, $isPhoto=false)
	{
		$post_data = $data;
		unset($post_data['upload']);
		
		$client = new Zend_Http_Client();
		$client->setUri($url);
		$client->setParameterPost($post_data);
		
		if ($isPhoto) {			
			$_filetype = self::_getFileType($data['upload']);
			$client->setFileUpload($data['upload'], 'upload', NULL, $_filetype);			
		}
		
				
		$client->request('POST');
		
		$html = $client->getLastResponse()->getBody();
		$status = $client->getLastResponse()->getStatus();
		if ($status != 200 ) return false;	

		return $html;
	}
	
	/**
	 * 
	 * @param $filename
	 * @return unknown_type
	 */
	public function _getFileType($filename)
	{
		$filename = basename($filename);
		$a = explode('.', $filename);
		$ext = array_pop($a);
		$ext = strtolower($ext);
		switch ($ext) {
			case 'jpg':
				$type = 'image/jpeg';
				break;
			case 'png':
				$type = 'image/png';
				break;
			case 'gif':
				$type = 'image/gif';
				break;	
			case 'bmp':
				$type = 'image/bmp';
				break;							
			default:
				$type = 'image/jpeg';
			break;
		}
		
		return $type;
	}	
	
	
	/**
	 * 
	 */
	public function getUserInfo()
	{
		$userinfo = array();
		
		$this->_getSessionKey();
		$usersInfo = $this->usersGetInfo();
		if (!is_array($usersInfo)) {
			return false;
		}
		$this->userinfo_json = $usersInfo[0];
		if ($this->userinfo_json->uid) {
			$userinfo['id'] = $this->userinfo_json->uid;
			$userinfo['name'] = $this->userinfo_json->name;
			$userinfo['profile_image_url'] = $this->userinfo_json->headurl;	
			$userinfo['gender'] = $this->userinfo_json->sex == '1' ? 'male' : 'female';		
		}

		return $userinfo;
	}
	
	
	/**
	 * 
	 */
	public function getInfo()
	{
		$r = $this->getUserInfo();
		if ($r) {
			$r['image_url'] = $r['profile_image_url'];
			$r['nickname'] = $r['name'];
		}
		
		return $r;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		$_uid = Better_Registry::get('suid');
		
		if( 1 || $_uid == '175659' || $_uid == '1704086' ) {
			return $this->postFull($msg, $attach, $poiId, $geo);
		}
		
		$this->_getSessionKey();
		$type = Better_Registry::get('type');
		
		if (preg_match('/badges/', $attach)) { //勋章同步
			$_a = explode('/', $attach);
			$_name = array_pop($_a);
			$_a = explode('.', $_name);
			$_bid = array_shift($_a);
			
			$badgeInfo = Better_Badge::getBadge($_bid)->getParams();
			$badge_name = $badgeInfo['badge_name'];
			
			$url = "http://k.ai/images/badges/big/$_bid.png";
			return self::feedPublish($badge_name, $msg, $url, 1);
		}		
		
		//$attach = '';//人人你就不要图片了吧 
		// /usr/local/apache2/htdocs/public/files/21/23/82/125800.296208.jpg
		
		$attach_url = str_replace('/usr/local/apache2/htdocs/public', 'http://k.ai', $attach);
		$attach = '';
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			$poiInfo = $poi->getBasic();
			$poiInfo['lon'] = $geo['lon'];
			$poiInfo['lat'] = $geo['lat'];
			$_rr_id = $this->placesCreate($poiInfo);
		}
		
		if ($type == 'checkin') { //签到同步 签到同步不支持图片
			if ($attach_url) {
				self::feedPublish('', $msg, $attach_url, 4);	
			}
			return self::checkinsCheckin($_rr_id, $msg);
		}
		
		if ($type == 'tips') {
			$attach && $url = self::photosUpload($attach, 'photo');
			$action = array(
							'href' => 'http://k.ai/poi/' . $poiId,
							'text' => '去看看'
							);
							
			return self::feedPublish($poiInfo['name'], $msg, $attach_url, 2, $action);			
		}

		if ($type == 'normal') {
			if ($poiId) { //有位置的吼吼
				$attach && $url = self::photosUpload($attach, $msg);
				$action = array(
								'href' => 'http://k.ai/poi/' . $poiId,
								'text' => '去看看'
								);
				return self::feedPublish($poiInfo['name'], $msg, $attach_url, 3, $action);				
			} else { //没有位置的吼吼
				if ($attach) {//有图片 没有位置 去相册吧
					return self::photosUpload($attach, $msg);	
				}
				
				if ($attach_url) {
					return self::feedPublish('', $msg, $attach_url, 4);	
				}
								
				return self::statusSet($msg); //你位置 图片都没有 去状态吧
			}
		}
		
		//这个就是保护而已
		if ($msg) { 
			return self::statusSet($msg);
		}
		
		return false;			
		
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function postFull($msg, $attach='', $poiId='', $geo='')
	{		
		
		$this->_getSessionKey();
		$type = Better_Registry::get('type');
		
		$is_badge = false;
		if ( preg_match('/badges/', $attach) ){
			$is_badge = true;
		}
		
		if ($is_badge) { //勋章同步
			$_a = explode('/', $attach);
			$_name = array_pop($_a);
			$_a = explode('.', $_name);
			$_bid = array_shift($_a);
			
			$badgeInfo = Better_Badge::getBadge($_bid)->getParams();
			$badge_name = $badgeInfo['badge_name'];
			
			$url = "http://k.ai/images/badges/big/$_bid.png";
			return self::feedPublish($badge_name, $msg, $url, 1);
		}
		
		$_rr_id = '';
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			$poiInfo = $poi->getBasic();
			$poiInfo['lon'] = $geo['lon'];
			$poiInfo['lat'] = $geo['lat'];
			$_rr_id = $this->placesCreate($poiInfo);			
		}
		
		if ($attach) {
			$url = self::photosUpload($attach, $msg, $_rr_id);
			return true;
		}		
		
		if ($type == 'checkin') { //签到同步 签到同步不支持图片
			return self::checkinsCheckin($_rr_id, $msg);
		}
		
		return self::statusSet($msg, $_rr_id);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#delete($id, $mode)
	 */
	public function delete($id)
	{
		return false;	
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
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#checkPost($return)
	 */
	public function checkPost($text)
	{		
		return false;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#getThirdUserName($username, $password)
	 */
	public function getThirdUserName($parms)
	{	
		$return_username = '';
		
		$id = $parms['tid'];
		
		$this->_getSessionKey();
		$usersInfo = $this->usersGetInfo($id);
		
		$json = $usersInfo[0];
		if ($json->uid) {
			$return_username = "{$json->name}($json->uid)";
		}
		
		return $return_username;
	}
	
	
	public function getThirdUserNick($username, $password, $accecss_token, $accecss_token_secret)
	{
		$return_username = '';

		return $return_username;
	}
	
	/**
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		$followers_count = 0;		
		return $followers_count;		
	}
	
	/**
	 * 得到好友
	 */
	public function getFriends()
	{
		$ids = array();
		
		$this->_getSessionKey();
		$data = array();
		$data['method'] = 'friends.get';
		
		$param = $this->_buildQuery($data, $this->_skey);
		$r = self::_httpPost($this->_api_url, $param);		
		if ($r === false) return false;
		
		$ids = json_decode($r);
		
		return $ids;
	}
	
	/**
	 * 
	 */
	public function getFollowerids()
	{
		$ids = array();
		return $ids;		
	}	
	
	
	
	/**
	 * 增加关注用户
	 */
	public function friendsAdd($name='kaierkaier')
	{
		
		return false;		
	}
	
	/**
 * 
	 * @return unknown_type
	 */
	public function getAvatar()
	{

	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#get3rdId()
	 */
	public function get3rdId()
	{
		return '';
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function getToken()
	{
		$r = array();

		$g['grant_type'] = 'password';
		$g['username'] = $this->_username;
		$g['password'] = $this->_password;
		$g['client_id'] = $this->_akey;
		$g['client_secret'] = $this->_skey;
		$g['scope'] = 'publish_checkin publish_feed photo_upload status_update';
		
		$url = 'https://graph.renren.com/oauth/token?' . http_build_query($g);
		$str = file_get_contents($url);	
		$json = json_decode($str);
		
		if ($json->access_token) {
			$r = array($json->access_token, $json->refresh_token, $json->expires_in);
		}
		
		return $r;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see library/Better/Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#fakeLogin($uid)
	 */
	public function fakeLoginApi()
	{	
		$_tokens = $this->getToken();
		list($access_token, $refresh_token, $expires_in) = $_tokens;
		
		if ( $access_token ) {
			// tid 赋值
			$_temp_a = explode('-', $access_token);
			$tid = $_temp_a[1];
			$this->tid = $tid;		
			$this->_accecss_token = $access_token;
			$this->_accecss_token_secret = $refresh_token;
			$this->_expires_in = $expires_in;
			$this->_getSessionKey();
		} 
		
		
		return $_tokens;
	}		
}
