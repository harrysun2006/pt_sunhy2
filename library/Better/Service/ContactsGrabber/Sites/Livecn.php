<?php

/**
 * 获取live.com邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Livecn extends Better_Service_ContactsGrabber_SItes_Msn
{

	/**
	 * 
	 */
	function __construct($params=array())
	{
		parent::__construct($params);
		$this->protocol = 'live.cn';
	}

}