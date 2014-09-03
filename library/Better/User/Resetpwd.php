<?php

/**
 * 重置密码
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Resetpwd extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	/**
	 * 检测重置密码的请求，如果通过验证，则为用户创建一个登录session，并重定向到重设密码页
	 * 
	 * @param $uid
	 * @param $hash
	 * @return unknown_type
	 */
	public static function hasRequest($uid, $hash)
	{
		$row = Better_DAO_Resetpwd::getInstance()->get(array(
						'uid' => $uid,
						'hash' => $hash,
						));
		$ruid = 0;

		if (isset($row['uid']) && $row['uid']==$uid) {
			$user = Better_User::getInstance($row['uid']);
			$info = $user->getUser();
			if (isset($info['uid'])) {
				$ruid = $info['uid'];

				Better_DAO_Resetpwd::getInstance()->deleteUid($ruid);
			}
		}

		return $ruid;		
	}
	
	/**
	 * 生成重置密码请求
	 * 
	 * @param $id
	 * @param $method
	 * @return array
	 */
	public static function request($id, $method='email')
	{
		$err = '';
		$has_err = 0;

		if ($method=='email') {
			$email = $id;

			if ($email=='') {
				$err = Better_Language::get()->resetpwd->email_required;
			} else {
				if (Better_Functions::checkEmail($email)) {
					!Better_User_Exists::getInstance()->email($email) && $err = Better_Language::get()->resetpwd->email_invalid;
				} else {
					$err = Better_Language::get()->resetpwd->email_incorrect;
				}
			}

			if ($err=='') {
				$uid = Better_User::search('email', $email);
				if ($uid) {
					$userInfo = Better_User::getInstance($uid)->getUser();

					if ($userInfo['state']==Better_User_State::SIGNUP_VALIDATING || $userInfo['state']==Better_User_State::UPDATE_VALIDATING || $userInfo['enabled']=='0') {
						$err = Better_Language::get()->resetpwd->email_validating;	
					}else if($userInfo['state']==Better_User_State::BANNED){
						$err = Better_Language::get()->error->login->account_banned;	
					} else {
						$err = Better_Language::get()->resetpwd->email_sent;
						Better_Email_Resetpwd::send($userInfo);
					}
				} else {
					$err = 'System Error';
					$has_err = 1;
				}
			} else {
				$has_err = 1;
			}

		} elseif ($method=='cell') {
		}

		return array(
						'err' => $err,
						'has_err' => $has_err,
						)	;		
	}
	
}