<?php

/**
 * POI分类数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Category extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = '';

		$data = &$params['data'];
		
		if (isset($data['category_id'])) {
			$result = $data['category_name'];
		}	
			
		return $result;
	}
}