<?php
/**
 * Console 缓存处理
 * 
 * @package Better.Cache.Handler
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_Cache_Handler_Console extends Better_Cache_Handler_Base
{
	protected $handler = null;
	
	public function __construct(array $options = array())
	{
		parent::__construct($options);
		
		$this->handler = Zend_Cache::factory('Core', 'File');
	}
	
	public function get($key)
	{
		return $this->handler->load($key);
	}
	
	public function set($key, $value=null, $ttl=0)
	{
		if ($value==null) {
			return $this->handler->remove($key);
		} else {
			$ttl = intval($ttl);
			
			return $this->handler->save($value, $key, array(), $ttl);
		}
	}

	public function remove($key)
	{
		return $this->handler->remove($key);
	}
}