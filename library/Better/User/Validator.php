<?php

/**
 * 用户资料验证
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_Validator
{
	public static function cell($cell)
	{
		$result = false;
		
		if (strlen($cell)==13) {
			$pat = '/^86(130|131|132|133|134|135|136|137|138|139|150|151|152|153|154|155|156|157|158|159|183|184|185|186|187|188|189)([0-9]{8})/';
			$result = preg_match($pat, $cell);
		}
		
		return $result;
	}
	
	public static function intro($intro) 
	{
		$flag = true;
		
		if (Better_Config::getAppConfig()->filter->enable && Better_Filter::getInstance()->filterBanwords($intro)) {
			$flag = false;
		}
		
		return $flag;
	}
	
	public static function birthday($birthday)
	{
		return preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/i', $birthday);	
	}
	
	public static function username($username, $uid=0)
	{
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		$codes = array(
			'USERNAME_TOO_LONG' => -1,
			'USERNAME_FORBIDEN_WORD' => -2,
			'USERNAME_EXISTS' => -3,
			'USERNAME_TOO_SHORT' => -4,
			'USERNAME_REQUIRED' => -5,
			'SUCCESS' => 1		
			);
		$code = $codes['USERNAME_REQUIRED'];
		$msg = '';
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";	
				
		if (strlen($username)) {
			$reservedname = explode("|",Better_Config::getAppConfig()->routes->exclude_controllers);
			for($i=0;$i<count($reservedname);$i++){
				$finduser = strpos($username,$reservedname[$i]);
				if($finduser !== false && $finduser==0){
					$code = $codes['USERNAME_EXISTS'];
					//error.account.username_exists
					$msg = $lang->api->error->account->username_exists;
					break;				
				}
			}
			if (mb_strlen($username)>20) {
				$code = $codes['USERNAME_TOO_LONG'];
				//error.account.username_too_long
				$msg = $lang->api->error->account->username_too_long;
			} else if (mb_strlen($username)<5) {
				$code = $codes['USERNAME_TOO_SHORT'];
				$msg = $lang->api->error->account->username_too_short;
			} else if (preg_match($patAT, $username)) {
				$code = $codes['USERNAME_FORBIDEN_WORD'];
				//error.account.username_forbiden_word
				$msg = $lang->api->error->account->username_forbiden_word_short;
			} else if (preg_match($patInvalid, $username)) {
				$code = $codes['USERNAME_FORBIDEN_WORD'];
				$msg = $lang->api->error->account->username_forbiden_word_short;
			} else if(preg_match($patChinese, $username)){
				$code = $codes['USERNAME_FORBIDEN_WORD'];
				$msg = $lang->api->error->account->username_forbiden_word_short;
			} else if(!preg_match($patNoSpecialChar, $username)){
				$code = $codes['USERNAME_FORBIDEN_WORD'];
				$msg = $lang->api->error->account->username_forbiden_word_short;
			} else if (preg_match($patControllers, $username)) {
				$code = $codes['USERNAME_EXISTS'];
				$msg = $lang->api->error->account->username_exists;
			} else if (Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->username($username, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($username) || Better_Filter::getInstance()->filterNameBanwords($username)) {
				$code = $codes['USERNAME_EXISTS'];
				$msg = $lang->api->error->account->username_exists;
			} else if ((strtolower($username)!='kai'.$uid && preg_match($patKai, $username)) || preg_match($patUrl, $username)) {
				$code = $codes['USERNAME_EXISTS'];
				$msg = $lang->api->error->account->username_exists;
			} else {
				$code = $codes['SUCCESS'];
			}
		} else {
			$code = $codes['USERNAME_REQUIRED'];
			$msg = $lang->api->error->account->username_required;
		}		
		
		return array(
			'codes' => &$codes,
			'code' => $code,
			'msg' => $msg
			);
	}
	
	public static function nickname($nickname, $uid=0)
	{
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		$codes = array(
			'NICKNAME_TOO_LONG' => -1,
			'NICKNAME_TOO_SHORT' => -2,
			'NICKNAME_FORBIDEN_WORD' => -3,
			'NICKNAME_EXISTS' => -4,
			'NICKNAME_REQUIRED' => -5,
			'SUCCESS' => 1
			);
		$msg = '';
		$code = $codes['NICKNAME_REQUIRED'];
		
		$patAT = '/@/is';
		$patInvalid = '/([\s\r\t ])/is';
		$patChinese = '/[^\x00-\xff]/is';
		$patNoSpecialChar = '/^([0-9a-zA-Z]+)$/is';// '/^[a-z0-9.]*$/is';
		$patControllers = '/^('.Better_Config::getAppConfig()->routes->exclude_controllers.')$/i';
		$patKai = '/^kai([0-9].+)$/i';
		$patUrl = '/(\/|\.|@|\?|\&)/';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";		

		if (strlen($nickname)) {
			if (mb_strlen($nickname)>20) {
				$code = $codes['NICKNAME_TOO_LONG'];
				$msg = $lang->api->error->account->nickname_too_long;
			} else if (strlen($nickname)<5) {
				$code = $codes['NICKNAME_TOO_SHORT'];
				$msg = $lang->api->error->account->nickname_too_short;
			}  else if (preg_match($patAT, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname)) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
				$msg = $lang->api->error->account->nickname_forbiden_word;
			} else if (Better_User_Exists::getInstance(Better_Registry::get('sess')->getUid())->nickname($nickname, Better_User_Exists::PROFILE) || Better_Filter::getInstance()->filterBanwords($nickname) || Better_Filter::getInstance()->filterNameBanwords($nickname)) {
				$code = $codes['NICKNAME_EXISTS'];
				$msg = $lang->api->error->account->nickname_exists;
			} else if (preg_match($patInvalid, $nickname)) {
				$code = $codes['NICKNAME_FORBIDEN_WORD'];
				$msg = $lang->api->error->account->nickname_forbiden_word;
			} else {
				$code = $codes['SUCCESS'];
				$msg = '';
			}
		} else {
			$code = $codes['NICKNAME_REQUIRED'];
			$msg = $lang->api->error->account->nickname_required;
		}		
		
		return array(
			'msg' => $msg,
			'code' => $code,
			'codes' => &$codes
			);
	}
	
	public static function email($email, $uid=0)
	{
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		
		$codes = array(
			'SUCCESS' => 1,
			'EMAIL_INVALID' => -1,
			'EMAIL_TOO_LONG' => -2,
			'EMAIL_EXISTS' => -3,
			);
		$code = $codes['EMAIL_INVALID'];
		$msg = $lang->api->error->account->email_invalid;
		
		if ($email!='' && trim($email)!='') {
			if (strlen($email)>=50) {
				$code = $codes['EMAIL_TOO_LONG'];
				$msg = $lang->api->error->account->email_too_long;
			} else {
				if (Better_Functions::checkEmail($email)) {
					if (Better_User_Exists::getInstance($uid)->email($email)) {
						$code = $codes['EMAIL_EXISTS'];
						$msg = $lang->api->error->account->email_exists;
					} else {
						$code = $codes['SUCCESS'];
						$msg = '';
					}
				}
			}
		}
		
		return array(
			'code' => $code,
			'codes' => &$codes,
			'msg' => $msg
			);
	}
}