<?php

/**
 * 用户注册
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Signup extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	protected static function _recUids(array $params)
	{
		$results = array();
		$excludeUids = array(
			$params['uid'],
			);
		$cfgExcludeUids = (array)explode('|', Better_Config::getAppConfig()->reg_active_exclude);
		$eUids = array_merge($excludeUids, $cfgExcludeUids);
		
		$tmp = Better_DAO_User_Search::getInstance()->search('REC_UIDS', $eUids, $params);
		
		foreach ($tmp as $row) {
			$results[] = $row['uid'];	
		}		
		
		if (count($results)<$params['count'] && $params['range']<1000000000) {
			$params['range'] = 10*$params['range'];
			Better_Log::getInstance(count($result).'|'.$params['count'], 'debug', true);
			$results = self::_recUids($params);
		}
		
		return $results;
	}
	
	/**
	 * 注册第四步的推荐用户
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function recUsers(array $params)
	{
		$results = array();
		$result = array(
			'count' => 0,
			'rows' => array(),
			'page' => 0,
			'pages' => 0,
			);
		
		$inBj = false;
		$alreadyUids = array();
		if (Better_LL::isValidLL($params['lon'], $params['lat'])) {
			$distance = Better_Service_Lbs::getDistance($params['lon'], $params['lat'], Better_Config::getAppConfig()->location->default_lon, Better_Config::getAppConfig()->location->default_lat);
			if ($distance<20000) {
				$inBj = true;
			}
		}
		
		if ($inBj) {
			$fuckingUids = array(180950, 185924, 186145);
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($fuckingUids, 1, 10, '', 'karma');

			$result['count'] = count($results);
			$user = Better_Registry::get('user');

			$rows = $tmp['rows'];
			
			foreach ($rows as $key=>$value) {
				$value['message'] = Better_Blog::dynFilterMessage($value['message']);
				$value['status'] = $value['status'] ? unserialize($value['status']) : array();
				$value['location_tips'] = Better_User::filterLocation($value, 'blog');
				$alreadyUids[] = $value['uid'];

				$result['rows'][] = $user->parseUser($value);
				$result['emails'][] = $value['email'];
			}
		}
					
		$results = self::_recUids($params);
		if (count($results)>0) {
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($results, $params['page'] ? $params['page'] : 1, $params['count'], '', 'karma');

			$result['count'] = count($results);
			$user = Better_Registry::get('user');

			$rows = $tmp['rows'];
			
			foreach ($rows as $key=>$value) {
				$value['message'] = Better_Blog::dynFilterMessage($value['message']);
				$value['status'] = $value['status'] ? unserialize($value['status']) : array();
				$value['location_tips'] = Better_User::filterLocation($value, 'blog');

				$rowUserInfo = $user->parseUser($value);

				$result['rows'][] = $rowUserInfo;
				$result['emails'][] = $value['email'];
			}
		}
				
		if (count($result['rows'])>10) {
			$tmp = array_chunk($result['rows'], 10);
			$result['rows'] = $tmp[0];
			$result['count'] = 10;
		}

		return $result;
	}
	
	/**
	 * 检测用户名的有效性
	 * 
	 * @return array
	 */
	public static function validUsername($username, $uid=0)
	{//illegal
		$codes = array(
			'VALID' => 1,
			'INVALIDE' => 0,
			'TOO_SHORT' => -1,
			'TOO_LONG' => -2,
			'ILLEGAL_CHARACTER' => -3,
			'ONLY_NUMBER' => -4,
			'ONLY_NUMBER_CHARACTER' => -5,
			'EXISTS' => -6
			);
		$code = $codes['INVALID'];	
			
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
			
		$username = trim($username);
		if (strlen($username)<5) {
			$code = $codes['TOO_SHORT'];
		} else if (strlen($username)>20) {
			$code = $codes['TOO_LONG'];
		} else if (!preg_match('/^([0-9a-zA-Z]{4,20})$/', $username)) {
			$code = $codes['ONLY_NUMBER_CHARACTER'];
		} else if (preg_match('/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/', $username) || (preg_match('/^kai([0-9].+)$/', $username) && str_replace('kai', '', $username)!=$uid) || Better_Filter::getInstance()->filterBanwords($username) || Better_Filter::getInstance()->filterNameBanwords($username)) {
			$code = $codes['ILLEGAL_CHARACTER'];
		} else if (preg_match($patInvalid, $username)) {
			$code = $codes['ILLEGAL_CHARACTER'];
		} else if (preg_match('/(\/|\.|@|\?|\&)/', $username)) {
			$code = $codes['ILLEGAL_CHARACTER'];
		} else if (preg_match($patChinese, $username)) {
			$code = $codes['ILLEGAL_CHARACTER'];
		} else {
			if ($uid>0) {
				$exists = Better_User::getInstance($uid)->exists()->username($username, Better_User_Exists::PROFILE);
			} else {
				$exists = Better_User_Exists::getInstance()->username($username, Better_User_Exists::PROFILE);
			}
			
			if ($exists) {
				$code = $codes['EXISTS'];
			} else {
				$code = $codes['VALID'];
			}
		} 
		
		return array(
			'codes' => &$codes,
			'code' => $code,
			);
	}
	
	public static function validNickname($nickname, $uid=0)
	{
		$codes = array(
			'NICKNAME_TOO_LONG' => -5,
			'NICKNAME_FORBIDEN_WORD' => -6,
			'NICKNAME_EXISTS' => -7,
			'NICKNAME_TOO_SHORT' => -11,
			'NICKNAME_REQUIRED' => -13,
			'FAILED' => 0,
			'VALID' => 1,
			);
		$code = $codes['FAILED'];		
	
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";	
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		//$pat = '#(?:@|＠)([^@\s\n\r\t,:]+)[\s\n\r\t,:]*#is';		
		if (strlen($nickname)) {
			if (mb_strlen($nickname)>20) {
				$code = $codes['NICKNAME_TOO_LONG'];
			} else if (strlen($nickname)<5) {
				$code = $codes['NICKNAME_TOO_SHORT'];
			}  else if (preg_match($patAT, $nickname)) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} else if (Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
				$code = $codes['NICKNAME_EXISTS'];
			} else if (preg_match($patInvalid, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname) || $nickname_pat) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} else {
				$code = $codes['VALID'];
			}
		} else {
			$code = $codes['NICKNAME_REQUIRED'];
		}		
		
		return array(
			'codes' => &$codes,
			'code' => $code
			);
	}
	
	/**
	 * 检测注册数据的有效性
	 * 
	 * @param $params
	 * @return array
	 */
	public static function check(array $params)
	{
		$err = array();
		$lang = Better_Language::load();
		$begintime = time();
		$email = trim($params['email']);
		if ($email=='') {
			$err['err_email'] = $lang->error->empty_email;
		} else if (strlen($email)>=50) {
			$err['err_email'] = $lang->error->email_too_long;
		} else {
			if (Better_Functions::checkEmail($email)) {
				Better_User_Exists::getInstance()->email($email) && $err['err_email'] = $lang->error->email_exists;
			} else {
				$err['err_email'] = $lang->error->email_invalid;
			}
		}
		
		$pwd = $params['password'];
		$repwd = $params['repassword'];
		if (strlen($pwd)<6) {
			$err['err_password'] = $lang->signup->password_to_short;
		}		
		if (strlen($pwd)>20) {
			$err['err_password'] = $lang->signup->password_too_long;
		}  else if ($repwd!=$pwd) {
			$err['err_repassword'] = $lang->signup->password_not_match;
		}
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		
		$username = $params['username'];
		/*
		if($username==''){
			$err['err_username'] = $lang->signup->username->empty;
		}else if (mb_strlen($username)>20) {
			$err['err_username'] = $lang->signup->username->too_long;
		} else if (mb_strlen($username)<5) {
			$err['err_username'] = $lang->signup->username->too_short;
		} else if (preg_match($patAT, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_at;
		} else if (preg_match($patInvalid, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_space;
		} else if(preg_match($patChinese, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_chinese;
		} else if(!preg_match($patNoSpecialChar, $username) || preg_match($patUrl, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_specialChar;
		} else if (strpos($patControllers, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (Better_User_Exists::getInstance()->username($username, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($username) || Better_Filter::getInstance()->filterNameBanwords($username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (preg_match($patKai, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		}
	//	$err['err_username'] = Better_Config::getAppConfig()->routes->exclude_controllers;
		$reservedname = explode("|",Better_Config::getAppConfig()->routes->exclude_controllers);
		for($i=0;$i<count($reservedname);$i++){
			$finduser = strpos($username,$reservedname[$i]);
			if($finduser !== false && $finduser==0){
				$err['err_username'] = $lang->signup->username->already_taken;
				break;				
			}
		}		*/
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";
		
		
		
		$nickname = $params['nickname'];
		
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		
		if ($nickname=='') {
			$err['err_nickname'] = $lang->signup->nickname->empty;
		} else if (mb_strlen($nickname)>20) {
			$err['err_nickname'] = $lang->signup->nickname->too_long;
		} else if (strlen($nickname)<5) {
			$err['err_nickname'] = $lang->signup->nickname->too_short;
		} else if (preg_match($patAT, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_at;
		} else if (Better_User_Exists::getInstance()->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
			$err['err_nickname'] =$lang->signup->nickname->already_taken;
		} else if (preg_match($patInvalid, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_space;
		} else if (preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname) || $nickname_pat) {
			$err['err_nickname'] = $lang->singup->nickname->forbidden_quote;
		}
	
		$scode = trim($params['code']);
		if ($scode=='') {
			$err['err_code'] = $lang->signup->scode->empty;
		} else if (Better_Registry::get('sess')->get('authCode')!=$scode) {
			$err['err_code'] = $lang->signup->scode->wrong;
		}
		
		$phone = $params['phone'] ? $params['phone'] : ($params['cell_no'] ? $params['cell_no'] : '');
		$phone = trim($phone);
		if ($phone!='') {
			$user = Better_User::getInstance($phone, 'cell');
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$error['err_phone'] = $lang->signup->phone_exists;
			}
		}		
		
		$agree = $params['agree'];
		if (!$agree) {
			$err['err_agree'] = $lang->signup->must_agree;
		}	
		

		/*if ($code!=$codes['SUCCESS']) {
			Better_Log::getInstance()->logInfo('Code:['.$code.'], Params:['.json_encode($params).']', 'signup_exception');
		}*/		
		
		$endtime = time();
		$dotime = $endtime-$begintime;
		Better_Log::getInstance()->logInfo("执行时间:".$dotime." 返回".serialize($err),'checkedtm');
		return $err;
	}
	
	/**
	 * 快速注册
	 * 
	 * @param $params
	 * @return array
	 */
	public static function quickSignup(array $params)
	{
		$result = self::quickCheck($params);
		
		if ($result['code']==$result['codes']['SUCCESS']) {
			$uid = self::signup($params);
			$result['uid'] = $uid;
			
			if (!$uid) {
				$result['code'] = $result['codes']['FAILED'];
			}
		}
		
		return $result;
	}
	
	/**
	 * 新版快速注册
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function quickSignupVer2(array $params)
	{
		$result = self::quickCheckVer2($params);
		
		if ($result['code']==$result['codes']['SUCCESS']) {
			
			$uid = self::signup($params);
			$result['uid'] = $uid;
			
			if (!$uid) {
				$result['code'] = $result['codes']['FAILED'];
			}
		}
		
		return $result;		
	}
	
	/**
	 * 快速注册检测
	 * 
	 * @param $params
	 * @return array
	 */
	public static function quickCheck(array $params)
	{
		$msg = '';
		$codes = array(
			'EMAIL_INVALID' => -1,
			'EMAIL_EXISTS' => -2,
			'SUCCESS' => 1,
			'FAILED' => 0,
			'PASSWORD_INVALID' => -3,
			'CELL_EXISTS' => -4,
			'CELL_INVALID' => -15,
		
			'NICKNAME_TOO_LONG' => -5,
			'NICKNAME_FORBIDEN_WORD' => -6,
			'NICKNAME_EXISTS' => -7,
			'NICKNAME_TOO_SHORT' => -11,
			'NICKNAME_REQUIRED' => -13,
		
			'USERNAME_TOO_LONG' => -8,
			'USERNAME_FORBIDEN_WORD' => -9,
			'USERNAME_EXISTS' => -10,
			'USERNAME_TOO_SHORT' => -12,
			'USERNAME_REQUIRED' => -14,
		
			'PASSWORD_TOO_LONG' => -16,
			'PASSWORD_TOO_SHORT' => -17,
			'PASSWORD_NOT_MATCH' => -18,
		
			'EMAIL_TOO_LONG' => -19,
		
			'BAN_WORDS'=> -20,
			
			'USER_INTRO_TOO_LANG'=> -21,
			);
		$code = $codes['FAILED'];
		
		$lang = Better_Language::load();

		$email = trim($params['email']);
		if ($email=='') {
			$code = $codes['EMAIL_INVALID'];
		} else if (strlen($email)>=50) {
			$code = $codes['EMAIL_TOO_LONG'];
		} else {
			if (Better_Functions::checkEmail($email)) {
				$uid = isset($params['uid']) ? (int)$params['uid'] : 0;
				if (Better_User_Exists::getInstance($uid)->email($email)) {
					$code = $codes['EMAIL_EXISTS'];
				} else {
					$code = $codes['SUCCESS'];
					$msg = str_replace('%s', $email, $lang->signup->active->tips);
				}
			} else {	
				$code = $codes['EMAIL_INVALID'];
			}
		}
		
		if (isset($params['passby_pass']) && $params['passby_pass']) {
			
		} else {
			$pwd = $params['password'];
			$repwd = $params['repassword'];
			
			if (strlen($pwd)<6) {
				$code = $codes['PASSWORD_TOO_SHORT'];
			} else if (strlen($pwd)>20) {
				$code = $codes['PASSWORD_TOO_LONG'];
			} else if (trim($pwd)!=trim($repwd)) {
				$code = $codes['PASSWORD_NOT_MATCH'];
			}
		}
		
		if (isset($params['passby_cell']) && $params['passby_cell']) {
			
		} else {
			$phone = $params['phone'] ? $params['phone'] : ($params['cell_no'] ? $params['cell_no'] : '');
			$phone = trim($phone);
			if ($phone!='') {
				
				if (preg_match('/^([0-9]{8,26})$/', $phone)) {
					$user = Better_User::getInstance($phone, 'cell');
					$userInfo = $user->getUserInfo();
					
					if ($userInfo['uid']) {
						$code = $codes['CELL_EXISTS'];
					}
				} else {
					$code = $codes['CELL_INVALID'];
				}
			}
		}
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";		
		
		$nickname = $params['nickname'];
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		
		if (strlen($nickname)) {
			if (mb_strlen($nickname)>20) {
				$code = $codes['NICKNAME_TOO_LONG'];
			} else if (strlen($nickname)<5) {
				$code = $codes['NICKNAME_TOO_SHORT'];
			}  else if (preg_match($patAT, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname)) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} else if (Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
				$code = $codes['NICKNAME_EXISTS'];
			} else if (preg_match($patInvalid, $nickname) || $nickname_pat) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} 
		} else {
			$code = $codes['NICKNAME_REQUIRED'];
		}
		
		
		$username = $params['username'];
		$result = Better_User_Validator::username($username, $params['uid']);
		$cs = &$result['codes'];
		
		switch ($result['code']) {
			case $cs['USERNAME_TOO_LONG']:
				$code = $codes['USERNAME_TOO_LONG'];
				break;
			case $cs['USERNAME_FORBIDEN_WORD']:
				$code = $codes['USERNAME_FORBIDEN_WORD'];
				break;
			case $cs['USERNAME_EXISTS']:
				$code = $codes['USERNAME_EXISTS'];
				break;
			case $cs['USERNAME_TOO_SHORT']:
				$code = $codes['USERNAME_TOO_SHORT'];
				break;
			case $cs['USERNAME_REQUIRED']:
				$code = $codes['USERNAME_REQUIRED'];
				break;
		}
		
		//自我介绍禁止词
		$self_intro = $params['self_intro'];
		if(Better_Filter::getInstance()->filterBanwords($self_intro)){
			$code = $codes['BAN_WORDS'];
		}
		if (mb_strlen($self_intro, 'UTF-8') > 50) {
			$code = $codes['USER_INTRO_TOO_LANG'];
		}
		if ($code!=$codes['SUCCESS']) {
			Better_Log::getInstance()->logInfo('Code:['.$code.'], Params:['.json_encode($codes).']', 'signup_exception');
		}
		
		return array(
			'codes' => $codes,
			'code' => $code,
			'msg' => $msg
			);
	}
	
	/**
	 * 快速注册检测版本2
	 * 
	 * @param $params
	 * @return array
	 */
	public static function quickCheckVer2(array $params)
	{
		$msg = '';
		$codes = array(
			'EMAIL_INVALID' => -1,
			'EMAIL_EXISTS' => -2,
			'SUCCESS' => 1,
			'FAILED' => 0,
			'PASSWORD_INVALID' => -3,
			'CELL_EXISTS' => -4,
			'CELL_INVALID' => -15,
		
			'NICKNAME_TOO_LONG' => -5,
			'NICKNAME_FORBIDEN_WORD' => -6,
			'NICKNAME_EXISTS' => -7,
			'NICKNAME_TOO_SHORT' => -11,
			'NICKNAME_REQUIRED' => -13,

			'PASSWORD_TOO_LONG' => -16,
			'PASSWORD_TOO_SHORT' => -17,
			'PASSWORD_NOT_MATCH' => -18,
		
			'EMAIL_TOO_LONG' => -19,
		
			'BAN_WORDS'=> -20,
			);
		$code = $codes['FAILED'];
		
		$lang = Better_Language::load();

		$email = trim($params['email']);
		if ($email=='') {
			$code = $codes['EMAIL_INVALID'];
		} else if (strlen($email)>=50) {
			$code = $codes['EMAIL_TOO_LONG'];
		} else {
			if (Better_Functions::checkEmail($email)) {
				$uid = isset($params['uid']) ? (int)$params['uid'] : 0;
				if (Better_User_Exists::getInstance($uid)->email($email)) {
					$code = $codes['EMAIL_EXISTS'];
				} else {
					$code = $codes['SUCCESS'];
					$msg = str_replace('%s', $email, $lang->signup->active->tips);
				}
			} else {	
				$code = $codes['EMAIL_INVALID'];
				Better_Log::getInstance()->logInfo($email, 'email_invalid', true);
			}
		}
		
		if (isset($params['passby_pass']) && $params['passby_pass']) {
			
		} else {
			$pwd = $params['password'];
			$repwd = $params['repassword'];
			
			if (strlen($pwd)<6) {
				$code = $codes['PASSWORD_TOO_SHORT'];
			} else if (strlen($pwd)>20) {
				$code = $codes['PASSWORD_TOO_LONG'];
			} else if (trim($pwd)!=trim($repwd)) {
				$code = $codes['PASSWORD_NOT_MATCH'];
			}
		}
		
		if (isset($params['passby_cell']) && $params['passby_cell']) {
			
		} else {
			$phone = $params['phone'] ? $params['phone'] : ($params['cell_no'] ? $params['cell_no'] : '');
			$phone = trim($phone);
			if ($phone!='') {
				
				if (preg_match('/^([0-9]{8,26})$/', $phone)) {
					$user = Better_User::getInstance($phone, 'cell');
					$userInfo = $user->getUserInfo();
					
					if ($userInfo['uid']) {
						$code = $codes['CELL_EXISTS'];
					}
				} else {
					$code = $codes['CELL_INVALID'];
				}
			}
		}
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";		
		
		$nickname = $params['nickname'];
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		if (strlen($nickname)) {
			if (mb_strlen($nickname)>20) {
				$code = $codes['NICKNAME_TOO_LONG'];
			} else if (strlen($nickname)<5) {
				$code = $codes['NICKNAME_TOO_SHORT'];
			}  else if (preg_match($patAT, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname)) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} else if (Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
				$new_nikename = Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->getNewNickname($nickname);
				Better_Registry::set('new_nickname', $new_nikename);
				$code = $codes['NICKNAME_EXISTS'];
			} else if (preg_match($patInvalid, $nickname) || $nickname_pat) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
			} 
		} else {
			$code = $codes['NICKNAME_REQUIRED'];
		}
		
		//自我介绍禁止词
		$self_intro = $params['self_intro'];
		if(Better_Filter::getInstance()->filterBanwords($self_intro)){
			$code = $codes['BAN_WORDS'];
		}
		
		if ($code!=$codes['SUCCESS']) {
			Better_Log::getInstance()->logInfo('Code:['.$code.'], Params:['.json_encode($codes).']', 'signup_exception');
		}
		
		return array(
			'codes' => $codes,
			'code' => $code,
			'msg' => $msg
			);
	}	
	
	/**
	 * 执行注册操作
	 * 
	 * @param $data
	 * @return integer
	 */
	public static function signup(array $data)
	{
		$uid = 0;
		
		$salt = Better_Functions::genSalt();
		$s = array();
		$s['email'] = strtolower($data['email']);
		$s['username'] = $data['username'];
		$s['password'] = md5($data['password']);
		$s['salt'] = $salt;
		$s['regtime'] = time();
		$s['regip'] = Better_Functions::getIP();
		$s['nickname'] = $data['nickname'];
		$s['self_intro'] = $data['self_intro'];
		$s['profile'] = '';
		$s['blog'] = '';
		$s['location'] = '';
		$s['x'] = 0;
		$s['y'] = 0;
		$s['state'] = Better_User_State::SIGNUP_VALIDATING;
		$s['partner'] = $data['partner'];

		if (isset($data['secret'])) {
			if (!Better_Imei::exists(Better_Imei::decrypt($data['secret']))) {
				$s['partner'] = $data['partner'];
			}
		} else {
			$s['partner'] = $data['partner'];
		}

		$s['last_update'] = time();
		
		if (isset($data['language'])) {
			$s['language'] = $data['language'];	
		}
		
		if (isset($data['birthday'])) {
			$s['birthday'] = $data['birthday'];
		}
		
		if (isset($data['gender'])) {
			$s['gender'] = $data['gender'];
		}
		
		$ref = (int)Better_Registry::get('sess')->get('ref_uid');
		if ($ref) {
			$s['ref_uid'] = $ref;
			Better_Registry::get('sess')->set('ref_uid');
		} else {
			$s['ref_uid'] = 0;
		}

		$s['uid'] = Better_User_Sequence::genUid();

		if ($s['uid']) {
			$s['username']=='' && $s['username'] = 'kai'.$s['uid'];
			$s['nickname']=='' && $s['nickname'] = 'kai'.$s['uid'];

			$uid = Better_DAO_User::getInstance()->insert($s);

			if ($uid) {
				Better_User::getInstance($uid)->getUser();

				Better_Hook::factory(array(
					'Filter', 'Email', 'DirectMessage', 'User', 'Karma', 'Badge', 'Meeting', 'Secret', 'Rp'
					))->invoke('UserCreated', array(
					'userInfo' => $s,
					'data' => &$data
					));
				Better_Hook::factory(array(
					'Rp'
				))->invoke('UserLogin', array(
					'uid' => $uid,	
					'autologin' => 1,
				));
				$cell = $data['phone'] ? $data['phone'] : ($data['cell_no'] ? $data['cell_no'] : '');
					
				if ($cell) {
					$flag = Better_User::getInstance($uid)->bind_Cell()->request($cell);
					if ($flag) {
						$msg = str_replace('{ROBOT}', Better_Config::getAppConfig()->cell->robot, str_replace('{UID}', $uid, Better_Language::load()->signup->cell_reg));
						Better_Registry::set('signupMsg', $msg);
					}
				}

			} 
		}

		return $uid;		
	}
	
	/**
	 * WAP注册项检测检测
	 * 
	 * @param $params
	 * @return array
	 */
	public static function wapCheck(array $params)
	{
		$err = array();
		$lang = Better_Language::load();
		$email = trim($params['email']);
		if ($email=='') {
			$err['err_email'] = $lang->error->empty_email;
		} else if (strlen($email)>=50) {
			$err['err_email'] = $lang->error->email_too_long;
		} else {
			if (Better_Functions::checkEmail($email)) {
				Better_User_Exists::getInstance()->email($email) && $err['err_email'] = $lang->error->email_exists;
			} else {
				$err['err_email'] = $lang->error->email_invalid;
			}
		}
		
		$pwd = $params['password'];
		$repwd = $params['repassword'];
		if (strlen($pwd)<6) {
			$err['err_password'] = $lang->signup->password_to_short;
		}		
		if (strlen($repwd)>20) {
			$err['err_repassword'] = $lang->signup->password_too_long;
		} else if ($repwd!=$pwd) {
			$err['err_repassword'] = $lang->signup->password_not_match;
		}
		
		$patRT = '/^RT(.*)/is';
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";		
		
		$nickname = $params['nickname'];
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		if ($nickname=='') {
			$err['err_nickname'] = $lang->signup->nickname->empty;
		} else if (mb_strlen($nickname)>20) {
			$err['err_nickname'] = $lang->signup->nickname->too_long;
		} else if (strlen($nickname)<5) {
			$err['err_nickname'] = $lang->signup->nickname->too_short;
		} else if (preg_match($patRT, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_rt;
		} else if (preg_match($patAT, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_at;
		} else if (Better_User_Exists::getInstance()->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
			$err['err_nickname'] =$lang->signup->nickname->already_taken;
		} else if (preg_match($patInvalid, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname) || $nickname_pat) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_space;
		}
/*
		$username = $params['username'];
		if($username==''){
			$err['err_username'] = $lang->signup->username->empty;
		}else if (mb_strlen($username)>20) {
			$err['err_username'] = $lang->signup->username->too_long;
		} else if (mb_strlen($username)<5) {
			$err['err_username'] = $lang->signup->username->too_short;
		} else if (preg_match($patAT, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_at;
		} else if (preg_match($patInvalid, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_space;
		} else if(preg_match($patChinese, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_chinese;
		} else if(!preg_match($patNoSpecialChar, $username) || preg_match($patUrl, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_specialChar;
		} else if (preg_match($patControllers, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (Better_User_Exists::getInstance()->username($username, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($username) || Better_Filter::getInstance()->filterNameBanwords($username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (preg_match($patKai, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		}	*/
		$scode = trim($params['code']);
		if ($scode=='') {
			$err['err_code'] = $lang->signup->scode->empty;
		} else if (Better_Registry::get('sess')->get('authCode')!=$scode) {
			$err['err_code'] = $lang->signup->scode->wrong;
		}
		$agree = $params['agree'];
		if (!$agree) {
			$err['err_agree'] = $lang->signup->must_agree;
		}		
		return $err;
	}
/**
	 * WAP快速注册
	 * 
	 * @param $params
	 * @return array
	 */
	public static function wapSignup(array $params)
	{

		$result = self::wapCheck($params);
		if ($result['code']==$result['codes']['SUCCESS']) {
			
			$uid = self::signup($params);
			if (!$uid) {
				$result['code'] = $result['codes']['FAILED'];
			} 
		}		
		return $uid;
	}
	
	
	/**
	 * 执行不需要激活的注册操作
	 * 
	 * @param $data
	 * @return integer
	 */
	public static function registe(array $data)
	{
		$uid = 0;
		
		$salt = Better_Functions::genSalt();
		$s = array();
		$s['email'] = $data['email'];
		$s['username'] = $data['username'];
		$s['password'] = md5($data['password']);
		$s['salt'] = $salt;
		$s['regtime'] = time();
		$s['regip'] = Better_Functions::getIP();
		$s['nickname'] = $data['nickname'];
		$s['self_intro'] = $data['self_intro'];
		$s['profile'] = '';
		$s['blog'] = '';
		$s['location'] = '';
		$s['x'] = 0;//Better_Config::getAppConfig()->location->default_x;
		$s['y'] = 0;//Better_Config::getAppConfig()->location->default_y;
		$s['state'] = Better_User_State::ENABLED; //不需要激活
		$s['enabled'] = '1';
		
		if (isset($data['birthday'])) {
			$s['birthday'] = $data['birthday'];
		}
		
		if (isset($data['gender'])) {
			$s['gender'] = $data['gender'];
		}
		
		$ref = Better_Registry::get('sess')->get('ref_uid');
		if ($ref) {
			$s['ref_uid'] = $ref;
			Better_Registry::get('sess')->set('ref_uid');
		} else {
			$s['ref_uid'] = 0;
		}

		$s['uid'] = Better_User_Sequence::genUid();

		if ($s['uid']) {
			$s['username']=='' && $s['username'] = 'kai'.$s['uid'];
			$s['nickname']=='' && $s['nickname'] = 'kai'.$s['uid'];

			$uid = Better_DAO_User::getInstance()->insert($s);

			if ($uid) {
				Better_User::getInstance($uid)->getUser();

				Better_Hook::factory(array(
					'Filter', 'User', 'Karma', 'Badge', 'Secret'
					))->invoke('UserCreated', array(
					'userInfo' => $s,
					'data' => &$data
					));
				$cell = $data['phone'] ? $data['phone'] : ($data['cell_no'] ? $data['cell_no'] : '');
					
				if ($cell) {
					$flag = Better_User::getInstance($uid)->bind_Cell()->request($cell);
					if ($flag) {
						$msg = str_replace('{ROBOT}', Better_Config::getAppConfig()->cell->robot, str_replace('{UID}', $uid, Better_Language::load()->signup->cell_reg));
						Better_Registry::set('signupMsg', $msg);
					}
				}

			}
		}

		return $uid;		
	}
	
	
	/**
	 * 不需要激活的检测
	 * 
	 * @param $params
	 * @return array
	 */
	public static function checkregiste(array $params)
	{
		$err = array();
		$lang = Better_Language::load();
		
		$email = trim($params['email']);
		if ($email=='') {
			$err['err_email'] = $lang->error->empty_email;
		} else {
			if (Better_Functions::checkEmail($email)) {
				Better_User_Exists::getInstance()->email($email) && $err['err_email'] = $lang->error->email_exists;
			} else {
				$err['err_email'] = $lang->error->email_invalid;
			}
		}
		
		$pwd = $params['password'];
		$repwd = $params['repassword'];
		if (strlen($pwd)<6) {
			$err['err_password'] = $lang->signup->password_to_short;
		}
		
		if (strlen($repwd)<6) {
			$err['err_repassword'] = $lang->signup->password_to_short;
		} else if ($repwd!=$pwd) {
			$err['err_repassword'] = $lang->signup->password_not_match;
		}
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';

		$username = $params['username'];
		if($username==''){
			$err['err_username'] = $lang->signup->username->empty;
		}else if (mb_strlen($username)>20) {
			$err['err_username'] = $lang->signup->username->too_long;
		} else if (mb_strlen($username)<3) {
			$err['err_username'] = $lang->signup->username->too_short;
		} else if (preg_match($patAT, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_at;
		} else if (preg_match($patInvalid, $username)) {
			$err['err_username'] = $lang->signup->username->forbidden_space;
		} else if(preg_match($patChinese, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_chinese;
		} else if(!preg_match($patNoSpecialChar, $username) || preg_match($patUrl, $username)){
			$err['err_username'] = $lang->signup->username->forbidden_specialChar;
		} else if (preg_match($patControllers, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (Better_User_Exists::getInstance()->username($username, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($username) || Better_Filter::getInstance()->filterNameBanwords($username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		} else if (preg_match($patKai, $username)) {
			$err['err_username'] = $lang->signup->username->already_taken;
		}
		
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";
		
		$nickname = $params['nickname'];
		$patarray = array();
		$patarray[] = '/(:)/';
		$patarray[] = '/(,)/';
		$patarray[] = '/(，)/';
		$patarray[] = '/(：)/';
		$patarray[] = '/(、)/';
		$nickname_pat = 0;
		foreach($patarray as $row){
			if (preg_match($row, $nickname)){
				$nickname_pat = 1;
			}			
		}
		if ($nickname=='') {
			$err['err_nickname'] = $lang->signup->nickname->empty;
		} else if (mb_strlen($nickname)>20) {
			$err['err_nickname'] = $lang->signup->nickname->too_long;
		} else if (strlen($nickname)<3) {
			$err['err_nickname'] = $lang->signup->nickname->too_short;
		} else if (preg_match($patAT, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_at;
		} else if (Better_User_Exists::getInstance()->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
			$err['err_nickname'] =$lang->signup->nickname->already_taken;
		} else if (preg_match($patInvalid, $nickname)) {
			$err['err_nickname'] = $lang->signup->nickname->forbidden_space;
		} else if (preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname) || $nickname_pat) {
			$err['err_nickname'] = $lang->singup->nickname->forbidden_quote;
		}

		$scode = trim($params['code']);
		if ($scode=='') {
			$err['err_code'] = $lang->signup->scode->empty;
		} else if (Better_Registry::get('sess')->get('authCode')!=$scode) {
			$err['err_code'] = $lang->signup->scode->wrong;
		}
		
		$phone = $params['phone'] ? $params['phone'] : ($params['cell_no'] ? $params['cell_no'] : '');
		$phone = trim($phone);
		if ($phone!='') {
			$user = Better_User::getInstance($phone, 'cell');
			$userInfo = $user->getUserInfo();			
			if ($userInfo['uid']) {
				$error['err_phone'] = $lang->signup->phone_exists;
			}
		}		
		
		$agree = $params['agree'];
		if (!$agree) {
			$err['err_agree'] = $lang->signup->must_agree;
		}	
		
		
		return $err;
	}
	
	public function autothirdreg(array $params){
		$regto = $params['regto'];
		$service = Better_Service_AutoRegThird::factory($regto);		
		$reged = $service->fakeReg($params);		
		Better_Log::getInstance()->logInfo($reged."用户参数".serialize($regto),'autothirdreg');
		return 	$reged;
	}
}