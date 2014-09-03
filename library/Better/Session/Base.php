<?php

/**
 * Session基类
 *
 * @package Better.Session
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Session_Base 
{

	public $user = array();
	public $uid = 0;
	
	protected $namespace = '';
	protected $handler = array();
	protected $dao = null;
	protected $sid = '';
	
	public static $stickTime = 31536000;

	public function init($handler=null)
	{
		Better_Registry::set('sess', $this);

		$handler == null && $handler = Better_Config::getAppConfig()->session->handler;
			
		switch($handler) {
			case 'memcached':
				/*
				 * 设置memcached参数，将session存入memcached
				 */
				$session_host = Better_Config::getAppConfig()->memcached->host;
				$session_port = Better_Config::getAppConfig()->memcached->port;
				$session_save_path = Better_Config::getAppConfig()->memcached->class=='Memcache' ? 'tcp://'.$session_host.':'.$session_port.
					'?persistent=1&weight=1&timeout=2&retry_interval=10, ,tcp://'.$session_host.':'.$session_port
					: $session_host.':'.$session_port;
				;

				@ini_set('session.save_handler', strtolower(Better_Config::getAppConfig()->memcached->class));
				@ini_set('session.gc_maxlifetime', 4*3600);
				session_save_path($session_save_path);
				break;
			case 'files':
			default:
				break;
		}

		session_start();
		session_regenerate_id();

		if (!isset($_SESSION[$this->namespace])) {
			$_SESSION[$this->namespace] = array();
		}
	}
	
	/**
	 * 保持session
	 *
	 * @return null
	 */
	public function stick($sec=null)
	{
		$sec == null && $sec = self::$stickTime;
		$cookieParams = session_get_cookie_params();

        session_set_cookie_params(
            $sec,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure']
            );
		//session_regenerate_id(true);
	}
	
	public function destroy()
	{
		session_destroy();
	}
	
	public function __get($var)
	{
		return $this->get($var);
	}
	
	public function __set($var,$val=null)
	{
		$this->set($var, $val);
	}
	
	public function getUid()
	{
		return $this->uid;
	}
	
	/**
	 * 设置一个session变量
	 * @param $var
	 * @param $val
	 * @return unknown_type
	 */
	public function set($var,$val=null)
	{
		$key = md5(APPLICATION_ENV.'_'.$this->namespace);
		if ($val==null) {
			unset($_SESSION[$key][$var]);
		} else {
			$_SESSION[$key][$var] = $val;
		}
	}
	
	/**
	 * 取得一个session变量
	 * @param $var
	 * @return misc
	 */
	public function get($var)
	{
		$key = md5(APPLICATION_ENV.'_'.$this->namespace);
		return isset($_SESSION[$key][$var]) ? $_SESSION[$key][$var] : null;
	}

}