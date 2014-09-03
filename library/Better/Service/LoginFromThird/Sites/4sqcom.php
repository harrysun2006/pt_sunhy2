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
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.foursquare.com/v1/user';
		$this->_api_url = 'http://api.foursquare.com/v1/checkin';
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
		}
		
		return $poiId4sq;
	}

	public function post($msg, $attach='', $poiId=0)
	{
		$flag = false;
		$poiId4sq = 0;
		$tried4sq = false;
		
		if ($poiId) {
			$poiId4sq = Better_DAO_Poi_4sq::getInstance()->get4sqId($poiId);
			if (!$poiId4sq) {
				$tried4sq = true;
				$poiId4sq = $this->_createNewPoi($poiId);
			}
		}
		
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
}