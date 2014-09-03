<?php
/**
 * 生成获取联系人对象的工厂
 *
 * @package ContactsGrabber
 * @author leip <leip@peptalk.cn>
 */

class Better_Service_ContactsGrabber
{
	/**
	 * 目前支持的协议类型
	 *
	 * @var array
	 */
	public static $supportedProtocols = array(
									'163.com','126.com','yeah.net','sina.com','sohu.com','tom.com','msn.com','live.com','hotmail.com','yahoo.com','gmail.com','yahoo.com.cn','yahoo.cn','qq.com',
									)	;
									
	/**
	 * 输出具体的对象
	 *
	 * @param string $protocol
	 * @param array $params
	 * @return object
	 */
	public static function factory($protocol, $params=array())
	{
		$protocol = strtolower($protocol);

		if (in_array($protocol,self::$supportedProtocols)) {
			
			$protocolClass = 'Better_Service_ContactsGrabber_Sites_'.ucfirst(str_replace('.', '', $protocol));
			if (class_exists($protocolClass)) {
				set_time_limit(60);
				return new $protocolClass($params);
			} else {
				throw new Zend_Exception('Protocol '.$protocol.' not supported yet.');
			}
		} else {
			throw new Zend_Exception('Protocol '.$protocol.' not supported yet.');
		}
	}
}
?>