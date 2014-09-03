<?php

/**
 * POI基本数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Concise extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		$smallIcon = isset($params['small_icon']) ? (bool)$params['small_icon'] : false;
		
		if (isset($data['poi_id'])) {
			$iconSize = $smallIcon ? 'small' : '48';
			
			$result['id'] = $data['aibang_id'] ? $data['aibang_id'] : $data['poi_id'];
			if ($data['no'] && $params['logid']) {
				$result['id'] .= '_' . $params['logid'] .  '_' . $data['no'];
			}
			
			$result['category_id'] = $data['category_id'];
			if($data['logo']){
				$result['category_image_url'] = str_replace(48,$iconSize,$data['logo']);
			} else {
				$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data, $iconSize);
			}
			//$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data, $iconSize);
			$result['name'] = $data['name'];
			$result['lon'] = $data['lon'];
			$result['lat'] = $data['lat'];
			$result['address'] = $data['address'];
			$result['city'] = $data['city'];
			$result['province'] = $data['province'];
			$result['country'] = $data['country'];
			$result['is_top'] = (isset($data['top']) && $data['top']) ? 'true' : 'false';
			$result['todo'] = isset($data['todo']) ? $data['todo'] : '';
			
			$result['last_checkin_at'] = parent::time($data['checkin_time']);
			$result['my_checkin_count'] = $data['checkin_count'];
							
			$result['coupon'] = isset($data['notification']['poi_id']) ? Better_Api_Translator::getInstance('poi_notification')->translate(array(
				'data' => &$data['notification'],
				'userInfo' => &$userInfo,
				)) : array();			
		}	
			
		return $result;
	}
}