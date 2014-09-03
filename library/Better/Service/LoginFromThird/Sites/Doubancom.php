<?php
class Better_Service_PushToOtherSites_Sites_Doubancom extends Better_Service_PushToOtherSites_Base
{
	
    function __construct( $username, $password , $accecss_token='' , $accecss_token_secret='' ) 
    { 
		$this->_username = $username;
		$this->_password = $password;       
    	$this->_accecss_token = $accecss_token;
        $this->_accecss_token_secret = $accecss_token_secret;
    }
    
	public function __destruct()
	{

	}
	
	
	/**
	 * 
	 */
	
	public function getToken()
	{
		$WB_AKEY = '0f20208b55876aea2d6061a2009640be';
		$WB_SKEY = 'af1327267a0fe0e3';
$log_array = array();
		
		$tokens = array();
		
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$keys = $o->getRequestToken();
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , 'http://localhost/mymy/weibodemo'.'/callback.php');
		
		//登录豆瓣  
		$snoopy = new Better_Snoopy;
		
		$submit_url = "http://www.douban.com/accounts/login";
		
		$submit_vars["form_email"] = $this->_username;
		$submit_vars["form_password"] = $this->_password;
		$submit_vars['remember'] = 'on';
		$submit_vars['user_login'] = '进入';
		
		$snoopy->submit($submit_url, $submit_vars);
		
$log_array[] = $this->_username;
$log_array[] = $this->_password;
$log_array[] = 'logincode:' . $snoopy->status;
		
		if ($snoopy->status == 302) {
			
			//取同意页面 
		
			$snoopy->fetchform($aurl);
			//模拟同意
			$forms = $snoopy->results;
		
			preg_match('<input type="hidden" name="ck" value="(.*)"/>', $forms, $matches);
			$ck = $matches[1];
			
			preg_match('<input type="hidden" name="oauth_token" value="(.*)"/> ', $forms, $matches);
			$oauth_token = $matches[1];					
			
			preg_match('<input type="hidden" name="ssid" value="(.*)"/>', $forms, $matches);
			$ssid = $matches[1];
		
			preg_match('<input type="hidden" name="oauth_callback" value="(.*)"/>', $forms, $matches);
			$oauth_callback = $matches[1];	
			
			preg_match('<input type="submit" name="confirm" value="(.*)"/>', $forms, $matches);
			$confirm = $matches[1];
			
			$submit_vars = array();
			$submit_vars["ck"] = $ck;
			$submit_vars["oauth_token"] = $oauth_token;
			$submit_vars["ssid"] = $ssid;
			$submit_vars["oauth_callback"] = $oauth_callback;
			$submit_vars["confirm"] = $confirm;

$log_array[] = $ck;
$log_array[] = $oauth_token;
$log_array[] = $ssid;
$log_array[] = $oauth_callback;
$log_array[] = $confirm;
			
			$snoopy->submit($aurl, $submit_vars);
			
$log_array[] = 'agreecode:' . $snoopy->status;

			if ($snoopy->status == 302) {
				$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
				$last_key = $o->getAccessToken();						
				$oauth_token = $last_key['oauth_token'];
				$oauth_token_secret = $last_key['oauth_token_secret'];
				$tokens['oauth_token'] = $oauth_token;
				$tokens['oauth_token_secret'] = $oauth_token_secret;
$log_array[] = 	$oauth_token;
$log_array[] = 	$oauth_token_secret;		
				}

		}

$log_str = implode('||' ,$log_array);
Better_Log::getInstance()->logAlert($log_str, 'douban');
		
		return $tokens;
			
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function fakeLogin()
	{	
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see library/Better/Service/PushToOtherSites/Better_Service_PushToOtherSites_Base#post($msg, $attach)
	 */	
	public function post($msg)
	{
		$akey = '0f20208b55876aea2d6061a2009640be';
		$skey = 'af1327267a0fe0e3';
		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
				
        $param = array();
            	
    	$url = "http://api.douban.com/miniblog/saying";
    	
    	$msg = htmlspecialchars($msg);
    	$xml = <<<EOT
<?xml version='1.0' encoding='UTF-8'?>
<entry xmlns:ns0="http://www.w3.org/2005/Atom" xmlns:db="http://www.douban.com/xmlns/">
<content>$msg</content>
</entry>
EOT;
		$this->oauth->post($url , $param, false, $xml );
					
    	return  $this->oauth->http_code == 201 ? true : false;		
		
	}
}