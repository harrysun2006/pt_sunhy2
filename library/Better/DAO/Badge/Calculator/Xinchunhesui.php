<?php

/**
 * 新春贺岁
 * 你在春节期间签到过
 * 
 * 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xinchunhesui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;

		if (APPLICATION_ENV=='production') {
			$start = gmmktime(16, 0, 0, 2, 1, 2011);
		} else {
			$start = gmmktime(16, 0, 0, 1, 1, 2011);
		}
		
		$end = gmmktime(16, 0, 0, 2, 8, 2011);
		$now = time();
		if($now<=$end && $now>=$start){
			$result = true;
		}
		return $result;
	}
}