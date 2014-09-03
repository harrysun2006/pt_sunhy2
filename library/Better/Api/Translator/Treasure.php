<?php

/**
 * 公共宝物数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Treasure extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];

		if (isset($data['id'])) {
			$result['name'] = $data['name'];
			$result['id'] = $data['id'];
			$result['description'] = $data['description'];
			$result['image_url'] = $data['image_url'];
		}
		
		return $result;
	}
}