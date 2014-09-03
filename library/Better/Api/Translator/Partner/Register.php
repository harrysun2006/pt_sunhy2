<?php

/**
 * 第三方注册应用
 * 
 * @package Better.Api.Translator.Partner
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Partner_Register extends Better_Api_Translator_Base
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