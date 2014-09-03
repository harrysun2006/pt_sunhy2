<?php

/**
 * 用户状态
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_State extends Better_User_Base
{
	const SIGNUP_VALIDATING = 'signup_validating';
	const UPDATE_VALIDATING = 'update_validating';
	const ENABLED = 'enabled';
	const LOCKED = 'locked';
	const BANNED = 'banned';
	const MUTE = 'mute';
	
	public static function isValidLoginState($state)
	{
		$flag = false;
		
		switch ($state) {
			case self::ENABLED:
			case self::MUTE:
			case self::LOCKED:
			case self::UPDATE_VALIDATING:
				$flag = true;
				break;
		}
		
		return $flag;
	}
	
	public static function isValidState($state)
	{
		$flag = false;
		
		switch ($state) {
			case self::SIGNUP_VALIDATING:
			case self::UPDATE_VALIDATING:
			case self::ENABLED:
			case self::BANNED:
			case self::MUTE:
			case self::LOCKED:
				$flag = true;
				break;
		}
		
		return $flag;
	}
	
	public static function isActiveState($state, $cellNo='')
	{
		$flag = false;
		
		if ($state==self::ENABLED || $state==self::LOCKED || Better_User_Validator::cell($cellNo)) {
			$flag = true;
		}
		
		return $flag;
	}
}