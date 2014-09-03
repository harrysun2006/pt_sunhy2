<?php
/** 
 * OAuth 认证类 
 * 
 * @package sae 
 * @author Easy Chen 
 * @version 1.0 
 * 
 * 
 */ 
class Better_Oauth_Weibo { 
    /** 
     * Contains the last HTTP status code returned.  
     * 
     * @ignore 
     */ 
    public $http_code; 
    /** 
     * Contains the last API call. 
     * 
     * @ignore 
     */ 
    public $url; 
    /** 
     * Set up the API root URL. 
     * 
     * @ignore 
     */ 
    public $host = ""; 
    /** 
     * Set timeout default. 
     * 
     * @ignore 
     */ 
    public $timeout = 30; 
    /**  
     * Set connect timeout. 
     * 
     * @ignore 
     */ 
    public $connecttimeout = 30;  
    /** 
     * Verify SSL Cert. 
     * 
     * @ignore 
     */ 
    public $ssl_verifypeer = FALSE; 
    /** 
     * Respons format. 
     * 
     * @ignore 
     */ 
    public $format = 'xml'; 
    /** 
     * Decode returned json data. 
     * 
     * @ignore 
     */ 
    public $decode_json = false; 
    /** 
     * Contains the last HTTP headers returned. 
     * 
     * @ignore 
     */ 
    public $http_info; 
    /** 
     * Set the useragnet. 
     * 
     * @ignore 
     */ 
    public $useragent = 'Sae T OAuth v0.2.0-beta2'; 
    /* Immediately retry the API call if the response was not successful. */ 
    //public $retry = TRUE; 
 
	public $proxy = "";
	public $proxy_type = '';
	
	public $protocol = 'douban';
	public $client_type = 'web';
    /** 
     * Set API URLS 
     */ 
    /** 
     * @ignore 
     */ 
    function accessTokenURL()  
    {
    	switch ($this->protocol){
    		case 'qqsns':
    			return 'http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token?oauth_consumer_key=' . Better_Config::getAppConfig()->oauth->key->qqsns_akey;
    			break;    			
    		case 'qq':
    			return 'https://open.t.qq.com/cgi-bin/access_token';
    			break;    			
    		case '163':
    			return 'http://api.t.163.com/oauth/access_token';
    			break;    			
    		case 'twitter':
    			return 'https://api.twitter.com/oauth/access_token';
    			break;    			
    		case 'douban':
    			return 'http://www.douban.com/service/auth/access_token';
    			break;
    		case 'sina':
    			return 'http://api.t.sina.com.cn/oauth/access_token'; 
    			break;
    	 }
    	
    } 

    /** 
     * @ignore 
     */ 
    function authenticateURL() 
    { 
    	return ''; 
    } 
    /** 
     * @ignore 
     */ 
    function authorizeURL()
    { 
    	switch ($this->protocol){
    		case 'qqsns':
    			return 'http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize';
    			break;  
    		case 'qq':
    			return 'https://open.t.qq.com/cgi-bin/authorize';
    			break;  
    		case '163':
    			return 'http://api.t.163.com/oauth/authenticate';
    			break;  
    		case 'twitter':
    			return 'https://api.twitter.com/oauth/authorize';
    			break;    			
    		case 'douban':
    			return 'http://www.douban.com/service/auth/authorize';
    			break;
    		case 'sina':
    			return 'http://api.t.sina.com.cn/oauth/authorize';
    			break;
    	 }    	
    } 

    /** 
     * @ignore 
     */ 
    function requestTokenURL() 
    { 
    	switch ($this->protocol){
    		case 'qqsns':
    			return 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';
    			break;    			
    		case 'qq':
    			return 'https://open.t.qq.com/cgi-bin/request_token';
    			break;    			
    		case '163':
    			return 'http://api.t.163.com/oauth/request_token';
    			break;    			
    		case 'twitter':
    			return 'https://api.twitter.com/oauth/request_token';
    			break;    			
    		case 'douban':
    			return 'http://www.douban.com/service/auth/request_token';
    			break;
    		case 'sina':
    			return 'http://api.t.sina.com.cn/oauth/request_token';
    			break;
    	 }       	
    } 


    /** 
     * Debug helpers 
     */ 
    /** 
     * @ignore 
     */ 
    function lastStatusCode() { return $this->http_status; } 
    /** 
     * @ignore 
     */ 
    function lastAPICall() { return $this->last_api_call; } 

    /** 
     * construct WeiboOAuth object 
     */ 
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) { 
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1(); 
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret); 
        if (!empty($oauth_token) && !empty($oauth_token_secret)) { 
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret); 
        } else { 
            $this->token = NULL; 
        } 
    } 


    /** 
     * Get a request_token from Weibo 
     * 
     * @return array a key/value array containing oauth_token and oauth_token_secret 
     */ 
    function getRequestToken($oauth_callback = NULL) { 
        $parameters = array(); 
        if (!empty($oauth_callback)) { 
            $parameters['oauth_callback'] = $oauth_callback; 
        }                
        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);	
        $token = OAuthUtil::parse_parameters($request);        
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']); 
        return $token; 
    } 

    /** 
     * Get the authorize URL 
     * 
     * @return string 
     */ 
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url) { 
        if (is_array($token)) { 
            $token = $token['oauth_token']; 
        } 
        if (empty($sign_in_with_Weibo)) { 
        	if ($this->protocol == '163' && $this->client_type == 'mobile') {
        		return $this->authorizeURL() . "?client_type=mobile&oauth_token={$token}&oauth_callback=" . urlencode($url);
        	} elseif ($this->protocol == 'qqsns') {
        		$appid = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
        		return $this->authorizeURL() . "?oauth_consumer_key={$appid}&oauth_token={$token}&oauth_callback=" . urlencode($url);
        	}
            return $this->authorizeURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url); 
        } else { 
            return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=". urlencode($url); 
        } 
    } 

    /** 
     * Exchange the request token and secret for an access token and 
     * secret, to sign API calls. 
     * 
     * @return array array("oauth_token" => the access token, 
     *                "oauth_token_secret" => the access secret) 
     */ 
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false) { 
        $parameters = array(); 
        if (!empty($oauth_verifier)) {
        	if ($this->protocol == 'qqsns') {
        		$parameters['oauth_vericode'] = $oauth_verifier;
        	} else {
        		$parameters['oauth_verifier'] = $oauth_verifier;
        	}
             
        } 
        
        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters); 
        
        $token = OAuthUtil::parse_parameters($request); 
        
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']); 
        
        return $token; 
    } 

    /** 
     * GET wrappwer for oAuthRequest. 
     * 
     * @return mixed 
     */ 
    function get($url, $parameters = array()) { 
        $response = $this->oAuthRequest($url, 'GET', $parameters); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * POST wreapper for oAuthRequest. 
     * 
     * @return mixed 
     */ 
    function post($url, $parameters = array() , $multi = false, $rawData='') {      
        $response = $this->oAuthRequest($url, 'POST', $parameters , $multi, $rawData); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    }
     
    /** 
     * PUT wreapper for oAuthRequest. 
     * 
     * @return mixed 
     */ 
    function put($url, $parameters = array() , $multi = false, $rawData='') {      
        $response = $this->oAuthRequest($url, 'PUT', $parameters , $multi, $rawData); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * DELTE wrapper for oAuthReqeust. 
     * 
     * @return mixed 
     */ 
    function delete($url, $parameters = array()) { 
        $response = $this->oAuthRequest($url, 'DELETE', $parameters); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * Format and sign an OAuth / API request 
     * 
     * @return string 
     */ 
    function oAuthRequest($url, $method, $parameters , $multi = false, $rawData='') {  
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);             
     
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);    	
        switch ($method) { 
	        case 'GET':        	
	            return $this->http($request->to_url(), 'GET'); 
	        case 'DELETE':
	        	$head_oauth = $request->to_header();       		
	        	return $this->http($request->get_normalized_http_url(), $method, $rawData , $multi ,$head_oauth);	        	    
	        case 'PUT':
        	    if ($rawData) {
	        		$head_oauth = $request->to_header();     
	        		return $this->http($request->get_normalized_http_url(), $method, $rawData , $multi ,$head_oauth, true);
	        	} else {
	        		$head_oauth = $request->to_header();
	        		return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi, $head_oauth, false);	
	        	}        	    
	        default: 
	        	if ($rawData) {
	        		$head_oauth = $request->to_header();     
	        		return $this->http($request->get_normalized_http_url(), $method, $rawData , $multi ,$head_oauth, true);
	        	} else {
	        		$head_oauth = $request->to_header();
	        		return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi, $head_oauth, false);	
	        	}
             
        } 
    } 

    /** 
     * Make an HTTP request 
     * 
     * @return string API results 
     */ 
    function http($url, $method, $postfields = NULL , $multi = false , $head_oauth='', $isRawdata=false) { 
    	
    	
        $this->http_info = array(); 
        $ci = curl_init(); 	
        if ($this->proxy) {
        	curl_setopt($ci, CURLOPT_PROXY, $this->proxy);
        }
        
        if ($this->proxy_type) {
        	curl_setopt($ci, CURLOPT_PROXYTYPE, $this->proxy_type);
        } 
	
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent); 
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout); 
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout); 
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE); 

        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);

        $heads = array($this, 'getHeader');   
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, $heads); 

        curl_setopt($ci, CURLOPT_HEADER, FALSE); 
        switch ($method) { 
	        case 'POST': 
	            curl_setopt($ci, CURLOPT_POST, TRUE); 
	            if (!empty($postfields)) { 
	                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields); 
	            } 
	            break; 
	        case 'DELETE': 
	            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
	            break; 
	        case 'PUT': 
	            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
	            if (!empty($postfields)) { 
	                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields); 
	            } 	             
	            break;
        } 

       $header_array2 = array();

        if( $multi ) {
        	$header_array2 = array("Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary , "Expect:");
        	if ( strpos($url, 'qq.com') === false && strpos($url, 'sina.com') === false ) array_push($header_array2, $head_oauth);
        	
        } else {
        	$header_array2 = array("Expect:");
        }
        
        if ($isRawdata) {
        	array_push($header_array2, "Content-Type: application/atom+xml");
        	array_push($header_array2, $head_oauth);
        }

        
		$hostname = $this->_getHostName($url);
        array_push($header_array2, "Host: " . $hostname);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array2 ); 
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE ); 
        curl_setopt($ci, CURLOPT_URL, $url); 
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));  
        $this->url = $url; 
        curl_close ($ci);

        return $response; 
    } 
    
    /**
     * 得到主机名
     * @return unknown_type
     */
    function _getHostName($url)
    {
    	$a = parse_url($url);
    	return $a['host'];
    }

    /** 
     * Get the header info to store. 
     * 
     * @return int 
     */ 
    function getHeader($ch, $header) { 
        $i = strpos($header, ':'); 
        if (!empty($i)) { 
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i))); 
            $value = trim(substr($header, $i + 2)); 
            $this->http_header[$key] = $value; 
        } 
        return strlen($header); 
    } 
} 



/** 
 * @ignore 
 */ 
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod { 
    function get_name() { 
        return "HMAC-SHA1"; 
    } 

    public function build_signature($request, $consumer, $token) { 
        $base_string = $request->get_signature_base_string(); 
		//print_r( $base_string );
        $request->base_string = $base_string; 

        $key_parts = array( 
            $consumer->secret, 
            ($token) ? $token->secret : "" 
        ); 

        //print_r( $key_parts );
		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts); 
        

		$key = implode('&', $key_parts); 

        return base64_encode(hash_hmac('sha1', $base_string, $key, true)); 
    } 
} 


/** 
 * @ignore 
 */ 
class OAuthConsumer { 
    public $key; 
    public $secret; 

    function __construct($key, $secret) { 
        $this->key = $key; 
        $this->secret = $secret; 
    } 

    function __toString() { 
        return "OAuthConsumer[key=$this->key,secret=$this->secret]"; 
    } 
} 

/** 
 * @ignore 
 */ 
class OAuthUtil { 

	public static $boundary = '';

    public static function urlencode_rfc3986($input) { 
        if (is_array($input)) { 
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input); 
        } else if (is_scalar($input)) { 
            return str_replace( 
                '+', 
                ' ', 
                str_replace('%7E', '~', rawurlencode($input)) 
            ); 
        } else { 
            return ''; 
        } 
    } 


    // This decode function isn't taking into consideration the above 
    // modifications to the encoding process. However, this method doesn't 
    // seem to be used anywhere so leaving it as is. 
    public static function urldecode_rfc3986($string) { 
        return urldecode($string); 
    } 

    // Utility function for turning the Authorization: header into 
    // parameters, has to do some unescaping 
    // Can filter out any non-oauth parameters if needed (default behaviour) 
    public static function split_header($header, $only_allow_oauth_parameters = true) { 
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/'; 
        $offset = 0; 
        $params = array(); 
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) { 
            $match = $matches[0]; 
            $header_name = $matches[2][0]; 
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0]; 
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) { 
                $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content); 
            } 
            $offset = $match[1] + strlen($match[0]); 
        } 

        if (isset($params['realm'])) { 
            unset($params['realm']); 
        } 

        return $params; 
    } 

    // helper to try to sort out headers for people who aren't running apache 
    public static function get_headers() { 
        if (function_exists('apache_request_headers')) { 
            // we need this to get the actual Authorization: header 
            // because apache tends to tell us it doesn't exist 
            return apache_request_headers(); 
        } 
        // otherwise we don't have apache and are just going to have to hope 
        // that $_SERVER actually contains what we need 
        $out = array(); 
        foreach ($_SERVER as $key => $value) { 
            if (substr($key, 0, 5) == "HTTP_") { 
                // this is chaos, basically it is just there to capitalize the first 
                // letter of every word that is not an initial HTTP and strip HTTP 
                // code from przemek 
                $key = str_replace( 
                    " ", 
                    "-", 
                    ucwords(strtolower(str_replace("_", " ", substr($key, 5)))) 
                ); 
                $out[$key] = $value; 
            } 
        } 
        return $out; 
    } 

    // This function takes a input like a=b&a=c&d=e and returns the parsed 
    // parameters like this 
    // array('a' => array('b','c'), 'd' => 'e') 
    public static function parse_parameters( $input ) { 
        if (!isset($input) || !$input) return array(); 

        $pairs = explode('&', $input); 

        $parsed_parameters = array(); 
        foreach ($pairs as $pair) { 
            $split = explode('=', $pair, 2); 
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]); 
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : ''; 

            if (isset($parsed_parameters[$parameter])) { 
                // We have already recieved parameter(s) with this name, so add to the list 
                // of parameters with this name 

                if (is_scalar($parsed_parameters[$parameter])) { 
                    // This is the first duplicate, so transform scalar (string) into an array 
                    // so we can add the duplicates 
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]); 
                } 

                $parsed_parameters[$parameter][] = $value; 
            } else { 
                $parsed_parameters[$parameter] = $value; 
            } 
        } 
        return $parsed_parameters; 
    } 
    
    public static function build_http_query_multi($params) { 
        if (!$params) return ''; 
		
		//print_r( $params );
		//return null;
     
        // Urlencode both keys and values 
        $keys = array_keys($params);
        $values = array_values($params);
        //$keys = OAuthUtil::urlencode_rfc3986(array_keys($params)); 
        //$values = OAuthUtil::urlencode_rfc3986(array_values($params)); 
        $params = array_combine($keys, $values); 

        // Parameters are sorted by name, using lexicographical byte value ordering. 
        // Ref: Spec: 9.1.1 (1) 
        uksort($params, 'strcmp'); 

        $pairs = array(); 
        
        self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

        foreach ($params as $parameter => $value) {
        	
	        if( ( $parameter == 'pic' || $parameter == 'Pic' )  && $value{0} == '@' )
	        {
	        	$url = ltrim( $value , '@' );
				$cxContext = stream_context_create();
	        	$content = file_get_contents( $url, false, $cxContext );
	        	$filename = reset( explode( '?' , basename( $url ) ));
	        	$mime = self::get_image_mime($url); 
	        	
	        	$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
				$multipartbody .= $content. "\r\n";
	        }
	        else
	        {
	        	$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="'.$parameter."\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
	        }    
        } 
        
        $multipartbody .=  $endMPboundary . "\r\n";
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61) 
        // Each name-value pair is separated by an '&' character (ASCII code 38) 
        // echo $multipartbody;
        return $multipartbody; 
    } 

    public static function build_http_query($params) { 
        if (!$params) return ''; 

        // Urlencode both keys and values 
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params)); 
        $values = OAuthUtil::urlencode_rfc3986(array_values($params)); 
        $params = array_combine($keys, $values); 

        // Parameters are sorted by name, using lexicographical byte value ordering. 
        // Ref: Spec: 9.1.1 (1) 
        uksort($params, 'strcmp'); 

        $pairs = array(); 
        foreach ($params as $parameter => $value) { 
            if (is_array($value)) { 
                // If two or more parameters share the same name, they are sorted by their value 
                // Ref: Spec: 9.1.1 (1) 
                natsort($value); 
                foreach ($value as $duplicate_value) { 
                    $pairs[] = $parameter . '=' . $duplicate_value; 
                } 
            } else { 
                $pairs[] = $parameter . '=' . $value; 
            } 
        } 
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61) 
        // Each name-value pair is separated by an '&' character (ASCII code 38) 
        return implode('&', $pairs); 
    } 
    
    public static function get_image_mime( $file )
    {
    	$ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
    	switch( $ext )
    	{
    		case 'jpg':
    		case 'jpeg':
    			$mime = 'image/jpg';
    			break;
    		 	
    		case 'png';
    			$mime = 'image/png';
    			break;
    			
    		case 'gif';
    		default:
    			$mime = 'image/gif';
    			break;    		
    	}
    	return $mime;
    }
} 

/** 
 * @ignore 
 */ 
class OAuthSignatureMethod { 
    public function check_signature(&$request, $consumer, $token, $signature) { 
        $built = $this->build_signature($request, $consumer, $token); 
        return $built == $signature; 
    } 
} 



/** 
 * @ignore 
 */ 
class OAuthRequest { 
    private $parameters; 
    private $http_method; 
    private $http_url; 
    public $base_string; 
    public static $version = '1.0'; //1.0a
    public static $POST_INPUT = 'php://input'; 

    function __construct($http_method, $http_url, $parameters=NULL) { 
        @$parameters or $parameters = array(); 
        $this->parameters = $parameters; 
        $this->http_method = $http_method; 
        $this->http_url = $http_url; 
    } 


    /** 
     * attempt to build up a request from what was passed to the server 
     */ 
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) { 
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") 
            ? 'http' 
            : 'https'; 
        @$http_url or $http_url = $scheme . 
            '://' . $_SERVER['HTTP_HOST'] . 
            ':' . 
            $_SERVER['SERVER_PORT'] . 
            $_SERVER['REQUEST_URI']; 
        @$http_method or $http_method = $_SERVER['REQUEST_METHOD']; 

        // We weren't handed any parameters, so let's find the ones relevant to 
        // this request. 
        // If you run XML-RPC or similar you should use this to provide your own 
        // parsed parameter-list 
        if (!$parameters) { 
            // Find request headers 
            $request_headers = OAuthUtil::get_headers(); 

            // Parse the query-string to find GET parameters 
            $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']); 

            // It's a POST request of the proper content-type, so parse POST 
            // parameters and add those overriding any duplicates from GET 
            if ($http_method == "POST" 
                && @strstr($request_headers["Content-Type"], 
                    "application/x-www-form-urlencoded") 
            ) { 
				$cxContext = stream_context_create();
				$post_data = OAuthUtil::parse_parameters( 
                    file_get_contents(self::$POST_INPUT, False, $cxContext) 
                ); 
                $parameters = array_merge($parameters, $post_data); 
            } 

            // We have a Authorization-header with OAuth data. Parse the header 
            // and add those overriding any duplicates from GET or POST 
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") { 
                $header_parameters = OAuthUtil::split_header( 
                    $request_headers['Authorization'] 
                ); 
                $parameters = array_merge($parameters, $header_parameters); 
            } 

        } 

        return new OAuthRequest($http_method, $http_url, $parameters); 
    } 

    /** 
     * pretty much a helper function to set up the request 
     */ 
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) { 
        @$parameters or $parameters = array(); 
        $defaults = array("oauth_version" => OAuthRequest::$version, 
            "oauth_nonce" => OAuthRequest::generate_nonce(), 
            "oauth_timestamp" => OAuthRequest::generate_timestamp(), 
            "oauth_consumer_key" => $consumer->key); 
        if ($token) 
            $defaults['oauth_token'] = $token->key; 

        $parameters = array_merge($defaults, $parameters); 

        return new OAuthRequest($http_method, $http_url, $parameters); 
    } 

    public function set_parameter($name, $value, $allow_duplicates = true) { 
        if ($allow_duplicates && isset($this->parameters[$name])) { 
            // We have already added parameter(s) with this name, so add to the list 
            if (is_scalar($this->parameters[$name])) { 
                // This is the first duplicate, so transform scalar (string) 
                // into an array so we can add the duplicates 
                $this->parameters[$name] = array($this->parameters[$name]); 
            } 

            $this->parameters[$name][] = $value; 
        } else { 
            $this->parameters[$name] = $value; 
        } 
    } 

    public function get_parameter($name) { 
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null; 
    } 

    public function get_parameters() { 
        return $this->parameters; 
    } 

    public function unset_parameter($name) { 
        unset($this->parameters[$name]); 
    } 

    /** 
     * The request parameters, sorted and concatenated into a normalized string. 
     * @return string 
     */ 
    public function get_signable_parameters() { 
        // Grab all parameters 
        $params = $this->parameters; 
        
        // remove pic 
        if (isset($params['pic'])) { 
            unset($params['pic']); 
        }
        if (isset($params['Pic'])) { 
            unset($params['Pic']); 
        }        

        // Remove oauth_signature if present 
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.") 
        if (isset($params['oauth_signature'])) { 
            unset($params['oauth_signature']); 
        } 

        return OAuthUtil::build_http_query($params); 
    } 

    /** 
     * Returns the base string of this request 
     * 
     * The base string defined as the method, the url 
     * and the parameters (normalized), each urlencoded 
     * and the concated with &. 
     */ 
    public function get_signature_base_string() { 
        $parts = array( 
            $this->get_normalized_http_method(), 
            $this->get_normalized_http_url(), 
            $this->get_signable_parameters() 
        ); 
        
        //print_r( $parts );

        $parts = OAuthUtil::urlencode_rfc3986($parts); 

        return implode('&', $parts); 
    } 

    /** 
     * just uppercases the http method 
     */ 
    public function get_normalized_http_method() { 
        return strtoupper($this->http_method); 
    } 

    /** 
     * parses the url and rebuilds it to be 
     * scheme://host/path 
     */ 
    public function get_normalized_http_url() { 
        $parts = parse_url($this->http_url); 

        $port = @$parts['port']; 
        $scheme = $parts['scheme']; 
        $host = $parts['host']; 
        $path = @$parts['path']; 

        $port or $port = ($scheme == 'https') ? '443' : '80'; 

        if (($scheme == 'https' && $port != '443') 
            || ($scheme == 'http' && $port != '80')) { 
                $host = "$host:$port"; 
            } 
        return "$scheme://$host$path"; 
    } 

    /** 
     * builds a url usable for a GET request 
     */ 
    public function to_url() { 
        $post_data = $this->to_postdata(); 
        $out = $this->get_normalized_http_url(); 
        if ($post_data) { 
            $out .= '?'.$post_data; 
        } 
        return $out; 
    } 

    /** 
     * builds the data one would send in a POST request 
     */ 
    public function to_postdata( $multi = false ) 
    {    	
	    if( $multi )
	    	return OAuthUtil::build_http_query_multi($this->parameters); 
	    else 
	        return OAuthUtil::build_http_query($this->parameters); 
    } 

    /** 
     * builds the Authorization: header 
     */ 
    public function to_header() { 
        $out ='Authorization: OAuth realm=""'; 
        $total = array(); 
        foreach ($this->parameters as $k => $v) { 
            if (substr($k, 0, 5) != "oauth") continue; 
            if (is_array($v)) { 
                throw new OAuthException('Arrays not supported in headers'); 
            } 
            $out .= ',' . 
                OAuthUtil::urlencode_rfc3986($k) . 
                '="' . 
                OAuthUtil::urlencode_rfc3986($v) . 
                '"'; 
        } 
        return $out; 
    } 

    public function __toString() { 
        return $this->to_url(); 
    } 


    public function sign_request($signature_method, $consumer, $token) { 
        $this->set_parameter( 
            "oauth_signature_method", 
            $signature_method->get_name(), 
            false 
        ); 
		$signature = $this->build_signature($signature_method, $consumer, $token); 
        //echo "sign=" . $signature;
		$this->set_parameter("oauth_signature", $signature, false); 
    } 

    public function build_signature($signature_method, $consumer, $token) { 
        $signature = $signature_method->build_signature($this, $consumer, $token); 
        return $signature; 
    } 

    /** 
     * util function: current timestamp 
     */ 
    private static function generate_timestamp() { 
        //return 1273566716;
		return time(); 
    } 

    /** 
     * util function: current nonce 
     */ 
    private static function generate_nonce() { 
        //return '462d316f6f40c40a9e0eef1b009f37fa';
		$mt = microtime(); 
        $rand = mt_rand(); 

        return md5($mt . $rand); // md5s look nicer than numbers 
    } 
} 

class OAuthException extends Exception { 
    // pass 
}



 