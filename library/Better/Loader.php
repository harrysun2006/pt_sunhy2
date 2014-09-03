<?php

/**
 * Better文件加载器
 * 使用本类中的autoload机制加载文件时，需要预先包含opcache处理相关的类库！！！
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Loader
{
	protected static $cacher = null;
	protected static $instance = null;
	
	protected static $included = array();
	protected static $cached = array();
	
	private function __construct()
	{
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/**
	 * This method is out of time
	 * 
	 * @return null
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadIt'));
	}
	
	/**
	 * 加载一个Class
	 * 
	 * @return misc
	 */
	public function loadIt($class)
	{
		$className = strtolower($class);
		if (class_exists($className) || interface_exists($className) || class_exists($class) || interface_exists($class) || $class==__CLASS__) {
			return false;
		}

		if ((!defined('BETTER_FORCE_INCLUDE') || (defined('BETTER_FORCE_INCLUDE') && !BETTER_FORCE_INCLUDE)) && ((APPLICATION_ENV=='production' || APPLICATION_ENV=='testing' || APPLICATION_ENV=='home_apc') && (strpos($class, 'Better_')===0 || strpos($class, 'Zend_')===0))) {
			//	如果是Better_开头的Class
			return self::_loadBetterClass($class);
		} else {
			$file = (str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
			return self::_loadFile($file);
		}
	}
	
	/**
	 * 对于Better_开头的Class，采用本地opcache将文件缓存到内存
	 * 这么做有个前提条件，就是所有Better_开头的class的文件里面不能有自己的require_once/require，所有的类库加载都
	 * 必须通过这个autoload机制来加载，否则容易造成class redeclare
	 * 
	 * 这么做还有一个要注意的，当更新了本地文件以后，需要刷新opcache的缓存来使文件修改生效
	 * 另：需要关闭APC的apc.cache_by_default
	 * 
	 * @return bool
	 */
	protected static function _loadBetterClass($class)
	{
		if (!(strtolower($class)=='zend_uri' && defined('BETTER_PASSBY_ZEND_URI')) && !in_array($class, self::$included)) {
			self::$included[] = $class;
			
			$cacher = Better_Cache::local();
			$key = 'Better_Classes_'.$class;
	
			$code = $cacher->get($key);
			
			if (!empty($code)) {
				eval($code);
			} else {
				try {
					$file = APPLICATION_PATH.'/../library/'.implode(DIRECTORY_SEPARATOR, explode('_', $class)).'.php';
					$c = file_get_contents($file);
					$c = ltrim($c, '<'.'?php');
					$c = rtrim($c, '?'.'>');				
					eval($c);
					$cacher->set($key, $c);
				} catch (Exception $e) {
					die($e->getTraceAsString());
				}
			}
		}

		return true;
	}

	protected static function _loadFile($file)
	{
		include_once($file);
	}

	public static function load($class)
	{
		return self::_loadFile($class);
	}
	

}