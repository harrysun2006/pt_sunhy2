<?php

/**
 * Png图片
 * 
 * @package Better.Image.Format
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Image_Format_Png extends Better_Image_Format_Base
{
	
	public function __construct($file)
	{
		$this->header = 0x89504E470D0A1A0A;
		$this->headerLength = 16;
		$this->ext = 'png';
		
		parent::__construct($file);	
	}
	
}