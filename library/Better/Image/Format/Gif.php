<?php

/**
 * Gif图片
 * 
 * @package Better.Image.Format
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Format_Gif extends Better_Image_Format_Base
{
	
	public function __construct($file)
	{
		$this->header = array(
			0x474946383761,
			0x474946383961
			);
		$this->headerLength = 12;
		$this->ext = 'gif';
		
		parent::__construct($file);	
	}
	
}