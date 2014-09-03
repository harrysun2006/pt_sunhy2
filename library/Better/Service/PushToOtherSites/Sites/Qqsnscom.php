<?php
/**
 * 
 * @author Jeff
 *
 */


class Better_Service_PushToOtherSites_Sites_Qqsnscom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'open.t.qq.com';
	public $_akey;
	public $_skey;
	public $_uid;
	public $userinfo_json;
	public $QQhexchars = "0123456789ABCDEF";
	
	/**
	 * 
	 * @param $username
	 * @param $password
	 * @param $accecss_token
	 * @param $accecss_token_secret
	 * @return unknown_type
	 */
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		$this->setUserPwd($username,$password);
		$this->_api_url = 'http://open.t.qq.com/api/t/add';
		$this->_accecss_token = $accecss_token;
		$this->_accecss_token_secret = $accecss_token_secret;	
		$this->_akey = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
		$this->_skey = Better_Config::getAppConfig()->oauth->key->qqsns_skey;
		
		$this->_protocol = 'qqsns.com';
	}
	
	
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		$lbs = $img = array();
		
		if ($attach) {
			$picinfo = $this->uploadPic($attach, $msg, $geo['lon'], $geo['lat']);
		}
		
		if ($geo) {
			$lbs['address'] = $geo['location'] ? $geo['location'] : 'address';
			$lbs['lon'] = $geo['lon']; //31.300, 120.626
			$lbs['lat'] = $geo['lat'];
			//$lbs['poi_id'] = '1';
			//$lbs['name'] = '苏州贝多科技';			
		}
		
		if ($picinfo['ret'] === 0) {
			$img['richtype'] = 1;
			$_temp = array(
							'albumid' => $picinfo['albumid'],
							'pictureid' => $picinfo['lloc'],
							'sloc' => $picinfo['sloc'],
							);
							
			$img['richval'] = join(',', $_temp);
			
		}
		
		return $this->addTopic($msg, $lbs, $img);
	}
	
	public function addTopic($msg, $lbs=array(), $img=array())
	{
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		$info = array();
		
		$info['con'] = $msg;
		$info['third_source'] = 4;
		if ($lbs) {
			$info['lbs_nm'] = $lbs['address'];
			$info['lbs_x'] = $lbs['lon'];
			$info['lbs_y'] = $lbs['lat'];
			//$info['lbs_id'] = $lbs['poi_id'];
			//$info['lbs_idnm '] = $lbs['name'];
		}
		
		if ($img) {
			$info = array_merge($info, $img);
		}
		$r = $this->add_topic($this->_akey, $this->_skey, $accecss_token, $accecss_token_secret, $this->_password, $info);
		
		return $r;
	}
	
	
	public function uploadPic($picture, $photodesc='', $x=0, $y=0)
	{
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;

		$imginfo = array();
		$imginfo['picture'] = $picture;
		if ($photodesc) $imginfo['photodesc'] = $photodesc;
		if ($x) $imginfo['x'] = $x; //31.300, 120.626
		if ($y) $imginfo['y'] = $y;
		$r = $this->upload_pic($this->_akey, $this->_skey, $accecss_token, $accecss_token_secret, $this->_password, $imginfo);

		return $r;
		
//  ["albumid"]=>
//  string(36) "ad97318f-bb4a-4ae7-bc92-fbe85256e3b7"
//  ["height"]=>
//  int(503)
//  ["lloc"]=>
//  string(36) "M9XL*554.0Uogf2dNFboyx31rZIoVJwAAA!!"
//  ["msg"]=>
//  string(1) " "
//  ["ret"]=>
//  int(0)
//  ["sloc"]=>
//  string(36) "MzKUkjO*G0UepYc5uINoQsDqFo4ouqEAAA!!"
//  ["width"]=>
//  int(670)		
	}

	
	public function getInfo()
	{
		$third_info = array();
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$r = $this->get_user_info($this->_akey, $this->_skey, $accecss_token, $accecss_token_secret, $this->_password);
		
//	array(6) {
//  ["ret"]=>
//  int(0)
//  ["msg"]=>
//  string(0) ""
//  ["nickname"]=>
//  string(8) "鈽?Jeff"
//  ["figureurl"]=>
//  string(70) "http://qzapp.qlogo.cn/qzapp/220736/103B903ABDF5181A4CFCF885D28B0840/30"
//  ["figureurl_1"]=>
//  string(70) "http://qzapp.qlogo.cn/qzapp/220736/103B903ABDF5181A4CFCF885D28B0840/50"
//  ["figureurl_2"]=>
//  string(71) "http://qzapp.qlogo.cn/qzapp/220736/103B903ABDF5181A4CFCF885D28B0840/100"
//}		
		if ($r['ret'] == 0) {
			$third_info['nickname'] = $r['nickname'];
			$third_info['image_url'] = $r['figureurl_2'];
			$third_info['is_vip'] = 0;
			$this->tid = $json->name;
		}
	
		return $third_info;
	}
	
	
	public function get_user_info($appid, $appkey, $access_token, $access_token_secret, $openid)
	{
		//获取用户信息的接口地址, 不要更改!!
	    $url    = "http://openapi.qzone.qq.com/user/get_user_info";
	    $info   = $this->do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid);
	    $arr = array();
	    $arr = json_decode($info, true);
	
	    return $arr;
	}

	
	public function upload_pic($appid, $appkey, $access_token, $access_token_secret, $openid, $imginfo)
	{
		$r = false;
		
		//上传照片的接口地址, 不要更改!!
	    $url    = "http://openapi.qzone.qq.com/photo/upload_pic";
	    $str = $this->do_multi_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid, $imginfo);
	    $arr = array();
	    $arr = json_decode($str, true);
	    
	    return $arr;
	    
		if ($arr['ret'] == 0) {
			$r = true;
		}
	    return $r;	    
	}	
	
	
	public function add_topic($appid, $appkey, $access_token, $access_token_secret, $openid, $info)
	{
	    $r = false;
		
		$url = "http://openapi.qzone.qq.com/shuoshuo/add_topic";
	    $str = $this->do_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid, $info);
	    $arr = array();
	    $arr = json_decode($str, true);
	    if ($arr['data']['ret'] === 0) {
	    	$r = true;
	    }
	    
	    return $r;
//	array(3) {
//  ["data"]=>
//  array(2) {
//    ["msg"]=>
//    string(0) ""
//    ["ret"]=>
//    int(0)
//  }
//  ["err"]=>
//  array(1) {
//    ["code"]=>
//    int(0)
//  }
//  ["richinfo"]=>
//  NULL
//}
	    
	}
	
	/**
	 * @brief 所有multi-part post 请求都可以使用这个方法
	 *
	 * @param $url
	 * @param $appid
	 * @param $appkey
	 * @param $access_token
	 * @param $access_token_secret
	 * @param $openid
	 *
	 */
	public function do_multi_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid, $addparams=array())
	{
	    //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
	    $sigstr = "POST"."&"."$url"."&";
	
	    //必要参数,不要随便更改!!
	    $params = $_POST;
	    $params["oauth_version"]          = "1.0";
	    $params["oauth_signature_method"] = "HMAC-SHA1";
	    $params["oauth_timestamp"]        = time();
	    $params["oauth_nonce"]            = mt_rand();
	    $params["oauth_consumer_key"]     = $appid;
	    $params["oauth_token"]            = $access_token;
	    $params["openid"]                 = $openid;
	    unset($params["oauth_signature"]);
		
		foreach ($addparams as $key=>$value) {
			$params[$key] = $value;
			if ($key == 'picture') {
				$params['picture'] = file_get_contents($value);
			}
		}
	   
	    //对参数按照字母升序做序列化
	    $sigstr .= $this->get_normalized_string($params);
	
	    //签名,需要确保php版本支持hash_hmac函数
	    $key = $appkey."&".$access_token_secret;
	    $signature = $this->get_signature($sigstr, $key);
	    $params["oauth_signature"] = $signature; 
	
	    //处理上传图片
	    if ($addparams['picture']) {
			$params['picture'] = "@" . $addparams['picture'];	    	
	    }
	
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);	     
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $ret = curl_exec($ch);
	    curl_close($ch);
	    return $ret;
	}

	
	public function do_post($url, $appid, $appkey, $access_token, $access_token_secret, $openid, $addparams=array())
	{
	    //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
	    $sigstr = "POST"."&". $this->QQConnect_urlencode($url)."&";
	
	    //必要参数,不要随便更改!!
	    $params = array();
	    $params["oauth_version"]          = "1.0";
	    $params["oauth_signature_method"] = "HMAC-SHA1";
	    $params["oauth_timestamp"]        = time();
	    $params["oauth_nonce"]            = mt_rand();
	    $params["oauth_consumer_key"]     = $appid;
	    $params["oauth_token"]            = $access_token;
	    $params["openid"]                 = $openid;
	    $params['format']				  = 'json';
	    unset($params["oauth_signature"]);
	    
	    foreach ($addparams as $key => $value) {
	    	$params[$key] = $value;
	    }
	
	    //对参数按照字母升序做序列化
	    $sigstr .= $this->QQConnect_urlencode($this->get_normalized_string($params));
	
	    //签名,需要确保php版本支持hash_hmac函数
	    $key = $appkey."&".$access_token_secret;
	    $signature = $this->get_signature($sigstr, $key); 
	    $params["oauth_signature"] = $signature; 
	    $postdata = $this->get_urlencode_string($params);
	
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
	    curl_setopt($ch, CURLOPT_POST, TRUE); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $ret = curl_exec($ch);
	
	    curl_close($ch);
	    return $ret;
	
	}	
	
	public function do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid)
	{
	    $sigstr = "GET"."&".$this->QQConnect_urlencode("$url")."&";
	
	    //必要参数, 不要随便更改!!
	    $params = $_GET;
	    $params["oauth_version"]          = "1.0";
	    $params["oauth_signature_method"] = "HMAC-SHA1";
	    $params["oauth_timestamp"]        = time();
	    $params["oauth_nonce"]            = mt_rand();
	    $params["oauth_consumer_key"]     = $appid;
	    $params["oauth_token"]            = $access_token;
	    $params["openid"]                 = $openid;
	    unset($params["oauth_signature"]);
	
	    //参数按照字母升序做序列化
	    $normalized_str = $this->get_normalized_string($params);
	    $sigstr        .= $this->QQConnect_urlencode($normalized_str);
	    //签名,确保php版本支持hash_hmac函数
	    $key = $appkey."&".$access_token_secret;
	    $signature = $this->get_signature($sigstr, $key);
	    $url      .= "?".$normalized_str."&"."oauth_signature=".$this->QQConnect_urlencode($signature);
	    return file_get_contents($url);
	}	
	
	/**
	 * 
	 * @param $str
	 * @return unknown_type
	 */
	public function QQConnect_urlencode($str)
	{
	    $QQhexchars = $this->QQhexchars;
	    $urlencode = "";
	    $len = strlen($str);
	
	    for($x = 0 ; $len--; $x++)
	    {
	        if (($str[$x] < '0' && $str[$x] != '-' && $str[$x] != '.') ||
	            ($str[$x] < 'A' && $str[$x] > '9') ||
	            ($str[$x] > 'Z' && $str[$x] < 'a' && $str[$x] != '_') ||
	            ($str[$x] > 'z' && $str[$x] != '~')) 
	        {
	            $urlencode .= '%';
	            $urlencode .= $QQhexchars[(ord($str[$x]) >> 4)];
	            $urlencode .= $QQhexchars[(ord($str[$x]) & 15)];
	        }
	        else
	        {
	            $urlencode .= $str[$x];
	        }
	    }
	
	    return $urlencode;
	}	
	
	
	/**
	 * @brief 对参数进行字典升序排序
	 *
	 * @param $params 参数列表
	 *
	 * @return 排序后用&链接的key-value对（key1=value1&key2=value2...)
	 */
	public function get_normalized_string($params)
	{
	    ksort($params);
	    $normalized = array();
	    foreach($params as $key => $val)
	    {
	        $normalized[] = $key."=".$val;
	    }
	
	    return implode("&", $normalized);
	}
	
		
	/**
	 * @brief 使用HMAC-SHA1算法生成oauth_signature签名值 
	 *
	 * @param $key  密钥
	 * @param $str  源串
	 *
	 * @return 签名值
	 */
	
	public function get_signature($str, $key)
	{
	    $signature = "";
	    if (function_exists('hash_hmac'))
	    {
	        $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
	    }
	    else
	    {
	        $blocksize	= 64;
	        $hashfunc	= 'sha1';
	        if (strlen($key) > $blocksize)
	        {
	            $key = pack('H*', $hashfunc($key));
	        }
	        $key	= str_pad($key,$blocksize,chr(0x00));
	        $ipad	= str_repeat(chr(0x36),$blocksize);
	        $opad	= str_repeat(chr(0x5c),$blocksize);
	        $hmac 	= pack(
	            'H*',$hashfunc(
	                ($key^$opad).pack(
	                    'H*',$hashfunc(
	                        ($key^$ipad).$str
	                    )
	                )
	            )
	        );
	        $signature = base64_encode($hmac);
	    }
	
	    return $signature;
	} 
	
	/**
	 * @brief 对字符串进行URL编码，遵循rfc1738 urlencode
	 *
	 * @param $params
	 *
	 * @return URL编码后的字符串
	 */
	public function get_urlencode_string($params)
	{
	    ksort($params);
	    return http_build_query($params);
	    
	    $normalized = array();
	    foreach($params as $key => $val)
	    {
	        if ( in_array($key, array('oauth_timestamp', 'oauth_nonce', 'third_source')) ) {
	        	$normalized[] = $key."=" . $val;
	        } else {
	        	$normalized[] = $key."=" . $this->QQConnect_urlencode($val);
	        }
	    	
	    }
	
	    return implode("&", $normalized);
	}	
}