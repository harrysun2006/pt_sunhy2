<?php

/**
 * 缓存处理基类
 * 
 * @package Better.Cache.Handler
 * @author leip <leip@peptalk.cn>
 *
 */

abstract class Better_Cache_Handler_Base
{
	protected $_options = array();
	
	public function __construct(array $options=array())
	{
		foreach ($options as $key=>$option) {
			$this->_options[$key] = $option;
		}
	}
	
	abstract public function set($key, $value=null, $ttl=0);
	abstract public function get($key);
	abstract public function remove($key);
	
	public function test($key)
	{
		return $this->get($key);
	}
	
	public function load($key)
	{
		return $this->get($key);
	}
	
	public function save($value, $key)
	{
		return $this->set($key, $value);
	}

}