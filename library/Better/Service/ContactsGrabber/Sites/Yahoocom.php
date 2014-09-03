<?php

/**
 * 获取yahoo.com邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Yahoocom extends Better_Service_ContactsGrabber_Sites_Yahoo
{

	/**
	 * 
	 */
	function __construct($params = array())
	{
		parent::__construct($params);
		$this->protocol = 'yahoo.com';
	
	}

	/**
	 * 
	 */
	function __destruct()
	{
		parent::__destruct();
	}
}