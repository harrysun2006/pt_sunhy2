<?php

/**
 * 通过ssh代理翻墙
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Proxy
{
	
	public static function getSocket($dest, $destPost=80)
	{
		$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
		
		foreach ($hosts as $host) {
			list($ip, $port) = explode(':', $host);
			
			$socket = new Better_Socket5($ip, $port);
			$socket->set_timeout(0);
			$socket->set_dnstunnel(true);			
			
			if ($socket->connect($dest, $destPost)===true) {
				return $socket;
				break;
			}
		}
		
		return null;
	}
}