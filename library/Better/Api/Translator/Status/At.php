<?php

/**
 * Status的@姓名
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Status_At extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		$data = &$params['data'];
		
		if (isset($data['uid'])) {
			$result['uid'] = $data['uid'];
			$result['at_string'] = '@'.$data['nickname'];
		}
		
		return $result;
	}
}