<?php

/**
 * 图片格式基类
 * 
 * @package Better.Image.Format
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Image_Format_Base
{
	protected $handler = null;
	
	public $ext = '';
	public $file = null;
	public $info = array();
	
	protected $header = '';
	protected $headerLength = 2;
	
	public function __construct($file=null)
	{
		$this->file = $file;
		$this->handler = Better_Image_Handler::factory($this);

		if (is_file($this->file)) {
			$info = getimagesize($this->file);
			$this->info['w'] = $info[0];
			$this->info['h'] = $info[1];
			$this->info['m'] = $info[2];
		}
	}
	
	public function __call($method, $params)
	{
		if (method_exists($this->handler, $method)) {
			return $this->handler->{$method}($params);
		}
	}

}