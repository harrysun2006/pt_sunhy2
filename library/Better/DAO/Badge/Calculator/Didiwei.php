<?php

/**
 * 敌敌畏
 * 你的Karma低于零
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Didiwei extends Better_DAO_Badge_Calculator_Spec_Karma
{
	public static function touch(array $params)
	{
		$params['limit'] = 0;
		$params['direct'] = 'lt';
		
		return parent::touch($params);
	}
}