<?php

/**
 * 调试用的定时器
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Timer
{
	protected static $timers = array();
	
	/**
	 * 启动一个定时器
	 * 
	 * @param $key
	 * @return null
	 */
	public static function start($key)
	{
		$mtime = explode(' ', microtime());
		self::$timers[$key] = $mtime[1]+$mtime[0];	
	}
	
	/**
	 * 终止一个定时器，并返回时间差
	 * 
	 * @param $key
	 * @return float
	 */
	public static function end($key, $keep=false)
	{
		$mtime = explode(' ', microtime());
		$end = $mtime[1]+$mtime[0];
		$start = self::$timers[$key];
		
		if (!$keep) {
			unset(self::$timers[$key]);
		}
		
		return round(($end-$start), 5);
	}
	
}