<?php

/**
 * 举报Poi
 * 
 * @package Better.Denounce
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Denounce_Poi extends Better_Denounce_Base
{
	protected static $instance = null;
	protected static $reasons = array(
		'incorrect' => '名字地点不符',
		'closedown' => '关门',
		'duplicated' => '重复',
		'other' => '其他',
		);
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 获取举报理由
	 * 
	 * @param $reason
	 * @return string
	 */
	private static function _getReason($reason)
	{
		if (array_key_exists($reason, self::$reasons)) {
			$result = self::$reasons[$reason];
		} else if (in_array($reason, self::$reasons)) {
			$result = $reason;
		} else {
			$result = self::$reasons['other'];
		}
		
		return $result;
	}	
}