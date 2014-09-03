<?php

/**
 * 获取live.cn邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Msncom extends Better_Service_ContactsGrabber_Sites_Msn
{

	/**
	 * 
	 */
	function __construct($params = array())
	{
		parent::__construct($params = array());
		$this->protocol = 'msn.com';
	}
}