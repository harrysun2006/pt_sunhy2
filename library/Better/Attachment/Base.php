<?php

/**
 * 附件处理基类
 * 
 * @package Better.Attachment
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Attachment_Base
{
	protected static $config = array();

	protected function __construct()
	{
		if (count(self::$config)==0) {
			self::$config = Better_Config::getAttachConfig();
		}	
	}
	
	/**
	 * DJB算法php实现
	 *
	 * @param string $str
	 * @return string
	 */
	protected static function DJBHash($str)
	{
		$hash = 0;
		
		for ($i=0;$i<strlen($str);$i++) {
			$hash += ($hash<<5)+ord($str[$i]);
		}
		
		return abs(abs($hash)%701819);
	}
	
	/**
	 * 根据一个fid/seq取hash目录
	 * 
	 * @return string
	 */
	public static function hashDir($fid, $basePath='', $create=false)
	{
		$level = 3;
		$path = $basePath;

		$hash = sprintf('%0'.($level*2).'d', self::DJBHash($fid));
		
		for ($i=0; $i<$level*2; $i+=2) {
			$path .= '/'.substr($hash, $i, 2);
		}
		if($create===true){
			try{
				!file_exists($path) && mkdir($path, 0777, true);				
			} catch(Exception $e){
				
			}
		}		
		return $path;
	}

}