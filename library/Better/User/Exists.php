<?php

/**
 * 用户存在性检查
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Exists extends Better_User_Base
{
	protected static $instance = array();
	const PROFILE = 'p.';
	const ACCOUNT = 'a.';

	public static function getInstance($uid=0)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 
	 * @param $method
	 * @param $params
	 * @return unknown_type
	 */
	public static function getNewNickname($nickname)
	{
		$new_nickname = array();
		for($i=1; $i<20; $i++) {
			$_nickmames = $nickname . '_' . rand(1000, 9999);
			if ( !self::getInstance(Better_Registry::get('sess')->getUid())->nickname($_nickmames, Better_User_Exists::PROFILE) ) {
				$new_nickname[] = $_nickmames;
				if (count($new_nickname) == 2) break;
			}
		}
		
		return join(',', $new_nickname);
	}
	
	
	public function __call($method, $params)
	{
		return $this->_key(strtolower($method), $params[0], $params[1]);
	}	

	/**
	 * 检查某个指定的键值是否存在
	 * 比如用户名，email等
	 *
	 * @param $key
	 * @param $val
	 * @return bool
	 */	
	private function _key($key, $val, $tbl='')
	{
		switch($tbl) {
			case self::PROFILE:
				$prefix = self::PROFILE;
				break;
			default:
				$prefix = self::ACCOUNT;
				break;
		}

		$data = Better_DAO_User::getInstance()->getByKey($val, $prefix.$key, false);

		return $data['uid']>0 ? ($data['uid']!=$this->uid ? true : false) : false;		
	}
	
}