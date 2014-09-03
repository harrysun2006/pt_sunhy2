<?php

/**
 * 
 * 宝物兑换
 * 
 * @package Better.Game
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Game_Exchange
{
	/**
	 * 
	 * 获取某一期宝物兑换记录
	 * 
	 * @param unknown_type $time
	 * @return array
	 */
	public static function getExchangeHistory($time)
	{
		$result = array();
		
		$rows = Better_DAO_Game_Exchange::getInstance()->getHistory($time);
		foreach ($rows as $row) {
			$userInfo = Better_User::getInstance($row['uid'])->getUser();
			$result[] = array(
				'data' => $row,
				'user' => $userInfo
				);
		}

		return $result;
	}
}