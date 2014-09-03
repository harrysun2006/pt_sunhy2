<?php

/**
 * PPNS 认证
 * 
 * @package Better.Ppns
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Ppns_Auth
{
	protected static $codes = array(
		'terminate_failed' => -7,
		'session_not_found' => -6,
		'force_validate' => -5,
		'user_banned' => -4,
		'user_need_validate' => -3,
		'invalid_password' => -2,
		'input_invalid' => -1,
		'unknown' => 0,
		'success' => 1,
		);
	
	/**
	 * 处理Ppns认证请求
	 * 
	 * @return integer
	 */
	public static function auth($xml, $ppnsSid='')
	{
		$code = self::$codes['unknown'];
		
		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXml($xml);
			
			//	尝试查找session id节点
			$items = $dom->query('session');
			if (count($items)>0) {
				foreach ($items as $item) {
					$sessId = $item->nodeValue;
				}
				$code = self::authBySid($sessId);
			} else {
				$idKey = '';
				
				//	尝试查找id节点
				$items = $dom->query('id');
				if (count($items)>0) {
					foreach ($items as $item) {
						$idKey = $item->nodeValue;
					}
					
					//	尝试查找basic节点
					$items = $dom->query('basic');
					if (count($items)>0) {
						foreach ($items as $item) {
							$basic = $item->nodeValue;
						}
						
						list($username, $password) = explode(':', base64_decode($basic));
						$code = self::authByPwd($idKey, $username, $password, $ppnsSid);
					} else {
						
						//	尝试查找用户名/密码节点
						$items = $dom->query('user');
						if (count($items)>0) {
							foreach ($items as $item) {
								$username = $item->nodeValue;
							}
							$items = $dom->query('pass');
							if (count($items)>0) {
								foreach ($items as $item) {
									$password = $item->nodeValue;
								}
								
								$code = self::authByPwd($idKey, $username, $password, $ppnsSid);
							} else {
								$code = self::$codes['input_invalid'];
							}
						} else {
							$code = self::$codes['input_invalid'];
						}
					}
				}
			}
				
		} catch (Exception $e) {
			$code = self::$codes['input_invalid'];
		}
		
		return $code;
	}
	
	/**
	 * 使用用户名/密码验证用户
	 * 
	 * @return integer
	 */
	protected static function authByPwd($idKey, $username, $password, $ppnsSessionId='')
	{
		$username = strtolower($username);
		if ($username=='pt') {
			$loginResult = Better_User_Login::tokenLogin($username, $password, false, false);
		} else {
			$loginResult = Better_User_Login::login($username, $password, false, 0);
		}		
		
		switch ($loginResult) {
			case Better_User_Login::INVALID_PWD:
				$code = self::$codes['invalid_password'];
				break;
			case Better_User_Login::NEED_VALIDATED:
				$code = self::$codes['user_need_validate'];
				break;
			case Better_User_Login::ACCOUNT_BANNED:
				$code = self::$codes['user_banned'];
				break;
			case Better_User_Login::FORCE_VALIDATING:
				$code = self::$codes['force_validate'];
				break;
			case Better_User_Login::LOGINED:
				
				$uid = Better_Registry::get('sess')->get('uid');
				$user = Better_User::getInstance($uid);
				$userInfo = $user->getUserInfo();
				
				$ppnsSessionInfo = Better_Ppns::getInstance()->getUserPpnsSession($uid);
				$sid = trim($ppnsSessionInfo['ppns_sid']);
				$sessIdKey = (int)$ppnsSessionInfo['seq'];
				
				//	有旧的ppns会话，那么从关志达处请求结束旧的连接先
				if ($idKey>$sessIdKey && $ppnsSessionId && $sid && $sid!=$ppnsSessionId) {
					$result = Better_Ppns::getInstance()->terminate($sid);
				}
				
				if ($ppnsSessionId) {
					$params = array(
						'uid' => $userInfo['uid'],
						'ppns_sid' => $ppnsSessionId,
						'start_time' => time()
						);
					
					//	写入新的用户/ppns会话对应关系
					Better_DAO_Ppns_Session::getInstance()->replace($params);
					$code = self::$codes['success'];
					Better_Ppns::getInstance()->pushOfflineMsg($userInfo['uid']);
				}
				break;		
			default:
				$code = self::$codes['unknown'];
				break;		
		}
		Better_Log::getInstance()->logInfo($username.'|'.$password.'|'.$code, 'ppns_debug', true);
		return $code;
	}

	/**
	 * 使用会话id认证
	 * 
	 * @TODO:具体逻辑待定
	 * 
	 */
	protected static function authBySid($sid)
	{
		$row = Better_DAO_Ppns_Session::getInstance()->get($sid);
		if (isset($row['session_id']) && $row['session_id']) {
			$code = self::$codes['success'];
		} else {
			$code = self::$codes['session_not_found'];
		}
		
		return $code;
	}
	
	/**
	 * 用户PPNS注销
	 * 
	 * @return bool
	 */
	public static function logout($sid)
	{
		$flag = false;
		
		if ($sid) {
			Better_DAO_Ppns_Session::getInstance()->deleteByCond(array(
				'ppns_sid' => $sid
				));
			$flag = true;
		}
		
		return $flag;
	}
}
