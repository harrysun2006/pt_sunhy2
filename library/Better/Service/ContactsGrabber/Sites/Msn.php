<?php

/**
 * 处理使用MSN协议的几个邮箱的通用类
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_Msn extends Better_Service_ContactsGrabber_Abstract
{
	/**
	 * PHPMSNClass实例
	 *
	 * @var object
	 */
	private $msn = null;

	/**
	 *
	 */
	function __construct($params=array())
	{
		$this->protocol = 'msn';
		$this->init($params);
		$this->msn = new Better_Msn('MSNP15');
	}

	/**
	 *
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
		if ($this->logined) {
			$data = $this->msn->getMembershipList();

			if (is_array($data) && count($data)>0) {
				foreach($data as $domain=>$rows) {
					foreach($rows as $name=>$ignored) {
						$this->contacts[] = array(
							'name' => $name,
							'email' => $name.'@'.$domain,
							);
					}
				}
			}
			
			return $this->outputResult();
		} else {
			$this->Error('Please login to '.$this->protocol.' first');
		}
	}

	/**
	 *
	 * @see abstractContactsGrabber::login()
	 */
	public function login()
	{
		$this->logined = $this->msn->connect($this->params['username'].'@'.$this->protocol, $this->params['password']);

		return $this->logined ;
	}

	/**
	 *
	 */
	function __destruct()
	{
		unset($this->msn);
	}
}

?>