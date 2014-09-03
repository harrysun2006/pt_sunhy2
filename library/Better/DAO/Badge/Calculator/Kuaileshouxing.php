<?php

/**
 * 快乐寿星
 * 生日那天你大吼了5声
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kuaileshouxing extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		if ($userInfo['birthday']!='0000-00-00') {
		
			$offset = $tz*3600;
			$month = intval(date('m', time()+$offset));
			$day = intval(date('d', time()+$offset));		
	
			$tmp = explode('-', $userInfo['birthday']);
			$bMonth = intval($tmp[1]);
			$bDay = intval($tmp[2]);
			
			if ($month==$bMonth && $day==$bDay) {
				$result = true;
			}
		}
		
		return $result;
	}
}