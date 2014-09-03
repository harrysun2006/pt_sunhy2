<?php

/**
 * 第三方登录应用
 * 
 * @package Better.Api.Translator.Partner
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Partner_Login extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		
		if (isset($data['id'])) {
			$result = &$data;
		}
		
		return $result;
	}
}