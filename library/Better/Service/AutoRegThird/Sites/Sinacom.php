<?php

class Better_Service_AutoRegThird_Sites_Sinacom extends Better_Service_AutoRegThird_Common
{
	protected $apiKey = '';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->apiKey = Better_Config::getAppConfig()->sina_api->key;	
		$this->friendship_uid = Better_Config::getAppConfig()->sina->defaultsinauid;
		$this->_reg_url = 'http://api.t.sina.com.cn/account/register.xml?source='.$this->apiKey;
		$this->_createfriendship_url = 'http://api.t.sina.com.cn/friendships/create.xml?source='.$this->apiKey;
		$this->_reg_find_key = 'uid';	
		$this->_reg_false_key = 'error';
		$this->_friendship_key = 'uid';
	}
	
	public function fakeReg($params)
	{
		$reged = array();
		$reged['checkreg'] = false;
		$reged['errorinfo'] = '';
		$client = new Zend_Http_Client($this->_reg_url, array(
			'keepalive' => true,
			));
		

		$gender = ($params['gender']=='male' || $params['gender']=='secret')? 'm':'f';	
		$nickname = isset($params['nickname'])? $params['nickname']: str_replace("@k.ai",'',$params['email']);
		$this->_request = array(
			'nick' => $nickname,
			'gender' => $gender,
			'password' => $params['password'],
			'email' => $params['email'],
			'ip' => $params['ip'],
			'source' =>	Better_Config::getAppConfig()->sina_api->key,
			);
		Better_Log::getInstance()->logInfo(serialize($this->_request),'sinapart');
		$client->setParameterPost($this->_request);
		$client->request(Zend_Http_Client::POST);
		$html = $client->getLastResponse()->getBody();	
		Better_Log::getInstance()->logInfo($html,'sinapart');	
		try {
			if (substr($html, 0, 5)=='<'.'?xml') {
				$dom = new Zend_Dom_Query();
				$dom->setDocumentXML($html);
				$items = $dom->query($this->_reg_find_key);				
				foreach($items as $item) {
					$reged['checkreg'] = trim($item->nodeValue)!='' ? $item->nodeValue : false;
					break;
				}
				if($reged['checkreg']==false){				
					$items = $dom->query($this->_reg_false_key);				
					foreach($items as $item) {
						$reged['errorinfo'] = $item->nodeValue;
						break;
					}
				} else {
					$client2 = new Zend_Http_Client($this->_createfriendship_url, array(
						'keepalive' => true,
						));
					$this->_request = array(
						'user_id' => $this->friendship_uid,								
					);
					$client2->setAuth($params['email'], $params['password']);
					$client2->setParameterPost($this->_request);
					$client2->request(Zend_Http_Client::POST);					
				}
			}			
		} catch(Exception $e) {
			
		}		
		//Better_Log::getInstance()->logInfo(serialize($reged), 'signstyle');	
		return $reged;
	}
	
}