<?php

/**
 * 大声展
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Getitlouder0926 extends Better_DAO_Badge_Calculator_Spec_Getitlouder
{

	public static function touch(array $params)
	{
		self::$md = substr(__CLASS__, -4);
		return parent::touch($params);
	}
}