<?php

/**
 * DAO工厂
 * 根据业务逻辑划分的模块分别编写的DAO层
 * 
 * ！！！！！！！！	该文件经过若干次代码调整，已经不适用了，现在初始化一个DAO的类都直接用类的名称，然后getInstance来获取实例
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 * @TODO 删除该文件
 *
 */
class Better_DAO 
{
	
	public static $supportedModules = array('user', 'blog', 'following', 'follower', 'attachment');

	public static function factory($name, $key=null)
	{


		$obj = null;
		$lowerName = strtolower($name);
		$regName = 'DAO';
		if (!Better_Registry::isRegistered($regName)) {
			Better_Registry::set($regName, array());
		}
		
		$daoArr = Better_Registry::get($regName);
		
		if ( (!isset($daoArr[$name]) || !is_object($daoArr[$name])) && in_array($lowerName, self::$supportedModules)) {
			$file = ucfirst($lowerName);
			$className = 'Better_DAO_'.$file;
			include_once(dirname(__FILE__).'/DAO/'.$file.'.php');
			
			$key==null ? $obj = new $className() : $obj = new $className($key);

		} else {
			$obj = &$daoArr[$name];
		}
		
		return $obj;
	}
	

}

?>