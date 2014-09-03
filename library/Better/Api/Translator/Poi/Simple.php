<?php

/**
 * 简化的POI数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Simple extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		$force = isset($data['force']) ? $data['force'] : false;
		if ($force || (isset($data['poi_id']) && $data['poi_id'])) {
			$result['id'] = $data['aibang_id'] ? $data['aibang_id'] : $data['poi_id'];
			$result['name'] = isset($data['poi_name']) ? $data['poi_name'] : $data['name'];
			$result['lon'] = $data['lon'];
			$result['lat'] = $data['lat'];			
			$result['category_id'] = $data['category_id'];
			$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data, '48');			
		}	
			
		return $result;
	}
}