<?php
/**
 * XCache 缓存处理
 * 
 * @package Better.Cache.Handler
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_Cache_Handler_Xcache extends Better_Cache_Handler_Base
{
	public function __construct(array $options = array())
	{
		parent::__construct($options);
	}
	
	public function get($key)
	{
		$value = null;
		
		if (xcache_isset($key)) {
			$value = xcache_get($key);
		}
		
		return $value;
	}
	
	public function set($key, $value=null, $ttl=0)
	{
		if ($value==null) {
			return $this->remove($key);
		} else {
			$ttl = intval($ttl);
			
			if ($ttl>0) {
				$flag = xcache_set($key, $value, $ttl);
			} else {
				$flag = xcache_set($key, $value);
			}
			
			return $flag;
		}
	}
	
	public function test($key)
	{
		return xcache_isset($key);	
	}
	
	public function remove($key)
	{
		return xcache_unset($key);
	}
}