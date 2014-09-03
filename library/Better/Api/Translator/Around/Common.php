<?php

/**
 * 附近探索 公用格式
 * 
 * @package Better.Api.Translator.User
 * @author yangl
 *
 */
class Better_Api_Translator_Around_Common extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = $params['data'];
		$type = $params['type'];
		$label = $params['label'];
		$result = array();
		
		$result['id'] = $data['id'];
		$result['create_at'] = isset($data['dateline'])? parent::time($data['dateline']) : '';
		$result['message'] = trim($data['content']);
		$result['message_url'] = '';
		$result['image_url'] = isset($data['img_url']) ? $data['img_url'] : '';
		$result['poi_id'] = isset($data['poi_id']) ? $data['poi_id'] : '';
		$result['poi_name'] = isset($data['name']) ? $data['name'] : '';
		$result['poi_address'] = isset($data['address']) ? $data['address'] : '';
		$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data);
		
		$result['category'] = 'external';
		$result['external'] = array(
			'type'=> $type,
			'label' => $label,
			'name'=> $data['source'],
			'image'=> $data['icon'],
			'url'=> ''
		);
		
		$result['place'] = Better_Api_Translator::getInstance('place')->translate(array(
			'lon'=> $data['lon'],
			'lat'=> $data['lat']
		));
			
		$result['action'] = Better_Api_Translator::getInstance('around_action')->translate(array(
			'data'=>$data
		));
		
		
		return $result;
	}
}