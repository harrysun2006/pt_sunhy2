<?php

/**
 * API缓存处理
 * 
 * @package Better.Api
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Api_Cache
{
	protected static $instance = array();
	private $uid = 0;
	protected static $cacher = null;
	private $_cacher = null;
	
	private function __construct($uid)
	{
		$this->uid = $uid;
		$this->_cacher = &self::$cacher;
	}
	
	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
			
			if (self::$cacher==null) {
				self::$cacher = Better_Cache::remote();
			}
		}
		
		return self::$instance[$uid];
	}
}