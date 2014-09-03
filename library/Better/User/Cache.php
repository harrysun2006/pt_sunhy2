<?php

/**
 * 用户缓存策略
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Cache extends Better_User_Base
{
	protected static $instance = array();
	protected static $cacher = null;
	protected $key = '';
	protected $data = array();
	
	protected function __construct($uid)
	{
		parent::__construct($uid);
		$this->key = md5(APPLICATION_ENV.'kai_user_cache_'.$uid);
		$this->data = self::$cacher->get($this->key);
	}
	
	function __destruct()
	{
		self::$cacher->set($this->key, $this->data);
	}
	
	public static function getInstance($uid)
	{				
		if (self::$cacher==null) {
			self::$cacher = Better_Cache::remote();
		}

		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}

		return self::$instance[$uid];
	}
	
	public function get($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}
	
	public function set($key, $value=null)
	{
		if ($value===null) {
			unset($this->data[$key]);
		} else {
			$this->data[$key] = $value;
		}
		
		return true;
	}
	
	/**
	 * 更新数据库缓存
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function updateDbCache($key, $value)
	{
		Better_DAO_User_Cache::getInstance($this->uid)->updateByCond(array(
			$key => serialize($value)
		), array(
			'uid' => $this->uid
			));
	}
}