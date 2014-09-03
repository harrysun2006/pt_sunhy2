<?php

/**
 * 仙风道骨
 * 你的Karma达到1000
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xianfengdaogu extends Better_DAO_Badge_Calculator_Spec_Karma
{
	public static function touch(array $params)
	{
		$params['limit'] = 999;
		return parent::touch($params);
	}
}