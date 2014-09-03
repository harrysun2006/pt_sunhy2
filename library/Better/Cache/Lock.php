<?php

/**
 * 
 * Memcached缓存锁管理
 * 
 * @package Better.Cache
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache_Lock
{
	protected static $instance = null;
	protected $sleepStep = 100000;
	protected $cacher = null;
	protected $cacheKey = 'kai_cache_locks';
	
	private function __construct()
	{
		$this->cacher = Better_Cache::remote();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function lock($key)
	{
		if (strlen($key)) {
			$locks = $this->cacher->get($this->cacheKey);
			$locks[$key] = '1';
			$this->cacher->set($this->cacheKey, $locks);
		}
	}
	
	public function release($key)
	{
		if (strlen($key)) {
			$locks = $this->cacher->get($this->cacheKey);
			$locks[$key] = '0';
			$this->cacher->set($this->cacheKey, $locks);
		}
	}
	
	public function wait($key)
	{
		if (strlen($key)) {
			$locks = $this->cacher->get($this->cacheKey);
			while($locks[$key]=='1') {
				usleep($this->sleepStep);
				$locks = $this->cacher->get($this->cacheKey);
			}
		}
	}
}