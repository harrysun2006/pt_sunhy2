<?php

/**
 * 恭贺新禧
 * 你在元旦节期间签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gonghexinxi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$tz = (int)$userInfo['timezone'];
		
		$offset = $tz*3600;
		$month = intval(date('m', time()+$offset));
		$day = intval(date('d', time()+$offset));

		if ($uid && $month==1 && $day==1) {
			$result = true;
		}
		
		return $result;
	}
}