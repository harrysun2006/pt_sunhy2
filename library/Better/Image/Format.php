<?php

/**
 * 图片格式
 * 
 * @package Better.Image
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Format
{
	protected static $instance = array();
	
	public static function getInstance($file)
	{
		if (!isset(self::$instance[$file])) {
			$pathinfo = pathinfo($file);
			$ext = $pathinfo['extension'];
			$class = 'Better_Image_Format_'.ucfirst(strtolower($ext));
			self::$instance[$file] = new $class($file);
		}		
		
		return self::$instance[$file];
	}
}