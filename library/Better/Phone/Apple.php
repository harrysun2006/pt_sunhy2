<?php

/**
 * 苹果设备相关操作
 * 
 * @package Better.Phone
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Phone_Apple extends Better_Phone_Base
{
	
	/**
	 * 绑定iPhone
	 * 
	 * @param unknown_type $uid
	 * @param unknown_type $token
	 * @param unknown_type $timezone
	 * @return integer
	 */
	public static function bind($uid, $token, $timezone='8', $isPolo=false)
	{
		if (!$isPolo) {
			Better_DAO_Phone_Apple::getInstance()->deleteByCond(array(
				'uid' => $uid
				));
	
			return Better_DAO_Phone_Apple::getInstance()->replace(array(
				'uid' => $uid,
				'token' => $token,
				'timezone' => $timezone
				));			
		} else {
			Better_DAO_Phone_Applepolo::getInstance()->deleteByCond(array(
				'uid' => $uid
				));
	
			return Better_DAO_Phone_Applepolo::getInstance()->replace(array(
				'uid' => $uid,
				'token' => $token,
				'timezone' => $timezone
				));				
		}
		

	}
	
	/**
	 * 解绑iPhone
	 * 
	 * @param unknown_type $uid
	 * @param unknown_type $token
	 * @return integer
	 */
	public static function unbind($uid, $token)
	{
		return Better_DAO_Phone_Apple::getInstance()->deleteByCond(array(
			'uid' => $uid,
			));	
	}
	
	/**
	 * 获取绑定的iPhone Token
	 * 
	 * @return array
	 */
	public static function getTokens(array $uids, $type='', $isPolo=false)
	{
		$tokens = array();
		
		if (count($uids)>0) {
			if (!$isPolo) {
				$tokens = Better_DAO_Phone_Apple::getInstance()->getTokens($uids, $type);	
			} else {
				$tokens = Better_DAO_Phone_Applepolo::getInstance()->getTokens($uids, $type);
			}
					
		}
		
		return $tokens;
	}
}