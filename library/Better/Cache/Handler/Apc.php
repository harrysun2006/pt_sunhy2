<?php
/**
 * Apc 缓存处理
 * 
 * @package Better.Cache.Handler
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_Cache_Handler_Apc extends Better_Cache_Handler_Base
{
	public function __construct(array $options = array())
	{
		parent::__construct($options);
	}
	
	public function get($key)
	{
		return apc_fetch($key);
	}
	
	public function set($key, $value=null, $ttl=0)
	{
		if ($value==null) {
			return $this->remove($key);
		} else {
			$ttl = intval($ttl);
			
			return apc_store($key, $value, $ttl);
		}
	}

	public function remove($key)
	{
		return apc_delete($key);
	}
}