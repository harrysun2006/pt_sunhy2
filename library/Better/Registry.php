<?php

/**
 * 注册表
 * 保存一些全局变量
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Registry
{
	protected static $items = array();
	
	/**
	 * 获取一个注册变量
	 * 
	 * @param $name
	 * @return misc
	 */
	public static function get($name)
	{
		return isset(self::$items[$name]) ? self::$items[$name] : null;
	}
	
	/**
	 * 注册一个变量
	 * 
	 * @param $name
	 * @param $value
	 */
	public static function set($name, &$value)
	{
		self::$items[$name] = $value;
	}
}