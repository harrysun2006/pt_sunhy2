<?php

/**
 * 抄送到foursquare.com
 * 
 * @package Better.Service.PushToOtherSites.Sites
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Service_PushToOtherSites_Sites_4sqcom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'www.foursquare.com';
	/*
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		
		$this->_login_url = 'https://foursquare.com/oauth2/access_token';
		//$this->_login_url = 'http://api.foursquare.com/v1/user';
		$this->_api_url = 'http://api.foursquare.com/v1/checkin';
	}
	*/
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		//$this->setUserPwd($username,$password);
		Better_Log::getInstance()->logInfo($username."--".$password."--".$accecss_token."--".$accecss_token_secret, "4sq_sync");
		$this->_login_url = 'https://foursquare.com/oauth2/access_token';
		//$this->_api_url = 'http://open.t.qq.com/api/t/add';
		$this->_accecss_token = $accecss_token;
		$this->_accecss_token_secret = $accecss_token_secret;		
		$this->_akey = Better_Config::getAppConfig()->oauth->key->foursq_akey;
		$this->_skey = Better_Config::getAppConfig()->oauth->key->foursq_skey;		
		$this->_protocol = '4sq.com';
	}
	
	
	
	
	
	
	/**
	 * 新建一个POI
	 * 
	 * @return integer
	 */
	private function _createNewPoi($poiId)
	{
		$poiId4sq = 0;
		
		$poi = Better_Poi_Info::getInstance($poiId);
		$poiInfo = $poi->getBasic();
		if ($poiInfo['poi_id']) {			
			$url = "https://api.foursquare.com/v2/venues/add";
			$params = array(
				'oauth_token'=>$this->_accecss_token,
				'name' => $poiInfo['name'], 
				'crossStreet' => $poiInfo['address'], 
				'city'=>$poiInfo['city'], 
				'state'=>$poiInfo['city'], 
				'zip' => $poiInfo['name'], 
				'phone' => $poiInfo['phone'], 
				'll'=>$poiInfo['lat'].",".$poiInfo['lon'], 
				'primaryCategoryId'=>''
			);
			$ch  = curl_init($url);
			if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			}else {						
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			if(isset($_SERVER ['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] != '127.0.0.1'){
			    curl_setopt($ch, CURLOPT_INTERFACE, $_SERVER ['SERVER_ADDR']);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			$result=curl_exec($ch);					
			$info=curl_getinfo($ch);
			$json = json_decode($result);			
			$poiId4sq = $json->response->venue->id;
			
			if($info['http_code']==200){
				
				if ($poiId4sq) {
					Better_DAO_Poi_4sq::getInstance()->save($poiId, $poiId4sq);
				} else {
					Better_Log::getInstance()->logAlert("4Sq_poi_create_failed:[".$result."]", "4sq_exception");
				}
			} else if($info['http_code']==400){
				$newstr = $json->meta->errorDetail;
				$poiId4sq = trim(str_replace('Possible duplicate venue, e.g.','',$newstr));
				Better_Log::getInstance()->logAlert("--4Sq_poi_create_failed:[".$result."]", "4sq_exception");
			} else {
				Better_Log::getInstance()->logAlert($info['http_code']."--4Sq_poi_create_failed:[".$result."]", "4sq_exception");
			}
			/*
			$authCredentials = base64_encode($this->_username.':'.$this->_password);
			$request[] = "POST http://api.foursquare.com/v1/addvenue?name=".$poiInfo['name']."&address=".$poiInfo['address']."&phone=".$poiInfo['phone']."&city=".$poiInfo['city']."&state=中国&geolat=".$poiInfo['lat']."&geolong=".$poiInfo['lon']."&primarycategoryid= HTTP/1.1";
			$request[] = "Host: ".$this->_host;
			$request[] = "Content-Type: application/x-www-form-urlencoded";
			$request[] = "Authorization: Basic {$authCredentials}";
			$request[] = "Connection: Close";
	
			$socket5 = Better_Proxy::getSocket($this->_host, 80);
			if($socket5 instanceof Better_Socket5) {
				$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");

				try {
					preg_match_all('#<id>([0-9]+)</id>#is', $html, $all);
					$poiId4sq = $all[1][0];

					if ($poiId4sq) {
						Better_DAO_Poi_4sq::getInstance()->save($poiId, $poiId4sq);
					} else {
						Better_Log::getInstance()->logAlert("4Sq_poi_create_failed:[".$html."]", "4sq_exception");
					}
				} catch(Exception $e) {

				}					
				
				unset($socket5);
			}	
			*/		
		}
		
		return $poiId4sq;
	}

	public function post($msg, $attach='', $poiId=0)
	{
		$flag = false;
		$poiId4sq = '';
		$tried4sq = false;
		
		if ($poiId) {
			$poiId4sq = Better_DAO_Poi_4sq::getInstance()->get4sqId($poiId);
			Better_Log::getInstance()->logInf("原始的:".$poiId4sq,'4sqpoi');			
			if($poiId4sq){
				$poiId4sq = self::checkpoi($poiId4sq);
				Better_Log::getInstance()->logInf("获得的:".$poiId4sq,'4sqpoi');
			}
			
			if (!$poiId4sq) {
				$tried4sq = true;
				$poiId4sq = $this->_createNewPoi($poiId);
				Better_Log::getInstance()->logInf("创建的:".$poiId4sq,'4sqpoi');
			}
		}
		
		
		$url = "https://api.foursquare.com/v2/checkins/add";
		$params = array(
		'oauth_token'=>$this->_accecss_token,
		'venueId' => $poiId4sq,
		'broadcast' => 'public',
		'shout'=> $msg
		);
		
		$ch  = curl_init($url);
		if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		}else {						
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		if(isset($_SERVER ['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] != '127.0.0.1'){
		    curl_setopt($ch, CURLOPT_INTERFACE, $_SERVER ['SERVER_ADDR']);
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		$result=curl_exec($ch);					
		$info=curl_getinfo($ch);		
		if($info['http_code']==200){
			$flag = true;			
			if($attach){
				try{
					$json = json_decode($result,true);
					$tempuid = $json['notifications'][3]['item']['leaderboard'][0]['user']['id'];
					$tempid = $json['response']['checkin']['id'];					
					$photourl = "https://api.foursquare.com/v2/photos/add";					
					$imgparams = array(
						'oauth_token'=>$this->_accecss_token,
						'checkinId' => $tempid, 
						'broadcast' => 'public',				
						'file' => "@".$attach
					);					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $photourl);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $imgparams);				
					$imgresult = curl_exec($ch);	
					Better_Log::getInstance()->logInfo($imgresult,'4sqattach');		
				} catch(Exception $ee){
					
				}
			}
			
		} else{
			Better_Log::getInstance()->logAlert("4sq:[".$result."]", "4sq_sync");
		}
		
		
		
		
		
		/*
		$authCredentials = base64_encode($this->_username.':'.$this->_password);
		$query = '';
		if ($poiId4sq) {
			$query .= '&vid='.$poiId4sq;
		}
		$request[] = "POST ".$this->_api_url."?shout=".urlencode($msg).$query." HTTP/1.1";
		$request[] = "Host: ".$this->_host;
		$request[] = "Content-Type: application/x-www-form-urlencoded";
		$request[] = "Authorization: Basic {$authCredentials}";
		$request[] = "Connection: Close";

		$socket5 = Better_Proxy::getSocket($this->_host, 80);
		if($socket5 instanceof Better_Socket5) {
			$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");
			$flag = $this->checkPost($html);
			
			unset($socket5);
		}
		*/
		
		return $flag;	
	}
	
	public function login()
	{
		return $this->fakeLogin();
	}
	
	public function fakeLogin()
	{
		$authCredentials = base64_encode($this->_username.':'.$this->_password);

		$request = array();
		$request[] = "GET ".$this->_login_url." HTTP/1.1";
		$request[] = "Host: ".$this->_host;
		$request[] = "Content-Type: application/x-www-form-urlencoded";
		$request[] = "Authorization: Basic {$authCredentials}";
		$request[] = "Connection: Close";

        $logined = false;
        $socket5 = Better_Proxy::getSocket($this->_host, 80);

        if ($socket5 instanceof Better_Socket5) {
			$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");
			try {
				if (preg_match('/<id>(.+)<\/id>/', $html)) {
					$logined = true;
				}
			} catch(Exception $e) {

			}	

			unset($socket5);
        }
        
		return $logined;		
	}
	
	public function checkPost($html)
	{
		if (strpos($html, '<id>')) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get3rdId()
	{
		return 0;
	}	
	public function gottoken($callback,$code)
	{
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->foursq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->foursq_skey;
		$params = array("client_id"=>$WB_AKEY,
						"client_secret"=>$WB_SKEY,
						"grant_type"=>"authorization_code",
						"redirect_uri"=>$callback,
						"code"=>$code);
		Better_Log::getInstance()->logInfo(serialize($params),'4sq');	
		$url = "https://foursquare.com/oauth2/access_token";
		if(!empty($params) && $params){
			foreach($params as $k=>$v) $kv[] = "$k=$v";
			$url_params = str_replace(" ","+",implode('&',$kv));
			$url = trim($url) . '?' . $url_params;
		}		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		}else {						
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
		}
		curl_setopt($ch , CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);			
		$result=curl_exec($ch);					
		$info=curl_getinfo($ch);
		$json = json_decode($result);
		$access_token = $json->access_token;
		Better_Log::getInstance()->logInfo(serialize($json),'4sq');
		curl_close($ch);
		return  $access_token;
	}
	public function checkpoi($venueid){
		$url = "https://api.foursquare.com/v2/venues/".$venueid."?oauth_token=".$this->_accecss_token;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		}else {						
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
		}
		curl_setopt($ch , CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);			
		$result=curl_exec($ch);					
		$info=curl_getinfo($ch);
		$json = json_decode($result);
		$newfourid = $json->response->venue->id;
		Better_Log::getInstance()->logInfo($result,'4sq');
		return $newfourid;
	} 
	
}