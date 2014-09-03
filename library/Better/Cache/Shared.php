<?php

/**
 * 
 * 共享Cache
 * 
 * @package Better.Cache
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Cache_Shared
{
	protected static $instance = null;

	private function __construct()
	{
		
	}
	
	public static function getInstance()
	{
		if (!self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}