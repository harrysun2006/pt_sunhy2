<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_DevController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
	}
	
	public function indexAction()
	{
		
	}
	
	public function phpinfoAction()
	{
		phpinfo();
		exit(0);
	}
	
	public function apcAction()
	{
		if ($this->getRequest()->getParam('clear', 0)=='1') {
			apc_clear_cache();	
		}
		
		$this->view->info = apc_cache_info();
	}
	
	public function memcacheAction()
	{
		$config = Better_Config::getAppConfig();
		
		$m = new Memcache();
		$m->addServer($config->memcached->host, $config->memcached->port);
		
		$stats = $m->getExtendedStats();
		$this->view->stats = $stats[$config->memcached->host.':'.$config->memcached->port];

		$this->view->memcached_host = $config->memcached->host;
		$this->view->memcached_port = $config->memcached->port;
		
		$m->close();
	}

}