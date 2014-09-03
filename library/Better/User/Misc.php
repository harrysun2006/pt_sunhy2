<?php

/**
 * 用户相关的一些其他方法
 * 不好归类，暂时就放在这里面了
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_Misc extends Better_User_Base
{
	protected static function randomArray(&$arr, $num=5)
	{
		$newArr = array();
		$rdm = array();

		while (count($rdm)<$num) {
			$x = rand(0,$num);
			if (!in_array($x, $rdm)) {
				$rdm[] = $x;
			}
		}
		
		foreach($rdm as $v) {
			$newArr[] = $arr[$v];
		}
		
		return $newArr;
	}
	
	public static function Random5UsersByIp($ip)
	{
		return array();
	}
	
	/**
	 * 随机读取5个用户的资料
	 * 
	 * @return array
	 */
	public static function Random5Users()
	{
		return array();
	}

}