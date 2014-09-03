<?php

/**
 * 获取yahoo.com.cn邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Yahoocomcn extends Better_Service_ContactsGrabber_Sites_Yahoo
{

	/**
	 * 
	 */
	function __construct($params=array())
	{
		parent::__construct($params);
		$this->protocol = 'yahoo.com.cn';
	}
	
	/**
	 * 
	 */
	function __destruct()
	{
		parent::__destruct();
	}	
}

?>