<?php

/**
 * 世间名士
 * 你的Karma达到500
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shijianmingshi extends Better_DAO_Badge_Calculator_Spec_Karma
{
	public static function touch(array $params)
	{
		$params['limit'] = 500;
		return parent::touch($params);
	}
}