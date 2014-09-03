<?php

/**
 * 位置相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_LocationController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
		
		$this->auth();
	}	

	/**
	 * 12 定位
	 * 
	 * @return
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'location';
		$this->needPost();
		
		$hex_data = $this->post['data'];
		$ver = $this->post['ver'];
		if ($ver!='0.2') {
			//$ver = '0.1';
		}
		
		$this->user->cache()->set('lbs_last_ver', $ver);
		
		if ($hex_data) {
			$app_lbs = Better_Service_Lbs::getInstance();
			$xml = "<location ver='".$ver."' vendid='".$this->config->lbs->api_key."' os='win' from='better' id='".$this->uid."'><locate hex='$hex_data'></locate><ip>".Better_Functions::getIP()."</ip></location>";
			
			$app_lbs->getLL($xml, $this->uid);
			$this->data[$this->xmlRoot] = array();
			
			if (!$app_lbs->error) {
	
				$app_geoname = new Better_Service_Geoname();
				$geo_info = $app_geoname->getGeoName($app_lbs->lon,$app_lbs->lat);
				
				$address = '';
				if (trim($geo_info['r1']) && trim($geo_info['r2'])) {
					$address = str_replace('{NO1}', $geo_info['r1'], $this->lang->poi->place->string);
					$address = str_replace('{NO2}', $geo_info['r2'], $address);
				}
				
				$this->user->cache()->set('lbs_last_ver', $ver);
				
				$this->data[$this->xmlRoot] = array(
					'lon' => $app_lbs->lon,
					'lat' => $app_lbs->lat,
					'time' => $app_lbs->time,
					'range' => $app_lbs->range,
					'city' => $geo_info['name'],
					'address' => $address,
					);
			} else if (!preg_match('/<location(.+)/is', $app_lbs->message)) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error($app_lbs->message);
			}		
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.location.invalid_data');
		}
		
		$this->output();
	}
}