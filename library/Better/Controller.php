<?php

/**
 * Better控制器基�?
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 * 
 */

abstract class Better_Controller extends Zend_Controller_Action
{
	protected $config = null;
	protected $jsUrl = 'js';
	protected $cssUrl = 'css';
	protected $firePhp= null;
	protected $uid = 0;
	protected $kkEncoding = false;
	
	/**
	 * 基本的初始化操作
	 *
	 */
	public function init()
	{
		$params = $this->getRequest()->getParams();
		defined('BETTER_CONTROLLER_MODULE') || define('BETTER_CONTROLLER_MODULE', $params['module'] ? $params['module'] : 'web');
		
		if ($this->getRequest()->getParam('__DB__', '0')=='1') {
			define('LOG_PAGE_QUERIES', true);
		} else {
			define('LOG_PAGE_QUERIES', false);
		}

		//	如果是ajax请求，跳过视图渲染
		if ($this->getRequest()->isXmlHttpRequest()) {
			Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		}

		$this->config = Better_Config::getAppConfig();
		
		$this->jsUrl = $this->view->jsUrl = $this->config->javascript->domain.$this->config->javascript->path;
		$this->cssUrl = $this->view->cssUrl = $this->config->css->domain.$this->config->css->path;

		if (BETTER_CONTROLLER_MODULE=='web') {
			$cacher = Better_Cache::remote();
			$cacheKey = md5($_SERVER['REQUEST_URI']).'_IS_404';
			$is404 = (bool)$cacher->get($cacheKey);
	
			if ($is404) {
				$this->view->cachedErrors = true;
				$this->view->css = 'default';
				$this->view->lang = Better_Language::load();
				$this->view->error_code= '404';
				$this->view->message = '';
				header("HTTP/1.1 404 Not Found");
				$this->view->render('error/error.phtml');
				exit(0);			
			}
		}
		header('P3P: CP=CAO PSA OUR');				
	}	
	
	public function sendSpecHeader()
	{
		header('Cache-Control: no-cache');
  		header('Pragma: no-cache');		
  		header('Expires: Fri, 01 Jan 1970 05:00:00 GMT');
	}
	
	public function sendSquidHeader($uid=0)
	{
		header('U: '.intval($uid));
	}	
	
	public static function sendSquidHeaderC($code)
	{
		if (strlen($code)) {
			header('C: '.$code);
		} else {
			Better_Registry::set('squid_code', 0);
		}
		Better_Registry::set('squid_code', $code);
	}
	
	protected static function t($what)
	{
		Better_Log::getInstance()->logTime($what);
	}
	
	protected function ft()
	{
		$ft = $this->config->poi->fulltext->enabled;
		
		return $ft;
	}
	
	/**
	 * 检测客户端是否支持gzip
	 * 
	 * @return bool
	 */
	protected function acceptGz()
	{
		$flag = false;
		if (strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {
			$flag = true;
		} else {
			$headers = function_exists('getallheaders') ? getallheaders() : array();
			$kkEncoding = trim($headers['KAI-Encoding']);
			if ($kkEncoding) {
				$accepted = explode(',', $kkEncoding);
				foreach ($accepted as $accept) {
					if (trim($accept)=='gzip') {
						$this->kkEncoding = true;
						$flag = true;
						break;
					}
				}
			}
		}
		
		return $flag;
	}		
}