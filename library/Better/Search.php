<?php

/**
 * Better搜索应用的工厂
 *
 * @package Better
 * @author pysche
 *
 */
class Better_Search
{
	
	/**
	 * 工厂方法
	 *
	 * @param string $what 要搜索什么：blog,user
	 * @param string $keyword 搜索的关键字（对于qbs搜索来说，需要将坐标及范围拼出这个关键字，见Better_Search_User_Qbs）
	 * @param string $func 要执行的搜索方法（默认的方法名为search）
	 * @param string $method 搜索方式（MySQL, Sphinx, Qbs）
	 * @return Better_Search_Base
	 */
	public static function factory(array $params)
	{
		$what = isset($params['what']) ? $params['what'] : 'blog';
		$method = isset($params['method']) ? $params['method'] : Better_Config::getAppConfig()->search->method;
		$method = $method ? $method : 'mysql';

		$class = 'Better_Search_'.ucfirst($what).'_'.ucfirst($method);
		return new $class($params);
	}
}