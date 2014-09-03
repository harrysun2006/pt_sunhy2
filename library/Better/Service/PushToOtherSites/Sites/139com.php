<?php
/**
 * Follow5 
 * @author Jeff
 *
 */
class Better_Service_PushToOtherSites_Sites_139com extends Better_Service_PushToOtherSites_Common 
{

	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->apiKey = Better_Config::getAppConfig()->shuoke_api->key;
		$this->apiSecret = Better_Config::getAppConfig()->shuoke_api->secret;
		
		$this->_protocol = '139.com';
	}
	
	public function fakeLogin($uid='')
	{	
		$this->_uid = $uid;
		return true;
	}
	
	
	public function post($msg, $attach='')
	{
		$app_account = $this->_uid;
		$miop = new Better_Miop_Main($this->apiKey, $this->apiSecret);
		$session = $miop->api_client->connect_getSession($app_account);
		$session_key = $session['key'];	
		$miop2 = new Better_Miop_Main($this->apiKey, $this->apiSecret, $session_key);
		
		if ($attach) {
			$pic_data = base64_encode(file_get_contents($attach));
			$file_name = basename($attach);
			$r = $miop2->api_client->italk_sendPic($pic_data, $file_name, $msg);
			list($error, $id) = $r;
			if (!$error) $result = true;
		} else {
			$r = $miop2->api_client->italk_send($msg);
			var_dump($r);
			list($error_code, $result, $id) = $r;
			var_dump($result);		
		}
		if ($result) {
			$this->third_id = $id;
			return true;
		} else {
			$log_str = '139' . '||' . $app_account . '||' . serialize($session) . '||' . serialize($r);
			$this->_log($log_str, $this->_protocol);			
		}
		
		return false;
	}
	
	public function delete($id)
	{
		$app_account = $this->_uid ;
		$miop = new Better_Miop_main($this->apiKey, $this->apiSecret);
		$session = $miop->api_client->connect_getSession($app_account);
		$session_key = $session['key'];
		
		$miop2 = new Better_Miop_main($this->apiKey, $this->apiSecret, $session_key);
		$infos = $miop2->api_client->italk_delete($id);
		
		if ( $infos && $infos[0] == 1 ) {
			return true;
		}
var_dump($infos);
		return false;
	}	
	
	public function get3rdId()
	{
		return $this->third_id;
	}	
	
}