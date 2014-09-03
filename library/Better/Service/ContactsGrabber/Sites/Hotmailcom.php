<?php

/**
 * 获取hotmail.com邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Hotmailcom extends Better_Service_ContactsGrabber_Sites_Msn
{

	/**
	 * 
	 */
	function __construct($params=array())
	{
		parent::__construct($params);
		$this->protocol = 'hotmail.com';
	}

}

?>