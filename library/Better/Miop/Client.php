<?php
//header('Cache-control: no-cache, no-store, must-revalidate');
/* 
  +-----------------------------------------------------------------------------+
  | 139.com Platform PHP5 client	                                            |
  +-----------------------------------------------------------------------------+
*/
define('MIOP_API_URL','http://jiekou.shequ.10086.cn/restserver.php');/*修改成自己的域名*/
define('MIOP_API_PHP_SDK_VERSION','1.0');

define('CONNECT_TIMEOUT',5);
define('READ_TIMEOUT',5);


class Better_Miop_Client {
	var $app_key;
	var $app_secret;
	var $session_key;
	var $user;
	var $time;
	var $friends_list;
	var $final_encode;		// the encoding print out finally  最终输出的数据的编码格式

	var $server_addr;		// 请求的接口文件名称

	/**
	* 创建API客户端 
	* @param string $session_key 如果$session_key没有值,则默认为null. 
	*                            随后的操作会对其赋值.
	*                            
	*/
	function __construct($app_key, $app_secret, $session_key=null, $user=null, $time=null) {
		$this->app_key		= $app_key;
		$this->app_secret	= $app_secret;
		$this->session_key	= $session_key;
		$this->user			= $user;
		$this->time			= $time;
		$this->final_encode	= "utf-8";
		$this->last_call_id = 0;

		$this->server_addr  =  MIOP_API_URL;
	}

	/**
	 * 兼容 php4
	 */
	function MiopClient($app_key, $app_secret, $session_key=null, $user=null, $time=null) {
		$this->__construct($app_key, $app_secret, $session_key, $user, $time);
	}

	/**
	 * 创建Token
	 *
	 */
	function &auth_createToken() {
		return $this->call_method('miop.auth.createToken',array());
	}

	/**
	 * 根据Token获取session_key.
	 *
	 */
	function &auth_getSession($token) {
		return $this->call_method('miop.auth.getSession',array('auth_token'=>$token));
	}


 function &connect_getAppAccount($app_account){
	      $params = array(
			'app_account' => $app_account,
			   'format' => 'json',
			   );
		  return $this->call_method('miop.connect.getAppAccount',$params);
 }

function &connect_getSession($app_account){
	$params = array(
			'app_account' => $app_account,
			   'format' => 'json',
			);
	return $this->call_method('miop.connect.getSession',$params);
}

function &connect_isRegister($app_account){
	$params = array(
			'app_account' => $app_account,
			   'format' => 'json',
			);
	return $this->call_method('miop.connect.isRegister',$params);
}

function &connect_unRegister($app_account){
	$params = array(
			'app_account' => $app_account,
			   'format' => 'json',
			);
	return $this->call_method('miop.connect.unRegister',$params);
}

  function &connect_register($uid, $app_account){
	  $params = array(
			  'uid' => $uid,
			  'app_account' => $app_account,
			   'format' => 'json',
			  );
	  return $this->call_method('miop.connect.register',$params);
  }


	/**
	* 获取好友ID列表
	* 不传uid参数，获取当前登录用户的好友；
	* 指定uid，则返回指定uid的好友
	* 指定的uid必须是当前授权该App访问的用户ID
	*
	*/
	function &friends_get($uid=null) {
		if (isset($this->friends_list)) {
		  return $this->friends_list;
		}
		return $this->call_method('miop.friends.get', array('uid'=>$uid));
	}


	/**
	* 返回当前session用户的好友中添加当前应用的ID列表 
	* @return array of user
	*/
	function &friends_getAppUsers() {
		return $this->call_method('miop.friends.getAppUsers', array());
	}


	/**
	* 比较两组用户的关系
	* uids1 和 uids2 两个数组元素的个数必须相同
	* uids1 和 uids2 两个数组的元素必须是当前登录用户或当前登录用户的好友
	*/
	function &friends_areFriends($uids1, $uids2) {
		return $this->call_method('miop.friends.areFriends', array('uids1'=>$uids1, 'uids2'=>$uids2));
	}

	/**
	* 返回查询用户资料的指定字段 
	* user_ids 查询用户的数组,如果数组为空,则查询当前用户的用户资料. 
	* fields 指定资料的字段
	*
	*/
	function &users_getInfo($user_ids, $fields) {
		return $this->call_method('miop.users.getInfo', array('uids' => $user_ids, 'fields' => $fields));
	}

	/**
	* 返回当前session的用户ID 
	*
	*/
	function &users_getLoggedInUser() {
		return $this->call_method('miop.users.getLoggedInUser', array());
	}

	/**
	* 判断用户是否授予当前App访问
	* 若不指定user_id，默认是当前session的用户.
	*
	*/
	function &users_isAppUser($user_id) {
		$params = array();
		if( intval($user_id) ) {
			$params['uid'] = intval($user_id);
		}
		return $this->call_method('miop.users.isAppUser', $params);
	}

	/**
	* 给指定用户的发送通知信 
	*
	*/
	function &notifications_send($notification,$to_ids=array(),$type='app_to_user', $message_type=1,$act_ids='') {
		$params = array(
				'notification'=>$notification,
				'type'=>$type,
				'format'=>'json',
				'to_ids'=>$to_ids,
				'message_type'=>$message_type,
				'act_ids'=>$act_ids,
				);
		return $this->call_method('miop.notifications.send', $params );
	}

	/**
	* 给当前用户好友发送feed,目前只是支持安装app的feed，例如:{"title":{"text":"礼物","href":"http://game.139.com/gift/"}} 
	*
	*/
	function &feed_publishTemplatizedAction($template_data, $templateId, $feedType=null, $pushFlag=null, $targetId=null, $targetType=null, $receiveId = null) {
		$options = array(
					'template_data'	=>$template_data,
					'template_id'	=>$templateId
					
				);
		if( isset( $feedType ) ) $options['feed_type'] = $feedType;
		if( isset( $pushFlag ) ) $options['push_flag'] = $pushFlag;
		if( isset( $targetId ) ) $options['target_id'] = $targetId;
		if( isset( $targetType ) ) $options['target_type'] = $targetType;
		if( isset( $receiveId ) ) $options['receive_id'] = $receiveId;
		return $this->call_method('miop.feed.publishTemplatizedAction', $options );
	}


	/**
	* 给当前用户好友发送feed,目前只是支持安装app的feed，例如:{"title":{"text":"礼物","href":"http://game.139.com/gift/"}} 
	*
	*/
	function &sms_send($to_id, $content) {
		$options = array('to_id' => $to_id,
						 'content' => $content
						);
		return $this->call_method('miop.sms.send', $options );
	}

	/**
	 * 判断$content中是否含有非法词.
	 */
	function &content_filter($content) {
		$options = array('content' => $content);
		return $this->call_method('miop.content.filter', $options );
	}

	/**
	 * 根据手机号获取用户的ID
	 */
	function &users_getIdByMobile($mobile,$fields) {
		$options = array('mobile' => $mobile,
							'fields' => $fields);

		return $this->call_method('miop.users.getIdByMobile',$options);
	}

	/**
	 * 创建支付订单.
	 */
	function &pay_getOrder($call_url,$return_url,$app_url,$extra_info,$commodities) {
		$options = array('CallUrl' => $call_url,
							'ReturnUrl' => $return_url,
							'AppUrl' => $app_url,
							'ExtraInfo' => $extra_info,
							'Commodities' => $commodities,
							);

		return $this->call_method('miop.pay.getOrder',$options);
	}

	/**
	 * 发送说客或者回复指定说客.
	 */
	function &italk_send($text, $uid=null) {
		$params = array(
						'text' => $text,
						'uid' => $uid,
						'no_sms' => 1,
						);
		return $this->call_method('miop.italk.send', $params);
	}

	/**
	 * 发送图片.
	 */
	function &italk_sendPic($pic_data, $pic_title, $pic_des, $album_id=null, $uid=null) {
		$params = array('pic_data' => $pic_data,
		                    'pic_title' => $pic_title,
		                    'pic_des' => $pic_des,
		                    'album_id' => $album_id,
						    'uid' => $uid,
							);
		return $this->call_method('miop.italk.sendPic', $params);
	}

	/**
	 * 获取指定用户的说客列表.
	 */
	function &italk_getTalks($uid,$page,$page_size,$max_id,$source=2) {
		$options = array(
					'uid'=>$uid,
					'page'=>$page,
					'page_size'=>$page_size,
					'maxid'=>$max_id,
					'source' => $source,
				);

		return $this->call_method('miop.italk.getTalks',$options);
	}
	
	/**
	 * 获取说客的回复/评论列表.
	 */
	function &italk_getReplys($note_id, $offset=null, $page_size=null, $maxid=null) {
		$params = array('note_id' => $note_id,
						    'offset' => $offset,
							'page_size' => $app_size,
							'maxid' => $maxid,
							);
		return $this->call_method('miop.italk.getReplys', $params);
	}

	/**
	 * 删除说客.
	 */
	function &italk_delete($mid, $uid = null) {
		return $this->call_method('miop.italk.delete', array('mid' => $mid, 'uid' => $uid));
	}

	/**
	 * 转发说客.
	 */
	function &italk_forward($mid, $uid=null, $content=null, $privacy=null) {
		$params = array('mid' => $mid,
		                    'uid' => $uid,
						    'content' => $content,
							'privacy' => $privacy,
							);
		return $this->call_method('miop.italk.forward', $params);
	}

	/**
	 * 评论说客.
	 */
	function &italk_comment($mid, $content, $uid=null, $receiveid=null, $privacy=null) {
		$params = array('mid' => $mid,
						    'content' => $content,
						    'uid' => $uid,
						    'receiveid' => $receiveid,
							'privacy' => $privacy,
							);
		return $this->call_method('miop.italk.comment', $params);
	}


	
	/**
	 * post params to the API at 139.com
	 */
	function create_post_string($method, $params) {

		$namespace = "mi_sig";

		$params['user'] = $this->user;
		$params['session_key'] = $this->session_key;
		$params['api_key'] = $this->app_key;
		$params['time'] = $this->time;
		$params['method'] = $method;
		$params['v'] = MIOP_API_PHP_SDK_VERSION;

		$params['call_id'] = $this->get_microtime();
		if ($params['call_id'] <= $this->last_call_id) {
		  $params['call_id'] = $this->last_call_id + 0.001;
		}
		$this->last_call_id = $params['call_id'];

		$prefix = $namespace . '_';
		$prefix_len = strlen($prefix);
		$fb_params = array();
		$post_data = "";
	    
		foreach ($params as $name => $val) {
			if (is_array($val)) {
				$val = implode(',', $val);
			}
			$params[$name] = Better_Miop_Main::no_magic_quotes($val);
		}
		$sig = Better_Miop_Main::generate_signature($params, $this->app_secret);
		foreach ($params as $name => $val)
		{
			if($this->final_encode != "utf-8" && $this->final_encode != "utf8")
				$val = iconv($this->final_encode,"utf-8",$val);

			//$post_data .= $prefix . $name . "=" . urlencode($val) . "&";
			$post_data .= $name . "=" . urlencode($val) . "&";
		}
		$post_data .= $namespace . "=" . $sig;
	    
		return $post_data;
	}

	/**
	 * Interprets a string of XML into an array
	 */
	function convert_xml_to_result($xml, $method, $params) {

		$sxml = simplexml_load_string($xml);
		$result = self::convert_simplexml_to_array($sxml);

		return $result;
	}


	function post_request($method, $params) {
		$post_string = $this->create_post_string($method, $params);
		$result = httpRequest($this->server_addr, $post_string);
		return $result;
	}

	/**
	 * set the encoding that printing out
	 * 设置返回的数据编码
	 */
	function set_encoding($enc="utf-8")
	{
		$arrEnc = array("utf-8", "gbk", "gb2312");
		if(!in_array(strtolower($enc), $arrEnc)) {
			$enc="utf-8";
		}
		$this->final_encode = $enc;
	}

	/**
	 * Performs a character set conversion on the string str from utf-8 to gbk
	 * @param	string	str		the string want to convert
	 * @param	bool	ignore	if ignore the chars that represented failed,
	 * @return	string	str		represented string
	 */
	function utf2Gbk($str, $ignore=true)
	{
		$this->final_encode = strtolower($this->final_encode);
		if($this->final_encode == "utf-8" || $this->final_encode == "utf8") {
			return $str;
		}
		if($ignore) {
			return iconv("utf-8", "{$this->final_encode}//IGNORE", $str);
		}else {
			return iconv("utf-8", "{$this->final_encode}", $str);
		}
	}

	function convert_simplexml_to_array($sxml) {
		$arr = array();
		if ($sxml) {
			foreach ($sxml as $k => $v) {
				if ($sxml['list']) {
					$arr[] = self::convert_simplexml_to_array($v);
				} else {
					$tmp = self::convert_simplexml_to_array($v);
					if(strtolower($v['enc']) == "base64") {
						$arr[$k] = self::utf2Gbk(base64_decode($tmp));
					}else {
						$arr[$k] = self::utf2Gbk($tmp);
					}
				}
			}
		}
		if (count($arr) > 0) {
			return $arr;
		} else {
			return (string)$sxml;
		}
	}

	/* UTILITY FUNCTIONS */
	function &call_method($method, $params) {

		$params['sdk_from'] = "php";
		$retStr = $this->post_request($method, $params);
		if($retStr == false) {
			$arr['error_code'] = 2;
			$arr['error_msg'] = "Service temporarily unavailable";
			return $arr;
		}
		if( $retStr ) { 
			if (empty($params['format']) || strtolower($params['format']) != 'json') {
				$result = $this->convert_xml_to_result($retStr, $method, $params);
			} else {
				$result = json_decode($retStr,true);
			}
			if($result === "") {
				$result = array();
			}
			return $result;
		}	
		return array();
	}

	/**
	 * 
	 */
	function get_microtime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

} // end class


/**
 * http post
 */
function httpRequest($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT)
{
	$result = "";
	if (function_exists('curl_init')) {
		$timeout = $connectTimeout + $readTimeout;
		// Use CURL if installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		if (APPLICATION_ENV!='production') curl_setopt($ch, CURLOPT_PROXY, 'http://10.10.1.254:808');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, '139.com API PHP5 Client 1.1 (curl) ' . phpversion());		
		$result = curl_exec($ch);	
		curl_close($ch);
	} else {
		// Non-CURL based version...
		$result = socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT);
	}
	return $result;
}

/**
 * http post
 */
function socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT){
	$urlInfo = parse_url($url);
	$urlInfo["path"] = ($urlInfo["path"] == "" ? "/" : $urlInfo["path"]);
	$urlInfo["port"] = ($urlInfo["port"] == "" ? 80 : $urlInfo["port"]);
	$hostIp = gethostbyname($urlInfo["host"]);

	$urlInfo["request"] =  $urlInfo["path"]	. 
		(empty($urlInfo["query"]) ? "" : "?" . $urlInfo["query"]) . 
		(empty($urlInfo["fragment"]) ? "" : "#" . $urlInfo["fragment"]);

	$fsock = fsockopen($hostIp, $urlInfo["port"], $errno, $errstr, $connectTimeout);
	if (false == $fsock) {
		return false;
	}
	/* begin send data */
	$in = "POST " . $urlInfo["request"] . " HTTP/1.0\r\n";
	$in .= "Accept: */*\r\n";
	$in .= "User-Agent: 139.com API PHP5 Client 1.1 (non-curl)\r\n";
	$in .= "Host: " . $urlInfo["host"] . "\r\n";
	$in .= "Content-type: application/x-www-form-urlencoded\r\n";
	$in .= "Content-Length: " . strlen($post_string) . "\r\n";
	$in .= "Connection: Close\r\n\r\n";
	$in .= $post_string . "\r\n\r\n";

	stream_set_timeout($fsock, $readTimeout);
	if (!fwrite($fsock, $in, strlen($in))) {
		fclose($fsock);
		return false;
	}
	unset($in);

	//process response
	$out = "";
	while ($buff = fgets($fsock, 2048)) {
		$out .= $buff;
	}
	//finish socket
	fclose($fsock);
	$pos = strpos($out, "\r\n\r\n");
	$head = substr($out, 0, $pos);		//http head
	$status = substr($head, 0, strpos($head, "\r\n"));		//http status line
	$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));		//page body
	if (preg_match("/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches)) {
		if (intval($matches[1]) / 100 == 2) {//return http get body
			return $body;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>
