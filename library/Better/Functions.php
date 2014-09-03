<?php

/**
 * 一些静态方法
 *
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Functions
{
	public static function toJsArray(&$arr)
	{
		return (is_array($arr) && count($arr)>0) ? '["'.implode('","', $arr).'"]' : '[]';
	}
	
	public static function fixEmail($str)
	{
		$str = iconv('GB18030', 'UTF-8//IGNORE', $str); 
		$str = strtolower ($str);
		$str = str_replace(' ', '', $str);
		$str = str_replace('..', '.', $str);
		$str = str_replace('＠', '@', $str);
		$str = str_replace('·', '.', $str);
//		$str = str_replace('.@.', '@', $str);
//		$str = str_replace('.@', '@', $str);
//		$str = str_replace('@.', '@', $str);
//	
//		$str = str_replace('qq,com', 'qq.com', $str);
//		$str = str_replace(array('qq@.com',  'qq@com'), '@qq.com', $str);
		
		
		return $str;
	}
	
	public static function cleanBr($str)
	{
		$str = str_replace('<br />', '', $str);
		$str = str_replace('<br>', '', $str);
		$str = str_replace('<BR />', '', $str);
		$str = str_replace('<BR>', '', $str);
		$str = str_replace('<Br>', '', $str);
		$str = str_replace('<Br />', '', $str);
		
		return $str;	
	}
	
	
	/**
	 * 
	 * 根据用户partner猜一个source
	 * @param unknown_type $partner
	 */
	public static function partner2source($partner)
	{
		$source = '';
		$partner = strtolower(substr($partner, 0, 3));
		
		switch ($partner) {
			case 'ifn':
				$source = 'IFN';
				break;
			case 'ppc':
				$source = 'ppc';
				break;
			case 's60':
				$source = 'S60';
				break;
			case 'and':
				$source = 'AND';
				break;
			default:
				$source = 'web';
				break;
		}
		
		return $source;
	}
	
	public static function gateway()
	{
		$gateway = '';
		if (isset($_SERVER['HTTP_VIA']) && preg_match('/wap/i', $_SERVER['HTTP_VIA'])) {
			$gateway = 'cmwap';
		}
		
		return $gateway;
	}
	
	/**
	 * 判断是否是wap访问
	 * 
	 * @return bool
	 */
	public static function isWap()
	{ 
		if((preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"]) && !preg_match("/(linux|nt)/i", $ua))) {
			return true;
		} else if(!preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"])) {
			return false;
		}

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

        if(isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
        	return true;
        }

        $uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|wap|m3gate|winwap|openwave)/i"; 

        if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'mobile')){//如果在访问的URL中已经找到 wap字样，表明已经在访问WAP页面，无需跳转，下一版本增加 feed访问时也不跳转 
            return true; 
        }else{ 
            return false;
        } 
    }
    
	/**
	 * 取经纬度
	 * 
	 * @return array
	 */
	public static function getLL(array $params)
	{
		$result = array(
			'range' => 99999999,
			'lon' => $lon,
			'lat' => $lat,
			'error' => '',
			);
		
		$lbs = $params['lbs'];
		$uid = $params['uid'];
		
		$lon = $lat = 0;
		
		if (!$lbs) $lbs = 'BBADAwAjiU+fIL4bAhy/AAEisggA7gcCCufFBAIM8T3H1ccA==';//空数据
		$ip = Better_Functions::getIP();
		$xml = "<location ver='0.1' vendid='".Better_Config::getAppConfig()->lbs->api_key."' os='win' from='better' id='".$uid."'><locate data='$lbs'></locate><ip>$ip</ip></location>";

		$lbs = Better_Service_Lbs::getInstance();
		$lbs->getLL($xml, $uid);
		$result['error'] = $lbs->error;
	
		$result['message'] = $lbs->message;
		
		if ($lbs->lon && $lbs->lat && !$lbs->message) {
			$result['lon'] = $lbs->lon;
			$result['lat'] = $lbs->lat;
			$result['range'] = $lbs->range;
			
			if ($uid) {
				Better_User::getInstance($uid)->cache()->set('lbs', array(
					'lon' => $lbs->lon,
					'lat' => $lbs->lat,
					'time' => $lbs->time,
					'range' => $lbs->range
					));			
			}
		} else {
			$tmp = Better_Service_Ip2ll::parse($ip);
			
			$lon = (float)$tmp['lon'];
			$lat = (float)$tmp['lat'];
			
			if ($lon && $lat) {
				$result['lon'] = $tmp['lon'];
				$result['lat'] = $tmp['lat'];
			} else if ($uid) {
				$user = Better_User::getInstance($uid);
				$userInfo = $user->getUserInfo();
				
				$lon = (float)$userInfo['lon'];
				$lat = (float)$userInfo['lat'];
				
				if ($lon && $lat) {
					$result['lon'] = $lon;
					$result['lat'] = $lat;
				} else {
					$config = Better_Config::getAppConfig();
					$result['lon'] = $config->location->default_lon;
					$result['lat'] = $config->location->default_lat;
				}
			}
		}
				
		return $result;
	}
	
	/**
	 * 简单加密
	 * 
	 * @param $str
	 * @return string
	 */
	public static function pEnc($str) 
	{
		$key = Better_Config::getAppConfig()->poi->hash_key;
		
		for ($i=0;$i<strlen($str);$i++) {
			for($j=0;$j<strlen($key);$j++) {
				$str[$i] = $str[$i]^$key[$j];
			}
		}
		
		return $str;
	}
	
	/**
	 * 简单解密
	 * 
	 * @param $str
	 * @return string
	 */
	public static function pDec($str)
	{
		$key = Better_Config::getAppConfig()->poi->hash_key;
		
		for ($i=0;$i<strlen($str);$i++) {
			for ($j=0;$j<strlen($key);$j++) {
				$str[$i] = $key[$j]^$str[$i];
			}
		}
		
		return $str;
	}
	
	/**
	 * 复写php的date函数
	 * 
	 */
	public static function date($format, $time=0, $timezone=8)
	{
		$offset = $timezone*3600;
		
		$date = $time>0 ? date($format, $time+$offset) : date($format, time()+$offset);
		
		return $date;
	}
	
	/**
	 * 计算页数
	 *
	 * @param integer $count
	 * @param integer $pageSize
	 * @return integer
	 */
	public static function calPages($count, $pageSize=0)
	{
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$pages = fmod($count, $pageSize)==0 ? intval($count/$pageSize) : ceil($count/$pageSize);
		
		return $pages;
	}
	
	/**
	 * 取随机数
	 *
	 * @param $len
	 * @return string
	 */
	public static function randomNum($len=4)
	{
		$str	=	""	;
		mt_srand((double)microtime() * 1000000)	;

		for ($i=0;$i<$len;$i++)
		{
			$str	.=	mt_rand(1,9)	;
		}

		return $str	;
	}

	/**
	 * 取客户端IP
	 *
	 * @return string
	 */
	public static function getIP()
	{
		static $ip = null;
		
		if ($ip==null) {
			$tmp = getenv('HTTP_X_FORWARDED_FOR');
			
			if ($tmp) {
				$addr = explode(',', $tmp);
				$ip = $addr[sizeof($addr)-1];
			} 
			
			if (!$ip) {
				$ip = getenv('HTTP_CLIENT_IP');
				
				if (!$ip) {
					$ip = getenv('REMOTE_ADDR');
					if (!$ip) {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
				}
			}
			if($ip==null){
				$a = rand(1,200);
				$ip = '221.224.52.'.$a;
			}
		}
		
		return $ip;
	}
	
	/**
	 * 检测Email有效性
	 *
	 * @param $email
	 * @return bool
	 */
	public static function checkEmail($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * 判断中国大陆手机号码
	 *
	 * @param string $cell
	 * @return bool
	 */
	public static function isCell($cell)
	{
		return preg_match(Better_User::CELL_PAT, $cell);
	}
	
	/**
	 * 生成随机的salt
	 *
	 * @param $len
	 * @return string
	 */
	public static function genSalt($len=6)
	{
		$salt = '';

		for ( $i = 0; $i < $len; $i++ )
		{
			$num   = rand(33, 126);
			
			if ( $num == '92' )
			{
				$num = 93;
			}
			
			$salt .= chr( $num );
		}
		
		return $salt;
	}
	
	/**
	 * 比较时间
	 *
	 * @param integer $time1
	 * @param integer $time2
	 * @return string
	 */
	/*public static function compareTime($time1, $time2='')
	{
		$time2=='' && $time2 = time();
		$oneMin = 60;
		$oneHour = $oneMin*60;
		$oneDay = $oneHour*24;
		$oneWeek = $oneDay*7;
		$offset = $time2 - $time1;
		$lang = Better_Registry::get('lang');
		$str = '';

		if ($offset<$oneMin) {
			$str = $lang->global->date->justnow;
		} else if ($offset<$oneHour) {
			$str = ceil($offset/$oneMin)
						.$lang->global->date->minute
						.$lang->global->before;
		} else if ($offset<$oneDay) {
			$str = ceil($offset/$oneHour)
						.$lang->global->date->hour
						.$lang->global->before;
		} else if ($offset<$oneWeek) {
			$str = ceil($offset/$oneDay)
						.$lang->global->date->day
						.$lang->global->before;
		} else if ($time1) {
			$str = date('Y-m-d', $time1);
		} else {
			$str = $lang->global->date->unknown;
		}
		
		return $str;
	}*/
	
	/**
	 * 比较时间2
	 *
	 * @param integer $time
	 * @return string
	 */
	 public static function compareTime($time, $relate='relative', $format='date')
     {
       if ($relate == 'relative') {
       	   $time = $time+8*3600;
           $now = time()+8*3600;
           $delta = $now - $time;
           $d1 = date('d', $time);
           $d2 = date('d', $now);
           if (($delta <= 0)||(floor($delta/60)<=0))
              return "刚才";
           else if ($delta < 3600)
              return floor($delta/60) . "分钟前";
           else if ($d2-$d1 == 0 && $delta < 86400)
              return floor($delta/3600) . "小时前";
           else if ($d2-$d1 == 1 && date('Ym', $time) == date('Ym', $now))
              return '昨天 ' . date("G:i", $time);
           else if (date('y', $time) == date('y', $now))
              return date("n月j日 G:i", $time);
           else
              return date("Y-n-j", $time);
       } 
    }

	
	
	/**
	 * Lon/Lat到xy转换
	 * 
	 * @return array
	 */
	public static function LL2XY($lon, $lat)
	{
		$PI = pi();
		$x = round($lon/360*256 * pow(2,17));
		$y = round(log(tan(($lat*$PI/180+$PI/2)/2))*256/$PI/2 * pow(2,17));
		return array($x, $y);
	}

	/**
	 * xy到Lon/Lat转换
	 * 
	 * @return array
	 */
	public static function XY2LL($x, $y)
	{
		$PI = pi();
		$lon = $x/93206.7556;
		$lat = (atan(exp($y/pow(2, 17)/256*$PI*2))*2-$PI/2)*180/$PI;
		return array($lon, $lat);
	}	
	
	/**
	 * source information to readable Chinese text
	 * @param $original
	 * For test by Shunkai 
	 */
	public static function source($original){
		$lang = Better_Registry::get('lang');
		$result = $lang->javascript->global->blog->source->kai;
		switch( strtolower($original) ){
			case '':
				$result = '';
				break;
			case 'api':
				$result = $lang->javascript->global->blog->source->api;
				break;
			case 'html5':
				$result = $lang->javascript->global->blog->source->html5;
				break;
			case 'blackberry':
				$result = $lang->javascript->global->blog->source->blackberry;
				break;
			case "mobile":
				$result = $lang->javascript->global->blog->source->cell;
				break;
			case "sms":
				$result = $lang->javascript->global->blog->source->sms;
				break;
			case "mms":
				$result = $lang->javascript->global->blog->source->mms;
				break;
			case "s60":
				$result = $lang->javascript->global->blog->source->s60;
				break;
			case "win":
				$result = $lang->javascript->global->blog->source->win;
				break;
			case "uiq":
				$result = $lang->javascript->global->blog->source->uiq;
				break;
			case "and":
				$result = $lang->javascript->global->blog->source->and;
				break;
			case "ifn":
				$result = $lang->javascript->global->blog->source->ifn;
				break;
			case "brw":
				$result = $lang->javascript->global->blog->source->brw;
				break;
			case "msn":
				$result = $lang->javascript->global->blog->source->msn;
				break;
			case "j2m":
				$result = $lang->javascript->global->blog->source->j2m;
				break;
			case "ppc":
				$result = $lang->javascript->global->blog->source->ppc;
				break;
			case "spn":
				$result = $lang->javascript->global->blog->source->spn;
				break;
			case "plm":
				$result = $lang->javascript->global->blog->source->plm;
				break;
			default:
				$result = $lang->javascript->global->blog->source->web;
				break;
		}
		
		return $result;
	}

	/**
	 * 获取当前执行时间
	 * 
	 * @return float
	 */
	public static function execTime() 
	{
		$mtime = explode(' ', microtime());
		$end = $mtime[1]+$mtime[0];
		
		$tmp = explode(' ', BETTER_START_TIME);
		$start = $tmp[1]+$tmp[0];

		return round(($end-$start), 5);
	}
		
	public static function hex2bin($hex)
	{
		if (!is_string($hex)) return null;
		
		$return = '';
		for ($i=0;$i<strlen($hex);$i+=2) {
			$return .= chr(hexdec($hex{$i}.$hex{($i+1)}));
		}
		
		return $return;
	}	
	
	function ip2long($arg) 
	{
	    $numbers = explode (".", $arg);
	    return ( $numbers[0] << 24) + 
	           ( $numbers[1] << 16) + 
	           ( $numbers[2] << 8 ) + 
	           ( $numbers[3] );
	}	
	
	public static function getip2city($ip='')
	{		
		$cityArray = Better_Citycenterll::$cityArray;	
		
		$result = array(
			'live_city' => '未知',
			'live_province' => '未知',			
			);
			
		$ip =='' && $ip = Better_Functions::getIP();
		
		$tmp = Better_Service_Ip2ll::parse($ip);			
		$lon = (float)$tmp['lon'];
		$lat = (float)$tmp['lat'];
		$geo = new Better_Service_Geoname();
		$geoInfo = $geo->getBigcityName($lon, $lat);
		
		if(strlen($geoInfo['0']['name'])>1){
			$thecityname = $geoInfo['0']['name'];		
			for($i=0;$i<count($cityArray);$i++){
				$temppro = $cityArray[$i][0];				
				$temp_city = array();	
				$temp_city = split("\|",$cityArray[$i][1]);				
				for($j=0;$j<count($temp_city);$j++){
					$a =strpos($thecityname,$temp_city[$j]);				
					if($a === false)
					{
						
					} else {
						$theproname = $temppro;
						$thecityname = $temp_city[$j];						
						$result = array(
							'live_city' => $thecityname,
							'live_province' => $theproname,			
							);
						break;
					}
				}
			}			
		}			
		Better_Log::getInstance()->logInfo(serialize($geoInfo)."**>>".$ip."**".$thecityname.serialize($result),'wapsign');
		return $result;
	}
	
	/**
	 * 写个简单的日志
	 */
	public static function sLog($str, $name)
	{
		$filename = APPLICATION_PATH . '/../logs' . '/' . $name;
		$hand = fopen($filename, 'a');
		fwrite($hand, $str);
		fclose($hand);
	}
	
	
	
	public function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "") {
		$message = '';
		
		if ($method != "get" and $method != "post") {
			$message = 'The cURL method is invalid.';
		}
		if ($url == "") {
			$message = 'The cURL url is blank.';
		}
		/* 		if ($referrer == "") { */
		/* 			$message = 'The cURL referrer is blank.'; */
		/* 		} */
		/* 		if ($method == "post" and (!is_array($data) or count($data) == 0)) { */
		/* 			$message = 'The cURL post data  for POST is empty or invalid.'; */
		/* 		} */
		
		// error
		if ($message != '') {
			array_unshift ( $return_status, array ("action" => "execute cURL", "status" => "failed", "message" => $message ) );
			return;
		}
		
		set_time_limit ( 150 );
		$c = curl_init ();
		if ($method == "get") {
			curl_setopt ( $c, CURLOPT_URL, $url );
			if ($referrer != "") {
				curl_setopt ( $c, CURLOPT_REFERER, $referrer );
			}
			//$this->CURL_PROXY($c);
			curl_setopt ( $c, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt ( $c, CURLOPT_USERAGENT, GM_USER_AGENT );
			if ($extra_type != "noheader") {
				curl_setopt ( $c, CURLOPT_HEADER, 1 );
			}
			if ($extra_type != "nocookie") {
				curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
			}
			/* 			curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);				 */
		} elseif ($method == "post") {
			curl_setopt ( $c, CURLOPT_URL, $url );
			curl_setopt ( $c, CURLOPT_POST, 1 );
			curl_setopt ( $c, CURLOPT_POSTFIELDS, $post_data );
			if ($referrer != "") {
				curl_setopt ( $c, CURLOPT_REFERER, $referrer );
			}
			//$this->CURL_PROXY($c);
			curl_setopt ( $c, CURLOPT_RETURNTRANSFER, 1 );
			if ($extra_type == "nocookie") {
				curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 0 );
			} else {
				curl_setopt ( $c, CURLOPT_FOLLOWLOCATION, 1 );
			}
			curl_setopt ( $c, CURLOPT_USERAGENT, GM_USER_AGENT );
			curl_setopt ( $c, CURLOPT_HEADER, 1 );
			if ($extra_type != "nocookie") {
				//echo $extra_data;
				curl_setopt ( $c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $cookie_str) );
			}
		}
		
		$gmail_response = curl_exec ( $c );
		curl_close ( $c );
		
		return $gmail_response;
	}
	
	public function getCookieByString($string) {
		
		$temp = explode ( "\r\n", $string );
		foreach ( $temp as $key => $header ) {
			if (preg_match ( "/Set-Cookie:/i", $header )) {
				
				$cookiestr = trim ( substr ( $header . "1", 11, - 1 ) );
				return $cookiestr;
			}
		
		}
		
		return "";
	}
	
	/**
	 * 通过获得的HTTP头文字符串以解析成头的数组
	 *
	 * @param string $string
	 * @return array
	 */
	public function get_headerArrByString($string) {
		
		$cookiearr = array ();
		$location = "";
		$Server = "";
		$Date = "";
		$Content_Type = "";
		$Transfer_Encoding = "";
		$Connection = "";
		$Cache_Control = "";
		$final = array ('location' => '', 'cookie' => $cookiearr, 'Server' => '', 'Date' => '', 'Content-Type' => '', 'Transfer-Encoding' => '', 'Connection' => '', 'Cache-Control' => '' );
		$temp = explode ( "\r\n", $string );
		foreach ( $temp as $key => $header ) {
			
			if (preg_match ( "/Location:/i", $header )) {
				$location = trim ( substr ( $header . "1", 9, - 1 ) );
			}
			if (preg_match ( "/Server:/i", $header )) {
				$Server = trim ( substr ( $header . "1", 6, - 1 ) );
			}
			if (preg_match ( "/Date:/i", $header )) {
				$Date = trim ( substr ( $header . "1", 5, - 1 ) );
			}
			if (preg_match ( "/Content-Type:/i", $header )) {
				$Content_Type = trim ( substr ( $header . "1", 13, - 1 ) );
			}
			if (preg_match ( "/Transfer-Encoding:/i", $header )) {
				$Transfer_Encoding = trim ( substr ( $header . "1", 18, - 1 ) );
			}
			if (preg_match ( "/Connection:/i", $header )) {
				$Connection = trim ( substr ( $header . "1", 1, - 1 ) );
			}
			
			if (preg_match ( "/Cache-Control:/i", $header )) {
				$Cache_Control = trim ( substr ( $header . "1", 9, - 1 ) );
			}
			
			if (preg_match ( "/Set-Cookie:/i", $header )) {
				
				$cookiestr = trim ( substr ( $header . "1", 11, - 1 ) );
				$cookie = explode ( ';', $cookiestr );
				$cookie = explode ( '=', $cookie [0] );
				$cookiename = trim ( array_shift ( $cookie ) );
				$cookiearr [$cookiename] = trim ( implode ( '=', $cookie ) );
			}
		
		}
		$final = array ('location' => $location, 'cookie' => $cookiearr, 'Server' => $Server, 'Date' => $Date, 'Content-Type' => $Content_Type, 'Transfer-Encoding' => $Transfer_Encoding, 'Connection' => $Connection, 'Cache-Control' => $Cache_Control );
		return $final;
	}
	
	public function findinside($start, $end, $string) {
		preg_match_all ( '/' . $start . '([^\.)]+)' . preg_quote ( $end, '/' ) . '/i', $string, $m );
		return $m [1];
	}
	
	public function getExportid($string) {
		preg_match_all ( '/(.*?)href=[\"](.*?[\?subsection=26].*?)[\"](.*?)/i', $string, $m );
		
		return $m;
	}
	
	public function getMiddleStr($start, $end, $string) {
		
		preg_match_all ( "|" . $start . "(.*)" . $end . "|U", $string, $out );
		return $out;
	
	}
	
	function get_cookies($header) {
		$match = "";
		preg_match_all ( '!Set-Cookie:\s*([^;\s]+)($|;)!', $header, $match );
		
		$cookie = "";
		foreach ( $match [1] as $val ) {
			if ($val {0} == '=')
				continue;
				// Skip over "expired cookies which were causing problems; by Neerav; 4 Apr 2006
			if (stripos ( $val, "Expires" ) !== false)
				continue;
			list ( $key, $value ) = explode ( "=", $val );
			if ($value != '')
				$cookie .= $val . "; ";
		}
		return substr ( $cookie, 0, - 2 );
	}
	#read_header is essential as it processes all cookies and keeps track of the current location url
	#leave unchanged, include it with get_contacts
	public function read_header($ch, $string) {
		global $location;
		global $cookiearr;
		global $ch;
		
		$length = strlen ( $string );
		if (! strncmp ( $string, "Location:", 9 )) {
			$location = trim ( substr ( $string, 9, - 1 ) );
		}
		if (! strncmp ( $string, "Set-Cookie:", 11 )) {
			$cookiestr = trim ( substr ( $string, 11, - 1 ) );
			$cookie = explode ( ';', $cookiestr );
			$cookie = explode ( '=', $cookie [0] );
			$cookiename = trim ( array_shift ( $cookie ) );
			$cookiearr [$cookiename] = trim ( implode ( '=', $cookie ) );
		}
		$cookie = "";
		if (trim ( $string ) == "") {
			foreach ( $cookiearr as $key => $value ) {
				$cookie .= "$key=$value; ";
			}
			curl_setopt ( $ch, CURLOPT_COOKIE, $cookie );
		}
		
		return $length;
	}
	
	#function to trim the whitespace around names and email addresses
	#used by get_contacts when parsing the csv file
	function trimvals($val) {
		return trim ( $val, "\" \n" );
	}
	
	//将日志输出到一个文件中	 
	public function log2($event = null, $filename = "") {
		
		$now = date ( "Y-M-d-H-i-s" );
		if (empty ( $filename ))
			$filename = $now . "log4.html";
		$fd = @fopen ( $filename, 'w' );
		$log = $now . " " . $_SERVER ["REMOTE_ADDR"] . " - $event <br>";
		@fwrite ( $fd, $log );
		@fclose ( $fd );
	
	}
	public function randnumletter(){
		srand((double)microtime()*1000000);//create a random number feed.
		$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
		$list=explode(",",$ychar);
		$authnum = '';
		for($i=0;$i<6;$i++){
			$randnum=rand(0,35); 
			$authnum.=$list[$randnum];
		}	
		return 	strtolower($authnum);
	}
	public static function conputeDisplayNum($a,$b){
		if($a>12){
			if($b>6){
				$a=$b=6;
			}elseif($b>2){
				$a=12-$b;
			}elseif($b>0){
				$a=10;
				$b=2;
			}else{
				$a=12;
				$b=0;
			}
		}elseif($a>6){
			if($b>6){
				$a=$b=6;
			}elseif($b>2){
				$a=12-$b;
			}elseif($b>0){
				$a=10;
				$b=2;
			}else{
				$b=0;
			}
		}elseif($a>2){
			if($a+$b>12){
				$b=12-$a;
			}elseif($b==1){
				$b = 2;
			}
		}elseif($a>0){
			$a=2;
			if($b>10){
				$b=10;
			}elseif($b==1){
				$b = 2;
			}
		}else{
			$a=0;
			if($b>12){
				$b=12;
			}elseif($b==1){
				$b = 2;
			}
		}
		return array($a,$b);
	}
}
