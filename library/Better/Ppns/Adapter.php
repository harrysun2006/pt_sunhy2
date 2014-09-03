<?php

/**
 * 处理ppns连接的适配器
 * 
 * @TODO 按照Zend_Http_Client的规范写一个处理ppns的适配器
 * 
 * @package Better.Ppns
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Ppns_Adapter extends Zend_Http_Client_Adapter_Interface
{
	protected $socket = null;
	
    public function setConfig($config = array())
    {
    	parent::setConfig($config);
    }

    public function connect($host, $port = 80, $secure = false)
    {
    }

    public function write($method, $url, $http_ver = '1.1', $headers = array(), $body = '')
    {
    }

    public function read()
    {
    }

    public function close()
    {
    }	
}