<?php

/**
 * 举报用户
 * 
 * @package Better.Denounce
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Denounce_User extends Better_Denounce_Base
{
	protected static $instance = null;
	protected static $reasons = array(
		'porn' => '色情',
		'reaction' => '反动',
		'cheat' => '欺骗',
		'other' => '其他',
		);
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}	
	
	/**
	 * 举报用户
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function denounce(array $params)
	{
		$content = $params['content'];
		$reason = $params['reason'];
		$uid = $params['uid'];
		$denounceUid = $params['denounce_uid'];
		
		$denounceUser = Better_User::getInstance($denounceUid);
		$denounceUserInfo = $denounceUser->getUserInfo();
		
		return Better_DAO_Denounce::getInstance()->insert(array(
			'denounce_uid' => $denounceUid,
			'denounce_nickname' => $denounceUserInfo['nickname'],
			'denounce_content' => $content,
			'denounce_reason' => self::_getReason($reason),
			'uid' => $uid,
			'dateline' => time(),
			'status'=> 'no_progress',
			));
	}
	
	/**
	 * 24小时内是否举报过用户
	 * 
	 * @return bool
	 */
	public function denounced($uid, $denounceUid, $reason='others')
	{
		return Better_DAO_Denounce::getInstance()->denounced($uid, $denounceUid, self::_getReason($reason));
	}
	
	/**
	 * 获取举报理由
	 * 
	 * @param $reason
	 * @return string
	 */
	private static function _getReason($reason)
	{
		if (array_key_exists($reason, self::$reasons)) {
			$result = self::$reasons[$reason];
		} else if (in_array($reason, self::$reasons)) {
			$result = $reason;
		} else {
			$result = self::$reasons['other'];
		}
		
		return $result;
	}
}