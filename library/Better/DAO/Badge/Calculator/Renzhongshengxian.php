<?php

/**
 * 人中圣贤
 * 你的Karma达到800
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Renzhongshengxian extends Better_DAO_Badge_Calculator_Spec_Karma
{
	public static function touch(array $params)
	{
		$params['limit'] = 800;
		
		return parent::touch($params);
	}
}