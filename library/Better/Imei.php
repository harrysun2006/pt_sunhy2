<?php

/**
 * Imei号记录
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Imei
{
	
	public static function save(array $params)
	{
		if ($params['imei']) {
			$data = array();
			$data['imei'] = $params['imei'];
			$data['action'] = $params['action'];

			if (isset($params['uid']) && isset($params['partner'])) {
				$data['last_active'] = time();
				$data['uid'] = (int)$params['uid'];
				$data['partner'] = $params['partner'];
				$data['version'] = $params['version'];
				$data['platform'] = $params['platform'];
				$data['model'] = $params['model'];
			} else if (isset($params['reg_uid']) && isset($params['reg_partner'])) {
				$data['reg_last_active'] = time();
				$data['reg_uid'] = (int)$params['reg_uid'];
				$data['reg_partner'] = $params['reg_partner'];
				$data['reg_version'] = $params['reg_version'];
				$data['reg_platform'] = $params['reg_platform'];
				$data['reg_model'] = $params['reg_model'];				
			}
			
			Better_DAO_Imei::getInstance()->save($data);
		}
	}
	
	public static function saveMirror(array $params)
	{
		if ($params['imei']) {
			$data = array();
			$data['imei'] = $params['imei'];
			$data['action'] = $params['action'];

			if (isset($params['uid']) && isset($params['partner'])) {
				$data['last_active'] = time();
				$data['uid'] = (int)$params['uid'];
				$data['partner'] = $params['partner'];
				$data['version'] = $params['version'];
				$data['platform'] = $params['platform'];
				$data['model'] = $params['model'];
			} else if (isset($params['reg_uid']) && isset($params['reg_partner'])) {
				$data['reg_last_active'] = time();
				$data['reg_uid'] = (int)$params['reg_uid'];
				$data['reg_partner'] = $params['reg_partner'];
				$data['reg_version'] = $params['reg_version'];
				$data['reg_platform'] = $params['reg_platform'];
				$data['reg_model'] = $params['reg_model'];				
				
				//	更新主表的reg信息
				Better_DAO_Imei::getInstance()->updateByCond(array(
					'reg_uid' => $data['reg_uid'],
				), array(
					'imei' => $data['imei']
					));
			}
			
			Better_DAO_Imei_Mirror::getInstance()->save($data);
		}		
	}
	
	public static function partnerRate($partner)
	{
		$rate = 1;
		if (strlen($partner)==7) {
			$partner = substr($partner, 2, 4);
		}
		
		if (Better_DAO_Imei_Logs::getInstance()->getPartnerRegCount($partner)>10) {		
			$row = Better_DAO_Imei_Rate::getInstance()->get($partner);
			if (isset($row['rate'])) {
				$rate = $row['rate'];	
			}
		}
		
		return $rate;
	}
	
	public static function exists($imei)
	{
		$row = Better_DAO_Imei::getInstance()->get($imei);
		$exists = false;
		
		if (isset($row['imei']) && $row['imei']) {
			$exists = true;
		}
		
		return $exists;
	}
	
	public static function decrypt($secret)
	{
		$key = Better_Config::getAppConfig()->aes->key;
		$secret = Better_Functions::hex2bin($secret);
		$imei = trim(mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, $secret, MCRYPT_DECRYPT));
		
		return $imei;
	}
	

}