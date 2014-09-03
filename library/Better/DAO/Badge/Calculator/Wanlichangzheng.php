<?php

/**
 * 万里长征
 * Rp值过万（新的Karma)
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Wanlichangzheng extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$userInfo = $user->getUserInfo();
		if ($userInfo['rp']>=10000) {
			$result = true;
		}		
		return $result;
	}
}