<?php

/**
 * 图片处理程序
 * 
 * @package Better.Image
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Handler
{
	
	public static function factory($file, $forceHandler='')
	{
		$handlerName = $forceHandler ? $forceHandler : Better_Config::getAppConfig()->image->handler;
		$class = 'Better_Image_Handler_'.ucfirst(strtolower($handlerName));
		
		try {
			$handler = new $class($file);
		} catch (Exception $e) {
			Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'image');
			$handler = new Better_Image_Handler_Gd($file);
		}
		
		return $handler;
	}
}