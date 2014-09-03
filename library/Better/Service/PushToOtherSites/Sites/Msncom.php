<?php
require_once('Zend/Uri.php');
define('BETTER_PASSBY_ZEND_URI', true);
class Better_Service_PushToOtherSites_Sites_Msncom extends Better_Service_PushToOtherSites_Common
{
	
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}
	
	public function __destruct()
	{

	}
	
	public function fakeLogin()
	{
		return $this->login();
	}
		
	public function login()
	{
		$msnurl =  Better_Config::getAppConfig()->msnsync->wrap_consent_url;
		$wrap_client_id = Better_Config::getAppConfig()->msnsync->wrap_client_id;
		$wrap_callback = Better_Config::getAppConfig()->msnsync->wrap_callback;
		$url = $msnurl."?wrap_client_id=".$wrap_client_id."&wrap_callback=".$wrap_callback."&wrap_client_state=js_close_windowjs_close_window&mkt=en-us&wrap_scope=WL_Activities.Update,Contacts.View";			
		$client = new Zend_Http_Client($url);
		$client->setCookieJar($this->_cookieJar);
		$client->request();
		$html = $client->getLastResponse()->getBody();		
		preg_match('<input type="hidden" name="__EVENTTARGET" id="__EVENTTARGET" value="(.*)" /> ', $html, $matches);
		$one = $matches[1];
		preg_match('<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="(.*)" /> ', $html, $matches);
		$two = $matches[1];
		preg_match('<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*)" /> ', $html, $matches);
		$three = $matches[1];		
		preg_match('<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*)" /> ', $html, $matches);
		$four = $matches[1];		
		$request = array(
			'__EVENTARGUMENT' => $two,
			'__EVENTTARGET' => $one,
			'__EVENTVALIDATION' => $four,
			'__VIEWSTATE' => $three,
			'btConsent' => 'Connect',
			'txtLiveID' => $this->_username,
			'txtPassword' =>$this->_password	
			);		
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer',Better_Config::getAppConfig()->msnsync->wrap_client_refresh_url);
		$client->request(Zend_Http_Client::POST);		
		$newhtml = $client->getLastResponse()->getBody();		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();		
		if($this->_cookies['c_accessToken']!='' && $this->_cookies['c_uid']!=''){
			$this->_logined = true;
			$this->tid = $this->_cookies['c_uid'];
			//$this->c_accessToken = $this->_cookies['c_accessToken'];
		}		
		$gotverificationtoken = $client->getLastRequest();
		preg_match('/.*wrap_verification_code=([^&]*)/', $gotverificationtoken, $m);
		$verificationtoken = $m[1];			
		$wrap_client_secret = Better_Config::getAppConfig()->msnsync->wrap_client_secret;	
		$request = array(
			'wrap_client_id' => $wrap_client_id,
			'wrap_callback' => $wrap_callback,
			'wrap_client_secret' => $wrap_client_secret,
			'wrap_verification_code' => $verificationtoken,
			'idtype' => 'CID'
			);	
		$url = Better_Config::getAppConfig()->msnsync->wrap_access_url."?wrap_client_id=".$wrap_client_id."&wrap_callback=".$wrap_callback."&wrap_client_secret=".$wrap_client_secret."&wrap_verification_code=".$verificationtoken."&idtype=CID";		
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer',Better_Config::getAppConfig()->msnsync->wrap_client_refresh_url);
		$client->request(Zend_Http_Client::POST);		
		$newhtml = $client->getLastResponse()->getBody();
		preg_match('/.*wrap_access_token=([^&]*)/', $newhtml, $m);	
		$wrap_access_token = $m[1];
		preg_match('/.*wrap_refresh_token=([^&]*)/', $newhtml, $m);
		$wrap_refresh_token = $m[1];
		preg_match('/.*uid=([^&]*)/', $newhtml, $m);
		$uid = $m[1];
		preg_match('/.*wrap_access_token_expires_in=([^&]*)/', $newhtml, $m);	
		$wrap_access_token_expires_in = $m[1];
		$loged = true;
		try{
			$b = gzcompress($wrap_access_token);
			$c = gzuncompress($b);			
			$loged && Better_Log::getInstance()->logInfo($this->c_accessToken."\n".$wrap_access_token."\n".$wrap_refresh_token."\n".$this->_cookies['c_accessToken']."\n".$uid."\n".$verificationtoken."\n".$b."\n".$c,'msntoken');
		} catch(Exception $e) {
			$loged && Better_Log::getInstance()->logInfo("false",'msntoken');
		} 
		$this->c_accessToken = $wrap_access_token;	
		$this->protocol_oauthtoken = $wrap_access_token;
		$this->protocol_oauthtokensecret = $wrap_refresh_token;
		$this->protocol_tid = $uid;	
		if($this->c_accessToken){
			$this->_logined = true;
		}		
		return $this->_logined;	
		
	}
	public function getrefreshtoken($wrap_refresh_token,$tid){	
		
		$wrap_client_id = Better_Config::getAppConfig()->msnsync->wrap_client_id;
		$wrap_client_secret = Better_Config::getAppConfig()->msnsync->wrap_client_secret;
		$postvars = 'wrap_refresh_token=' . $wrap_refresh_token
                . '&wrap_client_id=' . urlencode($wrap_client_id)
                . '&wrap_client_secret=' . urlencode($wrap_client_secret);		
		$posturl = 'https://consent.live.com/RefreshToken.aspx';		
		$ch = curl_init($posturl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $rec_Data = curl_exec($ch);
        curl_close($ch);       
       	$wrap_access_token= self::parsePOSTResponse(urldecode($rec_Data));
        return $wrap_access_token['wrap_access_token'];        
	}	
 	public function parsePOSTResponse($response)
    {
        // Firstly remove any extraneous header information from the returned
        // HTML
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
        return $returnedVariables;
    }
	
	public function post($msg,$attach='')
	{				
		$result = false;
		$log_array = array();
		$log_array[] = $this->_username;
		$log_array[] = $this->_password;		
		$k =0;		
		$loged = false;
		$loged && Better_Log::getInstance()->logInfo("dopost","postmsn");
		$syncinfo = Better_DAO_SyncQueue::getSyncbysiteuser('msn.com',$this->_username);
		$loginfrom = 'msn.com';		
		if($syncinfo['uid'] && (strlen($syncinfo['tid'])==0 || strlen($syncinfo['oauth_token'])==0)){
			self::login();			
			$c_uid = $this->tid;
			$c_accessToken = $this->protocol_oauthtoken;
			$wrap_refresh_token = $this->protocol_oauthtokensecret;	
			Better_User_Syncsites::getInstance($syncinfo['uid'])->delete($loginfrom);		
			Better_User_Syncsites::getInstance($syncinfo['uid'])->add('msn.com', $this->_username, $this->_password, $c_accessToken,$wrap_refresh_token,$c_uid);			
		} else {
			$c_uid = $syncinfo['tid'];
			$wrap_refresh_token = $syncinfo['oauth_token_secret'];
			if(time()-$syncinfo['dateline']>=17900){
				$c_accessToken = self::getrefreshtoken($wrap_refresh_token,$c_uid);
				Better_User_Syncsites::getInstance($syncinfo['uid'])->delete($loginfrom);	
				Better_User_Syncsites::getInstance($syncinfo['uid'])->add('msn.com', $this->_username, $this->_password,$c_accessToken, $wrap_refresh_token,$c_uid);
			} else {
				$c_accessToken = $syncinfo['oauth_token'];
			}
		}
		/*
		$c_uid = $this->tid;
		$c_accessToken = $this->protocol_oauthtoken;
		$wrap_refresh_token = $this->protocol_oauthtokensecret;
		*/
		$loged && Better_Log::getInstance()->logInfo($c_uid."\n".$c_accessToken."\n".$wrap_refresh_token,"postmsn");		
		if($attach){
			if (preg_match('/badges/', $attach)) { //勋章同步
				$_a = explode('/', $attach);
				$_name = array_pop($_a);
				$_a = explode('.', $_name);
				$_bid = array_shift($_a);
				
				$badgeInfo = Better_Badge::getBadge($_bid)->getParams();
				$badge_name = $badgeInfo['badge_name'];
				
				$attach_url = "http://k.ai/images/badges/big/$_bid.png";			
			} else {		
				$attach_url = str_replace('/usr/local/apache2/htdocs/public', 'http://k.ai', $attach);
			}
		}
		for($i=1;$i<2;$i++){			
			if($k==1){			
				Better_User_Syncsites::getInstance($syncinfo['uid'])->delete($loginfrom);	
				Better_User_Syncsites::getInstance($syncinfo['uid'])->add('msn.com', $this->_username, $this->_password,$c_accessToken, $wrap_refresh_token,$c_uid);
			}
			if($k>2){
				break;
			}	
			$c_uid = strtoupper($c_uid);							
			$url = "http://bay.apis.live.net/V4.0/cid-".$c_uid."/MyActivities";		
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->request();
			$html = $client->getLastResponse()->getBody();			
			$log_array[] = "url:$url";
			
			if($attach){
				$date = array(	
				'__type' => 'AddPhotoActivity:http://schemas.microsoft.com/ado/2007/08/dataservices',
				'ApplicationLink' => 'http://k.ai',		
				'ActivityObjects' => array(
						array(
							"ActivityObjectType" => "http://activitystrea.ms/schema/1.0/photo",
							"AlternateLink" => $attach_url,
							"PreviewLink" => $attach_url,
							"Title" =>$msg,
							'Description' => $msg,
							'Content' =>$msg
							),
					)
				);	
			} else {
				$date = array(	
					'__type' => 'AddStatusActivity:http://schemas.microsoft.com/ado/2007/08/dataservices',
					'ApplicationLink' => 'http://k.ai',		
					'ActivityObjects' => array(
							array(
								'Content' => $msg
								),
					)
				);	
			}		
			$request = json_encode($date);		
			$client1 = new Zend_Http_Client($url);		
			$client1->setRawData($request);
			$client1->setHeaders('Authorization', $c_accessToken);
			$client1->setHeaders('Accept', "application/json");				
			$client1->setHeaders('Content-Type',"application/json; charset=UTF-8");			
			$client1->setHeaders('Referer', Better_Config::getAppConfig()->msnsync->wrap_client_refresh_url);
			$client1->request(Zend_Http_Client::POST);
			$html = $client1->getLastResponse()->getBody();	
			$loged && Better_Log::getInstance()->logInfo(date("Y-m-d H:m:s",time()+8*3600)."\n 成功与否".$html,'postmsn');		
			$json = json_decode($html);	
			if($json->Code==412){
				$c_accessToken = self::getrefreshtoken($wrap_refresh_token,$c_uid);
				$loged && Better_Log::getInstance()->logInfo("Do refresh \n","postmsn");							
				$k++;
				$i=0;				
				continue;
			}
			if($client1->getLastResponse()->getStatus()=='200' || $client1->getLastResponse()->getStatus()=='201' ){
				$json->Id && $result = true;
			} 			
				
					
		}	
						
		return $result;
	}	
	
	public static function checkAccount($uid, $protocol, $username)
	{
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		foreach($sids as $sid) {
			$cs = Better_DAO_Base::assignDbConnection('user_server_' . $sid, true);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();		
			$sql = $rdb->quoteInto("SELECT * FROM `better_3rdbinding` WHERE `protocol`='$protocol' AND `username`=?", $username);
			$result = $rdb->query($sql);			
			$row = $result->fetch();		
			if ($row) {
				return false;
			}

		}		
		return true;
	}

	
	public function get3rdId()
	{
		return 0;
	}
	public function getHiddenFiledsByHtml($html) {
		$matches = array ();
		$actionarr = array ();
	
		preg_match_all ( '/<input type\="hidden" name\="([^"]+)".*?value\="([^"]*)"[^>]*>/si', $html, $matches );
		$values = $matches [2];
		$params = "";
		$i = 0;
		foreach ( $matches [1] as $name ) {
			$params .= "$name=" .  urlencode($values [$i])  . "&";
			++ $i;
		}
		return $params;
	}

//执行登录windows live 并返回验证令牌
	public function loginAuth($login, $password,$consenturl) {
		#the globals will be updated/used in the read_header function
		global $location;
		global $cookiearr;
		global $ch;		
		
		//Better_Log::getInstance()->logInfo("First:".time()."\n",'msntime');
		//$this->login();
		$ch = curl_init ();		
		
		$tm = time();
		$newurl = "https://login.live.com/login.srf?wa=wsignin1.0&rpsnv=11&ct=".$tm."&rver=6.1.6206.0&wp=SAPI_24HR&wreply=https:%2F%2Fconsent.live.com%2Fpp1000%2FDelegation.aspx%3FRU%3Dhttp%253A%252F%252Fblog.k.ai%252Flivecontacts%252Fexample.php%26ps%3DContacts.View%26pl%3Dhttp%253A%252F%252Fblog.k.aipolicy.php%26rollrs%3D04&lc=2052&id=252554";
		curl_setopt ( $ch, CURLOPT_URL,$newurl);
		curl_setopt ( $ch, CURLOPT_HEADER, 1);		
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );				
		$html = curl_exec ( $ch );		
		$loged = false;
		$loged && Better_Log::getInstance()->logInfo($newurl."\n",'msntime');	
		$loged && Better_Log::getInstance()->logInfo("Got Login:".time()."\n",'msntime');	
		$cookies = Better_Functions::get_cookies($html);	
		preg_match('/srf_uPost=\'([^\?]*)\?(.*?)\';/i', $html, $m);		
		$action = "https://login.live.com/ppsecure/post.srf?".$m[2];
		$params = self::getHiddenFiledsByHtml($html);
		$login = urlencode ( $login );
		$password = urlencode ( $password );		
		$fileds = $params . "login=" . $login . "&passwd=" . $password;		
		if(preg_match('/msn\.com/', $login)){
			$action="https://msnia.login.live.com/ppsecure/post.srf?".$m[2];			
		} 
		$loged && Better_Log::getInstance()->logInfo($action."--".$fileds."--".$cookies['str']."\n",'msntime');	
		$loged && Better_Log::getInstance()->logInfo("Login:".time()."\n",'msntime');		
		curl_setopt ( $ch, CURLOPT_URL, $action );
		curl_setopt ( $ch, CURLOPT_HEADER, 1);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );		
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_COOKIE, $cookies);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fileds );	
		$html2 = curl_exec( $ch );	
		$loged && Better_Log::getInstance()->logInfo($html2."\n",'msntimehtml2');			
		$params = self::getHiddenFiledsByHtml($html2);		
		curl_setopt ( $ch, CURLOPT_URL, $consenturl."&rollrs=04&wa=wsignin1.0"  );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_COOKIE, $cookies );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
		$html3 = curl_exec ( $ch );
		$loged && Better_Log::getInstance()->logInfo("Got Token:".time()."\n",'msntime');
		
		$responseform=strstr($html3,"responseform");		
		if(empty($responseform)){
			$newcookies=Better_Functions::get_cookies($html3);			
			$newurl = 'https://consent.live.com/pp1000/Delegation.aspx?RU=http%3a%2f%2fblog.k.ai%2flivecontacts%2fexample.php&ps=Contacts.View&pl=http%3a%2f%2fblog.k.aipolicy.php&rollrs=04&wa=wsignin1.0';			
			$params = self::getHiddenFiledsByHtml($html3)."&ctl00%24MainContent%24OfferRepeater%24ctl00%24ActionRepeater%24ctl00%24AcceptOfferCheck=Contacts.View&ctl00%24MainContent%24OfferRepeater%24ctl00%24ActionRepeater%24ctl00%24ExpiresDropDown%24ExpirationDropDown=365&ctl00%24MainContent%24SelectedDetails=&ctl00%24MainContent%24ConsentBtn=%E5%85%81%E8%AE%B8%E8%AE%BF%E9%97%AE";			
			$theend = $newurl;			
			curl_setopt ( $ch, CURLOPT_URL, $theend);
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_COOKIE, $newcookies );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );			
			$html3 = curl_exec ( $ch );
		}
		$finally=array();		
		preg_match_all('/ConsentToken"\s*value="(.*?)"/ism',$html3,$temp,PREG_SET_ORDER);
		$finally['ConsentToken']=str_replace("&#37;","%",$temp[0][1]);	
		$finally['ResponseCode']='RequestApproved';
		$finally['action']='delauth';
		$finally['appctx']='';
		curl_close($ch);
		$loged && Better_Log::getInstance()->logInfo(serialize($finally)."\n",'msntime');		
		return $finally;	
	} 
	
	public function getFriends()
	{			
		$userlist = array();
		$DEBUG = true;		
		$OFFERS = "Contacts.View";	
		$COOKIE = 'delauthtoken';
		$COOKIETTL = time() + (10 * 365 * 24 * 60 * 60);	
		$INDEX = 'index.php';		
		$HANDLER = 'delauth-handler.php';
		$wll = Better_Windownlive_Login::initFromXml('abc');		
		$consenturl = $wll->getConsentUrl($OFFERS);				
		$ch = curl_init ();
		//取得登录后的ConsentToken
		$login = $this->_username;
		$pass = $this->_password;	
		$loged = false;
		$finally=self::loginAuth($login, $pass,$consenturl);
		if($finally['ConsentToken']=='null' || $finally['ConsentToken']==''){
			return false;
			exit;
		}
		$cookie="";
		$consent = $wll->processConsent($finally); 	
		if ($consent) {
			$cookie = $consent->getToken();
		} else {
			die;
		}		 
		if ($cookie) {
			$token = $wll->processConsentToken($cookie);
		}		
		if ($token && !$token->isValid()) {
			$token = null;
		}	  
		if ($token) {
			// Convert Unix epoch time stamp to user-friendly format.
			$expiry = $token->getExpiry();
			$expiry = date(DATE_RFC2822, $expiry);	
			//*******************CONVERT HEX TO DOUBLE LONG INT ***************************************
			$hexIn = $token->getLocationID();
			$hi[19]=2328306436;		$lo[19]=2313682944;
			$hi[18]=232830643;		$lo[18]=2808348672;
			$hi[17]=23283064;		$lo[17]=1569325056;
			$hi[16]=2328306;		$lo[16]=1874919424;
			$hi[15]=232830;			$lo[15]=2764472320;
			$hi[14]=23283;			$lo[14]=276447232;
			$hi[13]=2328;			$lo[13]=1316134912;
			$hi[12]=232;			$lo[12]=3567587328;
			$hi[11]=23;				$lo[11]=1215752192;
			$hi[10]=2;				$lo[10]=1410065408;
			$hi[9]=0;				$lo[9]=1000000000;
			$hi[9]=0;				$lo[8]=100000000;
			$hi[9]=0;				$lo[7]=10000000;
			$hi[9]=0;				$lo[6]=1000000;
			$hi[9]=0;				$lo[5]=100000;
			$hi[9]=0;				$lo[4]=10000;
			$hi[9]=0;				$lo[3]=1000;
			$hi[9]=0;				$lo[2]=100;
			$hi[9]=0;				$lo[1]=10;
			$hi[9]=0;				$lo[0]=1;
			
			$hexTop = substr($hexIn,0,8);
			$hexBottom = substr($hexIn,8,8);
		
			$decTop = hexdec($hexTop);
			$decBottom = hexdec($hexBottom);		
			$sign="pos";			
			if ($decTop>2147483647)
			{
				$sign="neg";
				$decTop=$decTop-2147483648;
		
				$decTop=($decTop-2147483648)*-1 -1;
				$decBottom=($decBottom-4294967296)*-1;		
			}	
			for ($x=19;$x>9;$x--)
			{
				$div[$x]=0;
				while ($decTop>=$hi[$x] )
				{
					if ($decTop==$hi[$x] && $decBottom<$lo[$x])
					{
						break;
					}
					$div[$x]++;
					$decTop=$decTop-$hi[$x];
					if ($decBottom<$lo[$x])
					{				
						$decBottom=$decBottom-$lo[$x];
						$decBottom=4294967295+$decBottom;
						$decBottom++;
						$decTop--;
					}
					else
					{
						$decBottom=$decBottom-$lo[$x];
					}
				}
			}
		
			$x=9;
			$div[9]=0;
			
			while ($decTop>0)
			{
				if ($decBottom<$lo[$x])
				{				
					$decBottom=$decBottom-$lo[$x];
					$decBottom=4294967295+$decBottom;
					$decBottom++;
					$decTop--;
					$div[$x]++;
				}
				else 
				{
					$decBottom=$decBottom-$lo[$x];
					$div[$x]++;
				}
			}
		
			
			for ($x=9;$x>=0;$x--)
			{
		
				if ($x<9)
					$div[$x]=0;
				while ($decBottom>=$lo[$x])
				{
					$decTop=$decTop-$hi[$x];
					if ($decBottom<$lo[$x] && $decTop>0)
					{				
						$decBottom=$decBottom-$lo[$x];
						$decBottom=4294967295+$decBottom;
						$decBottom++;
						$decTop--;
						$div[$x]++;
					}
					else 
					{
						$decBottom=$decBottom-$lo[$x];
						$div[$x]++;
					}
				}
			}
			$output="";
			$x=19;
			while ($div[$x]=="0")
			{
				$div[$x]="";
				$x--;
			}
			if ($sign=="neg")
				$output="-";
			$output=$output.$div[19].$div[18].$div[17].$div[16].$div[15].$div[14].$div[13].$div[12].
					$div[11].$div[10].$div[9].$div[8].$div[7].$div[6].$div[5].$div[4].
					$div[3].$div[2].$div[1].$div[0];
				$longint=$output;		//here's the magic long integer to be sent to the Windows Live service
		
				//*******************CURL THE REQUEST ***************************************
				$uri = "https://livecontacts.services.live.com/users/@L@".$token->getLocationID()."/rest/livecontacts";
				//	    https://livecontacts.services.live.com/users/@L@<lid>/rest/livecontacts
				$dat_str=$token->getDelegationToken();
				
				//$ch = curl_init ();
				curl_setopt ( $ch, CURLOPT_URL, $uri );
				curl_setopt ( $ch, CURLOPT_HEADER, 0 );
				curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 1 );//DelegatedToken dt=
				curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Authorization: DelegatedToken dt="'.$token->getDelegationToken().'"'));
				curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
					
				$response_h = curl_exec ( $ch );
					
					
				curl_close ( $ch );
				$finally=array();
				//*******************PARSING THE RESPONSE ****************************************************
				$response=strstr($response_h,"<?xml version");
				$loged && Better_Log::getInstance()->logInfo($response,'msn'.time());
				try {
					$xml = new SimpleXMLElement($response);
				}
				catch (Exception $e) {
					echo $response_h."<br>".$uri;
					die;
				}
				
				$lengthArray=sizeof($xml->Contacts->Contact);
				for ($i=0;$i<$lengthArray;$i++)
				{
					//There can be more fields, depending on how you configure.  Here's
					//where you should access the fields and send them to the constructor
		
					$fn = $xml->Contacts->Contact[$i]->Profiles->Personal->FirstName;
					$ln = $xml->Contacts->Contact[$i]->Profiles->Personal->LastName;
					$em = $xml->Contacts->Contact[$i]->Emails->Email->Address;
					$finally[$i]['nickname'] = $ln[0].$fn[0];
					$finally[$i]['email'] = (string)$em;
					/*
					$finally[0][$i]=$ln[0].$fn[0];
					$finally[1][$i]=(string)$em;
					*/
					//instantiate an object and add it to the array
					//$person_array[]=new Person($fn,$ln,$em);
				}		
			}	
		$loged && Better_Log::getInstance()->logInfo(serialize($finally)."\n",'msntime');	
		$loged && Better_Log::getInstance()->logInfo("Got Userlist:".time()."\n",'msntime');		
		return $finally;
	}

	
	public function getFriendsbyport(){
		$userlist = array();
		$porturl = '10.10.1.251:7766';
		$xml = "<kai version='1.0'>";		
		$xml .= '<login account="'.$this->_username.'" password="'.$this->_password.'" />';
		$xml .= "</kai>";		
		$packet = "HTTP/1.1 \r\n";
		$packet .= "Content-Length: ".strlen($xml)."\r\n";
		$packet .= "\r\n";
		$packet .= $xml;
		try{			
			$streamContext = stream_context_create();
			$apns = stream_socket_client('tcp://' . $porturl, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);			
			fwrite($apns, $packet);
			$i=0;
			while (!feof($apns)) {
				
			  $x .= fread($apns, 50000000);
			  $i++;
			}			
			fclose($apns);		
			$userlist = explode("|",$x);
			unset($userlist[0]);
			array_pop($userlist);
			$finally = array();
			foreach($userlist as $row){
				$finally[]['nickname'] = $row;
				$finally[]['email'] = $row;
			}
			/*
			foreach($tempuser as $row){
				if(strlen($row)>0){
					$userlist[] = $row;
				}
			}		
			*/
			//Better_Log::getInstance()->logInfo($x."\n".$i,'syncmsn',true);
		} catch(Exception $e){
			//Better_Log::getInstance()->logInfo('','syncmsn',true);	
		}
		/*
		if (!strpos($x, 'error') && strpos($x, 'ok')) {
			$this->_logined = true;
		}
		*/
		return $finally;
	}
}