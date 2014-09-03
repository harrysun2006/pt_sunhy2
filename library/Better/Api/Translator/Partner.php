<?php

/**
 * 第三方数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Partner extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		$data = &$params['data'];

		if (isset($data['id'])) {
			$result['id'] = $data['id'];
			$result['findfriend_id'] = $data['findfriend_id'];
			$result['name'] = $data['name'];
			$result['domain'] = $data['domain'];
			$result['image_url'] = $data['image_url'];
			$result['needpassword'] = $data['needpassword'] ? 'true' : 'false';
			$result['type'] = $data['type'];
			$result['binded_name'] = $data['binded_name'];
			$result['notice'] = $data['notice'];
			$result['auth_url'] = $data['auth_url'];
			$result['followkai'] = $data['followkai'];
			$result['sms_no'] = $data['sms_no'];
			$result['sms_content'] = $data['sms_content'];
		}
		
		return $result;
	}
}