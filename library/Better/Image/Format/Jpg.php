<?php

/**
 * Jpg图片
 * 
 * @package Better.Image.Format
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Format_Jpg extends Better_Image_Format_Base
{
	
	public function __construct($file)
	{
		$this->header = 0xffd8;
		$this->ext = 'jpg';
		
		parent::__construct($file);	
	}
	
}