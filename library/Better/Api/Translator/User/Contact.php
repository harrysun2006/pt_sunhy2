<?php

/**
 * 转换用户号码簿
 * 
 * @package Better.Api.Translator.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_User_Contact extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		$phone = $params['phone'];

		$result = array();
			
		if (isset($data['phone'])) {
			$result['id'] = $data['id'];
		}
		
		return $result;
	}
}