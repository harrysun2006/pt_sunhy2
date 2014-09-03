<?php

/**
 * 机器人工厂
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Robot
{
	protected static $protocols = array(
		'msn'
		);
	protected static $instance = array();
	protected static $robots = array();
	
	public static function getInstance($uid, $protocol)
	{
		$key = $uid.'_'.$protocol;
		
		if (in_array($protocol, self::$protocols)) {
			if (!isset(self::$instance[$key])) {
				$robot = Better_User_Bind_Im::getInstance($uid)->getBindedIm($protocol);
				if ($robot) {
					$class = 'Better_Robot_'.ucfirst(strtolower($protocol));
					self::$instance[$key] = new $class($uid, $robot);
				} else {
					self::$instance[$key] = null;
				}
			}
		} else {
			throw new Better_Exception('Robot protocol '.$protocol.' not implement');
		}
		
		return self::$instance[$key];
	}
	
	/**
	 * 检测一段字符是否可以分析为机器人指令
	 * 
	 * @param $txt
	 * @return array
	 */
	public static function isCommand($txt)
	{
		$txt = trim($txt);
		$txt = trim($txt, "\n");
		
		$command = '';
		$username = '';
		$content = '';
		
		if (strtolower($txt)=='k' || strtolower($txt)=='on') {
			$command = 'k';
		} else if (strtolower($txt)=='g' || strtolower($txt)=='off') {
			$command = 'g';
		} else if (strtolower($txt)=='cancel') {
			$command = 'cancel';
		} else if (strtolower($txt)=='h') {
			$command = 'h';
		} else {
			$txt = preg_replace('/s(?=s)/', ' ', $txt);
			//$txt = preg_replace('/[nrt]/', ' ', $txt);
			
			$arr = explode(' ', $txt);
			if (count($arr)>1) {
				switch(strtolower($arr[0])) {
					case 'k':
						$command = 'k';
						$username = $arr[1];
						break;
					case 'g':
						$command = 'g';
						$username = $arr[1];
						break;
					case 's':
						if (count($arr>2)) {
							$command = 's';
							$username = $arr[1];
							$tmp = array_diff($arr, array($arr[0], $arr[1]));
							$content = implode(' ', $tmp);
						}
						break;
				}
			}
		}
		
		return array(
			'command' => $command,
			'username' => $username,
			'content' => $content,
			);		
	}

}