<?php

/**
 * 系统启动时需加载的缓存
 * 
 * @package Better.Cache
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache_BootStrap
{
	protected static $instance = null;
	protected $cacher = null;
	
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
	
	public function startup()
	{
		//	勋章缓存
		Better_Cache_Module_Badge::load();
		
		//	POI分类缓存
		Better_Cache_Module_Poi_Category::load();
	}
}