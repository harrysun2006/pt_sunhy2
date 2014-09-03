<?php

/**
 * 配置文件加载
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Config
{
	protected static $fullConfig = null;
	protected static $appConfig = null;
	protected static $dbConfig = null;
	protected static $attachConfig = null;
	
	private function __construct()
	{
	}

	public static function load()
	{
		//	加载缓存对象
		$cacher = Better_Cache::local();		
		$cacheKey = 'config_'.md5(APPLICATION_ENV);

		self::$fullConfig = $cacher->load($cacheKey);
		if (!self::$fullConfig) {
			//	加载配置文件
			self::$fullConfig = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);	
			$cacher->set($cacheKey, self::$fullConfig);
		} 
		self::$appConfig = &self::$fullConfig->kai;

		//	加载数据库配置
		self::$dbConfig = &self::$appConfig->database;
		
		//	加载附件配置
		self::$attachConfig = &self::$appConfig->attachment;
	}
	
	/**
	 * 获取应用配置
	 * 
	 * @return array
	 */
	public static function &getAppConfig()
	{
		return self::$appConfig;
	}
	
	/**
	 * 获取完整的配置
	 * 
	 * @return array
	 */
	public static function &getFullConfig()
	{
		return self::$fullConfig;
	}
	
	/**
	 * 获取数据库配置
	 * 
	 * @return array
	 */
	public static function &getDbConfig()
	{
		return self::$dbConfig;
	}
	
	/**
	 * 获取附件配置
	 * 
	 * @return array
	 */
	public static function &getAttachConfig()
	{
		return self::$attachConfig;
	}
}