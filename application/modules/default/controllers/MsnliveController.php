<?php

class MsnliveController extends Better_Controller_Front 
{
	public function init()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();	
	}
	
	public function indexAction()
	{
		//phpinfo();
		$this->output();
	}
	public function msnoauthcalltokenAction(){
		die('xx');
	}
	public function msnoauthAction()
	{
		$wrap_verification_code = $_GET['wrap_verification_code'];
		
		$msnoauthurl = 'https://consent.live.com/pp900/AccessToken.aspx';
		$msnoauthcalltokenurl = 'http://k.ai/msnlive/msnoauth';
		$client = new Zend_Http_Client($msnoauthurl, array(
			'keepalive' => true,
			));
		$request = array(
			'wrap_client_id' => Better_Config::getAppConfig()->oauth->key->live->client_id,
			'wrap_client_secret' => Better_Config::getAppConfig()->oauth->key->live->secret_key,
			'wrap_callback' => $msnoauthcalltokenurl,
			'wrap_verification_code' => $wrap_verification_code,
			);
		$client->setParameterPost($request);
		$client->request(Zend_Http_Client::POST);
		$html = $client->getLastResponse()->getBody();
		$response = $html;
		if (strpos($response, '?') === false) {
            $pos = strpos($response, 'wrap_access_token=');

            if ($pos === false) {
                $pos = strpos($response, 'wrap_error_reason=');
            }
            if ($pos !== false) {
                $response = '?' . substr($response, $pos, strlen($response));
            }
        }
        $returnedVariables = array();
        // RegEx the string to separate out the variables and their values
        if (preg_match_all('/[?&]([^&=]+)=([^&=]+)/', $response, $matches)) {
            $contents = '';
            for ($i =0; $i < count($matches[1]); $i++) {
                $_SESSION[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
                $returnedVariables[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
            }
        } else {
            throw new UnexpectedValueException(
                    'There are no matches for the regular expression used
                        against the OAuth response.');
        }
        Zend_Debug::dump($returnedVariables);
        Zend_Debug::dump($returnedVariables['wrap_refresh_token']);	
        //刷新取得新的TOKEN
        $wrap_refresh_token = $returnedVariables['wrap_refresh_token'];
		$msnoauth_refreshurl = 'https://consent.live.com/pp900/RefreshToken.aspx';		
		$msnoauthcalltokenurl = 'http://k.ai/msnlive/msnoauth';
		$client2 = new Zend_Http_Client($msnoauth_refreshurl, array(
			'keepalive' => true,
			));
		$request2 = array(
			'wrap_refresh_token' => $wrap_refresh_token,
			'wrap_client_id' => Better_Config::getAppConfig()->oauth->key->live->client_id,
			'wrap_client_secret' =>Better_Config::getAppConfig()->oauth->key->live->secret_key,
			);
		$client2->setHeaders('Authorization','WRAP access_token='.$returnedVariables['wrap_access_token']);
		$client2->setParameterPost($request2);
		$client2->request(Zend_Http_Client::POST);
		$html1 = $client2->getLastResponse()->getBody();
		$response = $html1;
		if (strpos($response, '?') === false) {
            $pos = strpos($response, 'wrap_access_token=');

            if ($pos === false) {
                $pos = strpos($response, 'wrap_error_reason=');
            }
            if ($pos !== false) {
                $response = '?' . substr($response, $pos, strlen($response));
            }
        }
        $returnedVariables = array();
        // RegEx the string to separate out the variables and their values
        if (preg_match_all('/[?&]([^&=]+)=([^&=]+)/', $response, $matches)) {
            $contents = '';
            for ($i =0; $i < count($matches[1]); $i++) {
                $_SESSION[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
                $returnedVariables[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
            }
        } else {
            throw new UnexpectedValueException(
                    'There are no matches for the regular expression used
                        against the OAuth response.');
        }
        Zend_Debug::dump($returnedVariables);
	}
	
	public function msnoauthrequestAction()
	{
		$msnoauthurl = 'https://consent.live.com/pp900/connect.aspx';
		$callback = 'http://k.ai/msnlive/msnoauth';
		header('Location:'.$msnoauthurl.'?wrap_client_id='.Better_Config::getAppConfig()->oauth->key->live->client_id.'&wrap_callback='.$callback);
exit(0);		
		$client = new Zend_Http_Client($msnoauthurl, array(
			'keepalive' => true,
			));
		$request = array(
			'wrap_client_id' => Better_Config::getAppConfig()->oauth->key->live->client_id,
			'wrap_callback' => $callback,
			);

		$client->setParameterGET($request);
		$client->request(Zend_Http_Client::GET);
		$html = $client->getLastResponse()->getBody();
		die($html);Zend_Debug::dump($html);
		exit(0);		
	}
	protected function output()
	{
		exit(0);
	}
}