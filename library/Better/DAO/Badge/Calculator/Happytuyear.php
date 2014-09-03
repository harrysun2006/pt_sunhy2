<?php

/**
 * Happy 兔 year

 * 2月1日（0点）~2月8日（24点），登录开开即可获得此勋章

 * 
 * 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Happytuyear extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$start = gmmktime(16, 0, 0, 1, 31, 2011);		
		$end = gmmktime(16, 0, 0, 2, 8, 2011);
		$now = time();
		if($now<=$end && $now>=$start){
			$result = true;
		}
		return $result;
	}
}