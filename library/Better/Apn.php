<?php

/**
 * 苹果APN
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Apn
{
	public static function connect()
	{
		$config = Better_Config::getAppConfig();
		$apnsHost = $config->apn->host;
		$apnsPort = $config->apn->port;
		$apnsCert = dirname(__FILE__).'/../../data/'.$config->apn->pem->file;
		$apnsCertPwd = $config->apn->pem->pass;
		
		$streamContext = stream_context_create();
		stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
		stream_context_set_option($streamContext, 'ssl', 'passphrase', $apnsCertPwd);
		$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 10, STREAM_CLIENT_CONNECT, $streamContext);

		if (!$apns) {
			$_log_filename = 'apn';
			Better_Log::getInstance()->logAlert('APN_Failed:['.$error.'], String:['.$errorString.']', $_log_filename, true);			
		}
		
		return $apns;
	}
	
	
	public static function push($params, $apns)
	{
		$deviceToken = $params['token'];
		
		$uid = (int)$params['uid'];
		if ($uid) {
			$_key_name = 'apns_count';
			$badge = (int)Better_User::getInstance($uid)->cache()->get($_key_name);
		} else {
			$badge = 1;
		}
		
		$push = array();
		$push['aps'] = array(
			'alert' => $params['msg'],
			'badge' => $badge,
			'sound' => 'default'
			);
		$payload = json_encode($push);
		
		if ($apns) {
			$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
			fwrite($apns, $apnsMessage);
		} else {
			return false;
		}
		
		return true;
	}
}