<?php

/**
 * 位置相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Public_PlaceController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
	}	

	/**
	 * 12 定位
	 * 
	 * @return
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'place';
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		
		if ($lon && $lat) {
			$tmp = Better_LL::parse($lon, $lat);
			$lon = $tmp['lon'];
			$lat = $tmp['lat'];
			
			$geo = new Better_Service_Geoname();
			$geoInfo = $geo->getGeoName($lon, $lat);
			
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
				$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
				$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}

			$this->data[$this->xmlRoot] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));			
		}		
		
		$this->output();
	}
}