<?php

/**
 * Memcache 缓存处理
 * 简化的、稍微重新封装一下memcache的api
 * 
 * @package Better.Cache.Handler
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_Cache_Handler_Memcache extends Better_Cache_Handler_Base
{
	private $_memcache = null;
	private $_class = null;

	public function __construct(array $options = array())
	{
		$this->_options['host'] = Better_Config::getAppConfig()->memcached->host;
		$this->_options['port'] = (int)Better_Config::getAppConfig()->memcached->port;
		$this->_options['compress'] = MEMCACHE_COMPRESSED;
		
		parent::__construct($options);
		
		$this->_class = Better_Config::getAppConfig()->memcached->class;
		
		$this->_memcache = new $this->_class();
		$this->_memcache->addServer($this->_options['host'], $this->_options['port']);
	}
	
	public function get($key)
	{
		$key = md5(APPLICATION_ENV.'_'.$key);
		return $this->_memcache->get($key);
	}

	public function add($key, $value, $ttl=0)
	{
		$result = false;
		if ($key) {
			$key = md5(APPLICATION_ENV.'_'.$key);
			$result = $this->_class=='Memcache' ? $this->_memcache->add($key, $value, $this->_options['compress'], intval($ttl)) : $this->_memcache->add($key, $value, intval($ttl));
		}
		return $result;
	}

	public function set($key, $value=null, $ttl=0)
	{
		$result = false;
		
		if ($value==null) {
			$result = $this->remove($key);
		} else {
			if ($key) {
				$key = md5(APPLICATION_ENV.'_'.$key);
				
				$result = $this->_class=='Memcache' ? $this->_memcache->add($key, $value, $this->_options['compress'], intval($ttl)) : $this->_memcache->add($key, $value, intval($ttl));
				if (!$result) {
					$result = $this->_class=='Memcache' ? $this->_memcache->set($key, $value, $this->_options['compress'], intval($ttl)) : $this->_memcache->set($key, $value, intval($ttl));
				}
			}
			
			return $result;
		}
	}

	public function remove($key)
	{
		$key = md5(APPLICATION_ENV.'_'.$key);
		return $this->_memcache->delete($key);
	}
	
	public function increment($key, $value=1)
	{
		$key = md5(APPLICATION_ENV.'_'.$key);
		return $this->_memcache->increment($key, $value);
	}

}