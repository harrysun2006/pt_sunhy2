<?php

/**
 * Email数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Email extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		
		if (isset($data['domain'])) {
			$result['domain'] = '@'.$data['domain'];
			$result['name'] = $data['name'];
			$result['image_url'] = $data['image_url'];
			$result['needpassword'] = $data['needpassword'];
			$result['username'] = $data['username'];
			$result['need_bind'] = isset($data['need_bind'])? ($data['need_bind']? 'true':'false') : 'false';
		}
		
		return $result;
	}
}