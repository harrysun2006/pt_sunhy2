<?php

/**
 * 
 * Cmcc
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Market_Yongle extends Better_Market_Base
{
	private static $_instance = null;
	public $poiIds = array(
		'bj' => 0,
		'sh' => 0,
		'gz' => 0
		);	
	public $startTime = 0;
	public $endTime = 0;
	public $sales = array();
	public $salesAll = array();
	public $shopAll = array();
	
	private function __construct()
	{
		
	}
		
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}		
		return self::$_instance;
	}
	
	public function checkid($str){
		$result = $str;		
		$tempmessage = str_replace('YL+','',$str);
		$templistbeg = split('#',$tempmessage);		
		$tempstr = $templistbeg['0'].",".$templistbeg['1'].",".substr($templistbeg['2'], 0, 6);		
		$packet="51000,".$tempstr.",#";
		Better_Log::getInstance()->logInfo($packet,'yongle');
		$streamContext = stream_context_create();
		$apns = stream_socket_client('tcp://121.101.221.73:2001', $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);			
		fwrite($apns, $packet);
		$x = fread($apns, 4096);		
		fclose($apns);	
		$x = mb_convert_encoding($x,'utf8','GBK');	
		$info = explode('#',$x);
		$yongle = end($info);
		Better_Log::getInstance()->logInfo($x."--".serialize($info)."--".$yongle,'yongle');
		count($info)>5 &&  $result = str_replace("{SEAT}",$yongle,Better_Language::loadIt('zh-cn')->global->yongle->whgl->shout);
		return $result;
	}
}