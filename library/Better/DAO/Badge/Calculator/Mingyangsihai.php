<?php

/**
 *名扬四海
 Karma达到4000
 
 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mingyangsihai extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$userInfo = $user->getUserInfo();
		if ($userInfo['rp']>=4000) {
			$result = true;
		}		
		return $result;
	}
}