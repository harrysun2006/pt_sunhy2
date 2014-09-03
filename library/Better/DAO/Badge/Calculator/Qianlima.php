<?php

/**
 * 千里马
 Karma达到1000
 您的karma值突破1000点了
 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qianlima extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		
		$result = false;
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$userInfo = $user->getUserInfo();
		if ($userInfo['rp']>=1000) {
			$result = true;
		}		
		return $result;
	}
}