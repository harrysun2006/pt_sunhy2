<?php

/**
 * 我是机器人
 * 开开的Andriod客户端用户都可得到，其他平台的无法获得该勋章

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Iloveandriod extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$source = $params['blog']['source'];
		if(defined('IN_API') && strrpos(strtolower($source), "and") !== FALSE){
			$result = true;
		}
		return $result;
	}
}