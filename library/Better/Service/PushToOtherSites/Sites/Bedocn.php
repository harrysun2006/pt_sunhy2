<?php

class Better_Service_PushToOtherSites_Sites_Bedocn extends Better_Service_PushToOtherSites_Common
{
	protected $apiKey = '';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		
		$this->apiKey = Better_Config::getAppConfig()->sina_api->key;
		
		$this->_login_url = 'http://www.bedo.cn/api/mobile/allot.dll';
		$this->_api_url = 'http://www.bedo.cn/api/mobile/a.php';
		$this->_protocol = 'bedo.cn';
	}
	
	public function fakeLogin($uid='')
	{
		$logined = false;
		
		$client = new Zend_Http_Client($this->_login_url, array(
			'keepalive' => true,
			));

		$xml = "<?xml version='1.0' encoding='utf-8'?><allot jid='$this->_username' pass='$this->_password' platform='1' net='net' ver='2.1'/>";	
		$client->setRawData($xml, 'text/xml')->request('POST');
		$html = $client->getLastResponse()->getBody();
		$status = $client->getLastResponse()->getStatus();
		
		if ($status == 200) {
			$dom = new DOMDocument();
			$o = @$dom->loadXML($html);
			if (!$o) {
				return false;
			}
			$allot = $dom->getElementsByTagName('allot')->item(0);			
			$logined = $allot->getAttribute("result") == 'OK' ? true : false;
		}	

		return $logined;
	}	
	
	/**
	 * 提交的参数赋值
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		$flag = false;
		
		$pep = $this->_username;
		$time = 'time';
		$action = 'post';
		$key = "peptalk is best";
		$string = md5($pep . $time . $action . $key);	

		list($text, $link) = explode('BBEEDDOO', $msg);
		$t =  mb_substr($text, 0, 100, 'UTF-8');
		if (!$t) $t = '附件消息';
		$c = $text . ' [url=' . $link . ']' . $link . '[/url]';
		$client = new Zend_Http_Client($this->_api_url , array(
			'keepalive' => true,
			));	

		$this->_request = array(
			'a' => $action,
			'time' => $time,
			't' => $t,
			'c' => $c,
			'p' => $pep,
			'm' => $string,
			'ip' => 'win',
			'ifconvert' => 1,
		);
		
		if ($geo) {
			list($x, $y) = Better_Functions::LL2XY($geo['lon'], $geo['lat']);
			$this->_request['x'] = $x; 
			$this->_request['y'] = $y;
			$this->_request['pub'] = 'public';
		}

		$client->setParameterPost($this->_request);

		if ($attach!='' && file_exists($attach)) {
			$client->setFileUpload($attach, 'atc1');
		}
		$client->request(Zend_Http_Client::POST);
		
		$status = $client->getLastResponse()->getStatus();
		$html = $client->getLastResponse()->getBody();
		
		if ($status != 200 ) {
			$flag = false;
		} else {
			$flag = substr($html, 0, 1) == 'S' ? true : false;
		}
		
		if (!$flag) var_dump($html);
		return $flag;
	}
	
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		return false;
	}
	
}