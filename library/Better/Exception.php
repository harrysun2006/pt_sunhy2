<?php

/**
 * Better 异常处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better
 *
 */

class Better_Exception extends Exception
{

	function __construct($message='', $code=0)
	{
		parent::__construct($message, $code);
		Better_Log::getInstance()->logInfo('Exception: ['.$message.'], Code:['.$code.']', 'exception');
	}

}