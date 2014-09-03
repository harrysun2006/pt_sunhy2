<?php

/**
 * 举报吼吼
 * 
 * @package Better.Denounce
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Denounce_Blog extends Better_Denounce_Base
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
	 * 24小时内是否举报过
	 * 
	 * @return bool
	 */
	public function denounced($uid, $bid, $reason='others')
	{
		return Better_DAO_Denounce_Blog::getInstance()->denounced($uid, $bid, self::_getReason($reason));
	}	
	
	/**
	 * 举报吼吼
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function denounce(array $params)
	{
		$content = $params['content'];
		$reason = $params['reason'];
		$bid = $params['bid'];
		$uid = $params['uid'];
		
		$data = Better_Blog::getBlog($bid);
		$denounceUid = $data['user']['uid'];
		$blog_type = $data['blog']['type'];
		$attach = $data['blog']['attach'];
		
		$denounceUser = Better_User::getInstance($denounceUid);
		$denounceUserInfo = $denounceUser->getUserInfo();
		
		return Better_DAO_Denounce_Blog::getInstance()->insert(array(
			'denounce_bid' => $bid,
			'denounce_uid' => $denounceUid,
			'denounce_nickname' => $denounceUserInfo['nickname'],
			'denounce_content' => $content,
			'denounce_reason' => self::_getReason($reason),
			'uid' => $uid,
			'dateline' => time(),
			'status'=> 'no_progress',
			'blog_type'=> $blog_type,
			'attach'=> $attach
			));
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
			$result = self::$reasons['others'];
		}
		
		return $result;
	}	
}