<?php

/**
 * 果粉
 * 开开的苹果客户端用户都可得到，其他平台的无法获得该勋章

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Applefan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$source = $params['blog']['source'];
		if(defined('IN_API') && (strrpos(strtolower($source), "ifn") !== FALSE || strrpos(strtolower($source), "iphone") !== FALSE)){
			$result = true;
		}
		return $result;
	}
}