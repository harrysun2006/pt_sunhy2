<?php

/**
 * GeoCoding
 * Google提供的服务，将经纬度提交给google，google返回地址、城市等信息
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_Geocoding
{
	
	public static function request($address)
	{
		$result = array(
			'lon' => 0,
			'lat' => 0,
			'city' => '',
			'province' => '',
			'country' => '',
			);

		if ($address) {
			$url = 'http://ditu.google.cn/maps/geo?key=ABQIAAAAz2EYq_GNa47dPkdP-6nRixThnWgwAfIozEyIOiZ15mj0OMKvahRVNoJqYZPBWjUNqHRmCrcDysiTvw&sensor=false&output=json';
			$url .= '&q='.urlencode($address);
			
			$client = new Zend_Http_Client($url, array());
			$client->request();
			$html = $client->getLastResponse()->getBody();			
			
			try {
				$data = json_decode($html);

				if ($data->Status->code=='200') {
					$result['lon'] = $data->Placemark[0]->Point->coordinates[0];
					$result['lat'] = $data->Placemark[0]->Point->coordinates[1];
					$result['country'] = $data->Placemark[0]->AddressDetails->Country->CountryName;
					$result['province'] = $data->Placemark[0]->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName;
					$result['city'] = $data->Placemark[0]->AddressDetails->Country->AdministrativeArea->Locality->LocalityName;
				}
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert('Google Geocoding Failed:['.$address.']', 'geocoding');
			}
		}
		
		return $result;
	}
}