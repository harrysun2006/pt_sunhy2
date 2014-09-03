<?php

/**
 * 地点分类数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Class extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		
		if (isset($data['category_id'])) {
			$result['id'] = $data['category_id'];
			$result['name'] = $data['category_name'];
		}	
			
		return $result;
	}
}