<?php
/**
 * 开心API
 * @author Jeff
 *
 */
class Better_Service_LoginFromThird_Sites_Kaixin001com extends Better_Service_LoginFromThird_Common
{
	public $_session_key = '';
	
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = '';
		$this->_api_url = 'http://rest.kaixin001.com/api/rest.php';
    	$this->_accecss_token = $accecss_token;
        $this->_accecss_token_secret = $accecss_token_secret;
        $this->_username = $username;
        $this->_password = $password;
        
        
        $this->api_key = Better_Config::getAppConfig()->oauth->key->kaixin001_akey;	
        //$this->api_key = '624121267795ca6700507845dacb15a6';	
        $this->secret = Better_Config::getAppConfig()->oauth->key->kaixin001_skey;
        //$this->secret = '651982147b37a3e5cf8ea3964dc70cd8';
        $this->_protocol = 'kaixin001.com';	
	}
	
	public function __destruct()
	{

	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _initCurl()
	{
		$ch = curl_init($this->_api_url);	
		$options = array(
						CURLOPT_HEADER => 0,
						CURLOPT_FOLLOWLOCATION => 1,
						CURLOPT_NOBODY => 0,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_USERAGENT => 'kaixin001.com API PHP5 Client 1.1 (curl) ',
						CURLOPT_CONNECTTIMEOUT => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_AUTOREFERER => true,
						);		
		curl_setopt_array($ch, $options);

		return $ch;	
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _login()
	{
		$url = 'http://wap.kaixin001.com/home/';
		$request = array(
			'bind' => '',
			'email' => $this->_username,
			'from' => '',
			'gotourl' => '',
			'login' => '登 录',
			'password' => $this->_password,
			'refcode' => '',
			'refuid' => 0,
			'remember' => 1,
			);
		
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer', 'http://wap.kaixin001.com/');
		$client->request(Zend_Http_Client::POST);
		$html = $client->getLastResponse()->getBody();
		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if ($this->_cookies['_user']!='' && $this->_cookies['_uid']!='')
		{
			$this->_logined = true;
		}

		return $this->_logined;	
	}
	
	
	/**
	 * 
	 */
	public function login()
	{
		return true;
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function fakeLogin($uid=0)
	{	
		if ($uid) return true;
		if ($this->_getSessionKey(0)) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * 得到sessionKey
	 * 
	 */
	public function _getSessionKey($isBind=0)
	{
		$ch = $this->_initCurl();		
		$upwd = md5($this->_password . $this->secret);
		$param = array( 
					'api_key' => $this->api_key, 
					'method' => 'users.getSessionKey', 
					'uname' => $this->_username, 
					'upwd' => $upwd	,  
					'session_key' => 1	,  
					);
		$param['call_id'] = microtime(true) + 500; 
		$param['v'] = '1.0';
		$query = self::_buildQuery($param, $this->secret);					
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
		$result = curl_exec($ch); 
		$error = curl_error($ch);
		$info = curl_getinfo($ch);
		$json = json_decode($result);
		curl_close($ch);
		
		if ($isBind && !$json->result) {
			$log_str = $this->_username . '|' . $this->_password . '|' . $result;
			Better_Log::getInstance()->logAlert($log_str, 'bindsns_fail_kaixin001');
		}
		$this->_session_key = $json->result;
		if ($json->result) {
			$this->tid = $this->_getUid($this->_session_key);
		}		
		return $json->result;		
	}
	
	/**
	 * 
	 */
	public function getInfo()
	{
		$return = array();
		$_uid = $this->_getUid($this->_session_key);
		$_user_info = $this->_getInfo($this->_session_key, $_uid);
		if ($_user_info) {
			$return['uid'] = $_user_info[0]->uid;
			$return['nickname'] = $_user_info[0]->name;
			$return['gender'] = $_user_info[0]->gender == 0 ? 'male' : 'female';
			$return['image_url'] = $_user_info[0]->logo50;
			$return['imgurl'] = $_user_info[0]->logo50;
			//$return['online'] = $_user_info[0]->online;
		}
		return $return;
	}
	
	/**
	 * session 的UID
	 */
	public function _getUid($session_id)
	{
		$a_temp = explode('_', $session_id);
		return $a_temp[0];			
	}
	
	/**
	 * 
	 */
	public function _getInfo($session_id, $uid)
	{
		$ch = $this->_initCurl();		
		$param = array( 
					'api_key' => $this->api_key, 
					'method' => 'users.getInfo', 
					'uids' => $uid, 
					'session_key' => $session_id, 
					);
		$param['call_id'] = microtime(true) + 500; 
		$param['v'] = '1.0';
		$query = self::_buildQuery($param, $this->secret);					
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
		$result = curl_exec($ch); 
		$error = curl_error($ch);
		$info = curl_getinfo($ch);
		$json = json_decode($result);
		curl_close($ch);
		return $json;		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Base#post($msg, $attach)
	 */	
	public function post($msg, $attach='', $poiId='', $geo='')
	{				
		if (!$geo['lon'] || !$geo['lat']) {
			//return $this->_post($msg);
			return $this->postRecord($msg);
		}
		
		$ch = $this->_initCurl();
		$session_key = $this->_getSessionKey();
		$api_key = $this->api_key;
		
		$poi = Better_Poi_Info::getInstance($poiId);
		$poiInfo = $poi->getBasic();
		$location = $poiInfo['name'] . ' (' . $poiInfo['address'] . ')';
		
		$country = '中国';
		$province = $poiInfo['province'];
		$city = $poiInfo['city'];
		if (!$city || !$province) {
			$lon = $geo['lon'];
			$lat = $geo['lat'];
			$app_geoname = new Better_Service_Geoname();
			$city = $app_geoname->getBigcityname($lon, $lat);
			if ($city[0]['name']) {
				$city = $city[0]['name'];
			} else {
				$city = 'City';
			}
			$province = $app_geoname->getProvinceByCity($city);
		}
		
		
		$param = array( 
					'api_key' => $api_key, 
					'method' => 'users.sendLBS', 
					'format' => 'json', 
					'session_key' => $session_key, 
					'content' => $msg, 
					'longitude' => $lon, 
					'latitude' => $lat, 
					'location' => $location, 
					'country' => $country, 
					'province' => $province, 
					'city' => $city, 
					'from_key' => 'kaikai', 
					'notify_friend' => '1', 
					'coop_link' => 'http://k.ai', 
					);
		if ($attach) $param['raw_request'] = 1;			
		$param['call_id'] = microtime(true) + 500; 
		$param['v'] = '1.0';
		$query = self::_buildQuery($param, $this->secret);
print_r($param);		
		if ($attach) {
			$_type = self::_getFileType($attach);
			$query['upload_pic'] = "@$attach;type=$_type"; //
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:") );
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
		}
		$result = curl_exec($ch); 
		$error = curl_error($ch);
		$info = curl_getinfo($ch);

		$json = json_decode($result);
var_dump($result);		
		curl_close($ch);
		return $json->result;
	}
	
	/**
	 * 
	 */
	public function postRecord($msg)
	{
		$ch = $this->_initCurl();
		$session_key = $this->_getSessionKey();
		$api_key = $this->api_key;
		
		$param = array( 
					'api_key' => $api_key, 
					'method' => 'users.sendRecord', 
					'format' => 'json', 
					'session_key' => $session_key, 
					'content' => $msg, 
					'from_key' => 'kaikai', 
					);
		$param['call_id'] = microtime(true) + 500; 
		$param['v'] = '1.0';
		$query = self::_buildQuery($param, $this->secret);
print_r($param);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
		
		$result = curl_exec($ch); 
		$error = curl_error($ch);
		$info = curl_getinfo($ch);

		$json = json_decode($result);
var_dump($result);		
		curl_close($ch);
		return $json->result;		
	}
	
	/**
	 * 发送单独的吼吼
	 */
	public function _post($msg)
	{				
		$this->_logined==false && $this->_login();
$log_array = array();
$log_array[] = $this->_username;
$log_array[] = $this->_password;

		if ($this->_logined == true) {
			$url = 'http://wap.kaixin001.com/record/write.php';
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->request();
			$html = $client->getLastResponse()->getBody();			
		    preg_match('<form method="post" action="(.*)" style="margin:0">', $html, $matches);	
		    $url = "http://wap.kaixin001.com" . $matches[1];	
$log_array[] = "url:$url";
			
			$request = array(
				'content' => $msg,
				'privacy' => '1',
				
				);							
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->setParameterPost($request);
			$client->setHeaders('Referer', 'http://wap.kaixin001.com/record/write.php');
			$client->request(Zend_Http_Client::POST);


			$html = $client->getLastResponse()->getBody();
			$log_array[] = "httpcode:" . $client->getLastResponse()->getStatus();			
			$log_str = implode('||' ,$log_array);
			Better_Log::getInstance()->logAlert($log_str, 'kaixin001', true);			
			
			$ok = true;
			return $ok;
		}
		
		return $ok;
	}
	
	/**
	 * 
	 * @param $param
	 * @param $secret
	 * @return unknown_type
	 */
	public static function _buildQuery($param, $secret) 
	{ 
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
	 * 返回消息的ID
	 */
	public function get3rdId()
	{
		return 0;
	}	
	
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
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		return 0;		
	}	
	
}