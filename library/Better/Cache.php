<?php

/**
 * 获得缓存对象
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache
{
	protected static $local = null;
	protected static $remote = null;

	public static function local()
	{
		if (self::$local==null) {
			if (defined('BETTER_IN_CONSOLE')) {
				self::$local = new Better_Cache_Handler_Console();
			} else {
				if (function_exists('xcache_info')) {
					include_once 'Better/Cache/Handler/Xcache.php' ;
					self::$local = new Better_Cache_Handler_Xcache();
				} else if (function_exists('apc_cache_info')) {
					$flag = include_once 'Better/Cache/Handler/Apc.php' ;
					
					self::$local = new Better_Cache_Handler_Apc();
				}
			}
		} 
		
		return self::$local;
	}
	
	public static function remote()
	{
		if (self::$remote==null) {
			self::$remote = new Better_Cache_Handler_Memcache();
		}
		
		return self::$remote;
	}
	
}