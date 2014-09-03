<?php

/**
 * Better钩子对象
 * 
 * 在一些典型的事件发生时（如发布一个消息，删除一个消息，用户关注了其他人，等等），
 * 往往需要进行额外的一些逻辑处理，这个hook对象则将这些额外的操作从原有逻辑代码中
 * 分离，以便更好的维护
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better
 */
class Better_Hook
{
	public static $RESULT_ALL_OK = '8888';
	public static $RESULT_BLOG_NEED_CHECK = 1000;
	
	public static $hookResults = array();
	public static $hookMessages = array();
	public static $hookNotify = array();
	public static $tmp = array();
	public static $params = array();
	
	protected static $hooks = array();
	
	private static $_in_hook = false;
	private $_hooks = array();

	private function __construct()
	{
		
	}
	
	/**
	 * 钩子工厂
	 * 
	 * @param $name
	 * @return Better_Hook_Base
	 */
	public static function factory($name)
	{
		return self::register($name);
	}
	
	/**
	 * 某个事件是否为hook调用
	 * 
	 * @return bool
	 */
	public static function inHook()
	{
		return self::$_in_hook ? true : false;
	}
	
	/**
	 * 注册钩子
	 * 
	 * @param $name
	 * @return null
	 */
	protected static function register($name)
	{
		$instance = new self();
		
		if (is_array($name)) {
			$name = array_unique($name);
			foreach ($name as $n) {
				$instance->_register($n);
			}
		} else {
			$instance->_register($name);
		}
		
		$instance->_register('Clean');
		$instance->_register('Tracelog');

		return $instance;
	}
	
	private  function _register($name)
	{
		if (!isset(self::$hooks[$name])) {
			$hook = 'Better_Hook_'.ucfirst($name);
			self::$hooks[$name] = new $hook();
		}
		
		!in_array($name, $this->_hooks) && $this->_hooks[] = $name;
	}
	
	/**
	 * 调用事件
	 * 
	 * @param $event
	 * @param $params
	 * @return null
	 */
	public function invoke($event, $params=array())
	{
		self::$_in_hook = true;
		
		self::$params[$event] = $params;
		
		foreach ($this->_hooks as $hook)
		{
			$method = 'on'.ucfirst($event);
			
			try {
				if (method_exists(self::$hooks[$hook], $method)) {
					call_user_func(array(
						self::$hooks[$hook], 
						$method
						), $params);
				}
			} catch (Exception $e) {
				Better_Log::getInstance()->logEmerg('Exception in ['.$method.']: ['.$e.'], Msg:['.$e->getTraceAsString().']', 'hook');
			}
		}
		
		self::$_in_hook = false;
	}
	
	/**
	 * 获取钩子执行过后的消息
	 * 
	 * @param $event
	 * @return string
	 */
	public static function getMessages($event)
	{
		return isset(self::$hookMessages[$event]) ? self::$hookMessages[$event] : '';
	}
	
	/**
	 * 获取钩子执行过后的结果
	 * 
	 * @see getMessage
	 */
	public static function getResult($event)
	{
		return isset(self::$hookResults[$event]) ? self::$hookResults[$event] : self::$RESULT_ALL_OK;
	}
	
	/**
	 * 
	 * @param unknown_type $event
	 */
	public static function getNotify($event)
	{
		$notify = '';
		if (isset(self::$hookNotify[$event]) && is_array(self::$hookNotify[$event])) {
			foreach (self::$hookNotify[$event] as $msg) {
				if (strlen($notify)==0) {
					$notify .= $msg;
				} else {
					$notify .= ', '.$msg;
				}
			}
		}
		
		return $notify;
	}

}