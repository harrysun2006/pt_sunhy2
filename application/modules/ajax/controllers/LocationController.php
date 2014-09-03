<?php

/**
 * 位置相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_LocationController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}	
	
	/**
	 * 在Poi内报到
	 * 
	 * @return
	 */
	public function poicheckinAction()
	{
		$params = $this->getRequest()->getParams();
		$params['source'] = 'web';
		
		if (BETTER_HASH_POI_ID) {
			$params['poi_id'] = Better_Poi_Info::dehashId($params['poi_id']);
		}
		
		$result = $this->user->checkin()->checkin($params);

		$this->output = array_merge($this->output, $result);
		$this->processRightbar();
		
		$this->output();
	}
	
	public function findoutAction()
	{
		
	}
	
	public function checkinAction()
	{
		$uid = $this->uid;
		$lon = floatval(trim($this->getRequest()->getParam('lon')));
		$lat = floatval(trim($this->getRequest()->getParam('lat')));
		$address = trim($this->getRequest()->getParam('address', ''));
		$range = floatval(trim($this->getRequest()->getParam('range')));
		
		Better_Registry::get('user')->updateUser(array(
			'lon' => $lon,
			'lat' => $lat,
			'address' => $address,
			'places' => $this->userInfo['places']+1,
			'range' => $range,
			), true);
			
		$newUserInfo = Better_Registry::get('user')->getUser();
		
		$this->output['lon'] = $lon;
		$this->output['lat'] = $lat;
		list($this->output['x'], $this->output['y']) = Better_Functions::LL2XY($lon, $lat);
		$this->output['address'] = $address ? $address : $newUserInfo['address'];
		$this->output['city']  = $newUserInfo['city'];
		$this->output['lbs_report'] = time();
		$this->output['range'] = $range;
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 根据Wifi地址取位置
	 * 
	 * @return
	 */
	public function wifiAction()
	{
		$user = $this->user->getUserInfo();

		$wifi_data = trim(implode('',file("php://input")));
		if (!$wifi_data) $wifi_data = 'BBADAwAjiU+fIL4bAhy/AAEisggA7gcCCufFBAIM8T3H1ccA==';//空数据
		$ip = Better_Functions::getIP();
		$xml = "<location ver='0.1' vendid='".$this->config->lbs->api_key."' os='win' from='better' id='".$this->uid."'><locate data='$wifi_data'></locate><ip>$ip</ip></location>";

		$lbs = Better_Service_Lbs::getInstance();
		$lbs->getLL($xml);
		
		$range = $lbs->range;
		$lon = $lbs->lon;
		$lat = $lbs->lat;
		$msg = '';
		$addressAlert = 0;

		if ($user['lbs_report']) {
			$d = Better_Service_Lbs::getDistance($user['lon'], $this->userInfo['lat'], $lon, $lat);
			if ($d>$range) {
				$msg = $this->lang->global->lbs->big_offset;
				$addressAlert = 1;
			}
		} else {
			$msg = $this->lang->global->lbs->checked_in;
			$addressAlert = 1;
		}
		
		$this->output['alert'] = $addressAlert;
		$this->output['lon'] = $lon;
		$this->output['lat'] = $lat;
		$this->output['range'] = $range;
		$this->output['msg'] = $msg;
		$this->output['error'] = $lbs->error;
		$this->output['message'] = $lbs->message;

		$this->output();
	}	
	
	public function mixAction()
	{
		$lon = $this->getRequest()->getParam('lon', -1);
		$lat = $this->getRequest()->getParam('lat', -1);
		
		if (Better_LL::isValidLL($lon, $lat)) {
			$tmp = Better_LL::parse($lon, $lat);
			$lon = $tmp['lon'];
			$lat = $tmp['lat'];
		}
		
		$this->output['lon'] = $lon;
		$this->output['lat'] = $lat;
		
		$this->output();
	}
	
	
	/**
	 * 根据经纬度得到位置
	 */
	public function getaddressbyllAction(){
		$lon = $this->getRequest()->getParam('lon', -1);
		$lat = $this->getRequest()->getParam('lat', -1);
		
		$address = '';
		if (Better_LL::isValidLL($lon, $lat)) {
			$app_geoname = new Better_Service_Geoname();
			$cityname = $app_geoname->getCityName($lon, $lat);
			$geo_info = $app_geoname->getGeoName($lon, $lat);
			
			if (trim($geo_info['r1']) && trim($geo_info['r2'])) {
				$address = str_replace('{NO1}', $geo_info['r1'], $this->lang->api->poi->place->string);
				$address = str_replace('{NO2}', $geo_info['r2'], $address);
			}
		}
		
		$this->output['address'] = $address;
		$this->output['cityname'] = $cityname;
		
		$this->output();
	}
}