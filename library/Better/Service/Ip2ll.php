<?php

class Better_Service_Ip2ll
{
	
	public static function parse($ip='')
	{
		$result = array(
			'lon' => Better_Config::getAppConfig()->location->default_lon,
			'lat' => Better_Config::getAppConfig()->location->default_lat
			);
		
		$ip =='' && $ip = Better_Functions::getIP();

		$tmp = Better_DAO_Ipcityll::getInstance()->getLL($ip);
		
		if ($tmp['lon'] && $tmp['lat']) {
			$result = &$tmp;
		}
		return $result;
	}
}