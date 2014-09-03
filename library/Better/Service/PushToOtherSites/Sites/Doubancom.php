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
		//$WB_AKEY = '0f20208b55876aea2d6061a2009640be';
		//$WB_SKEY = 'af1327267a0fe0e3';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
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
		$akey = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$skey = Better_Config::getAppConfig()->oauth->key->douban_skey;
		
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
		$this->html = $this->oauth->post($url , $param, false, $xml );	
		$flag = $this->oauth->http_code == 201 ? true : false;
				
		$userID = $this->oauth->http_header['x_userid'];
		$url = "http://api.douban.com/people/{$userID}/miniblog";
		$t = $this->oauth->get($url , $param);
		$this->html = $t;
				
    	return $flag;	
	}
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		$akey = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$skey = Better_Config::getAppConfig()->oauth->key->douban_skey;	
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
            	
    	$url = "http://api.douban.com/miniblog/$id";
    	$h = $this->oauth->delete($url);
    	
    	return  $this->oauth->http_code == 200 ? true : false;
	}	
	
	public function get3rdId()
	{
/*
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:db="http://www.douban.com/xmlns/" xmlns:gd="http://schemas.google.com/g/2005" xmlns:openSearch="http://a9.com/-/spec/opensearchrss/1.0/" xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/">
        <title>冯同学 的广播</title>
        <author>
                <link href="http://api.douban.com/people/2184737" rel="self"/>
                <link href="http://www.douban.com/people/jeffengjun/" rel="alternate"/>
                <name>冯同学</name>
                <uri>http://api.douban.com/people/2184737</uri>
        </author>
        <entry>
                <id>http://api.douban.com/miniblog/413649743</id>
                <title>567 http://221.224.52.81:4082/jeff172470</title>
                <category scheme="http://www.douban.com/2007#kind" term="http://www.douban.com/2007#miniblog.saying"/>
                <published>2010-08-19T14:49:20+08:00</published>
                <content type="html"><![CDATA[567 <a href="http://221.224.52.81:4082/jeff172470" target="_blank" rel="nofollow">http://221.224.52.81<wbr/>:4082/jeff172470</a>]]></content>
                <db:attribute name="comments_count">0</db:attribute>
        </entry>
        <openSearch:itemsPerPage>10</openSearch:itemsPerPage>
        <openSearch:startIndex>1</openSearch:startIndex>
</feed>
*/				
		$dom = new DOMDocument();
		$o = @$dom->loadXML($this->html);
		if (!$o) {
			return 0;
		}
		
		$entry = $dom->getElementsByTagName('entry')->item(0);
		$text = $entry->getElementsByTagName("id")->item(0)->nodeValue;		
		$a = explode('/', $text);
		$id = end($a);
					
		return $id;
	}
	
	/**
	 * 
	 * @param $userID
	 * @return unknown_type
	 */
	public function getInfo($userID='')
	{
		$r = array();
		
		$akey = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$skey = Better_Config::getAppConfig()->oauth->key->douban_skey;
		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
				
        $param = array();

        if ($userID) {
        	$url = "http://api.douban.com/people/{$userID}";
        } else {
        	$url = "http://api.douban.com/people/%40me";
        }

		$xml = $this->oauth->get($url, $param);
		$flag = $this->oauth->http_code == 200 ? true : false;

/*
 * <?xml version="1.0" encoding="UTF-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:db="http://www.douban.com/xmlns/" xmlns:gd="http://schemas.google.com/g/2005" xmlns:openSearch="http://a9.com/-/spec/opensearchrss/1.0/" xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/">
	<id>http://api.douban.com/people/50282107</id>
	<title>海西子</title>
	<link href="http://api.douban.com/people/50282107" rel="self"/>
	<link href="http://www.douban.com/people/50282107/" rel="alternate"/>
	<link href="http://img3.douban.com/icon/user_normal.jpg" rel="icon"/>
	<content></content>
	<db:attribute name="n_mails">0</db:attribute>
	<db:attribute name="n_notifications">0</db:attribute>
	<db:location id="suzhou">江苏苏州</db:location>
	<db:signature></db:signature>
	<db:uid>50282107</db:uid>
	<uri>http://api.douban.com/people/50282107</uri>
</entry>

 */		
		
		$dom = new DOMDocument();
		$o = @$dom->loadXML($xml);
		if (!$o) {
			return $r;
		}
		
		$entry = $dom->getElementsByTagName('entry')->item(0);
		$title = $entry->getElementsByTagName("title")->item(0)->nodeValue;	
		$id_str = $entry->getElementsByTagName("id")->item(0)->nodeValue;
		$id_array = explode('/', $id_str);
		$id = array_pop($id_array);	

		$link = $entry->getElementsByTagName("link");
		foreach ($link as $l) {
			$rel = $l->getAttribute('rel');
			if ( 'icon' == $rel ) {
				$href = $l->getAttribute('href');
				break;
			}
		}
		
		$r['image_url'] = $href;
		$r['nickname'] = $title;	
		$r['id'] = $id;	
    	return $r;		
	}
}