<?php

/**
 * Karma值计算
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Karma
{
	const BASE = 1000;
	
	/**
	 * 格式化Karma数字
	 * 
	 * @param unknown_type $karma
	 */
	public static function format($karma)
	{
		return sprintf("%.2f", (float)$karma);
	}
}