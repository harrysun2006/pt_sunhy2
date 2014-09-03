<?php

/**
 * 
 * 特别信息
 * 目前只在签到后信息中使用
 * 
 * @package Better.Api.Translate
 * @author leip
 *
 */
class Better_Api_Translator_Special extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		
		$result = array();
		$result['category'] = $data['category'] ? $data['category'] : 'default';
		$result['message'] = Better_Functions::cleanBr($data['message']);
		$result['image_url'] = $data['image_url'];
		$result['url'] = $data['url'];
			
		return $result;		
	}
}