<?php

/**
 * 
 * POI数据转换
 * 
 * @package Better.Api.Translate
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		$smallIcon = $params['small_icon'] ? (bool)$params['small_icon'] : false;
			
		if (isset($data['poi_id'])) {
			$iconSize = $smallIcon ? 'small' : '48';
			
			$result['id'] = $data['aibang_id'] ? $data['aibang_id'] : $data['poi_id'];
			$result['category_name'] = $data['category_name'];
			$result['category_id'] = $data['category_id'];
			if($data['logo']){
				$result['category_image_url'] = $data['logo'];
			} else {
				$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data, $iconSize);
			}
			//$result['category_image_url'] = Better_Poi_Category::getCategoryImage($data, $iconSize);
			$result['coupon'] = isset($data['notification']['poi_id']) ? Better_Api_Translator::getInstance('coupon')->translate(array(
				'data' => &$data['notification'],
				'userInfo' => &$userInfo,
				)) : array();
			$result['name'] = $data['name'];
			$result['lon'] = $data['lon'];
			$result['lat'] = $data['lat'];			
			$result['address'] = $data['address'];
			$result['phone'] = $data['phone'];
			$result['city'] = $data['city'];
			$result['province'] = $data['province'];
			$result['country'] = $data['country'];
			$result['image_url'] = $data['logo_url'] ? $data['logo_url'] : Better_Config::getAppConfig()->poi->default_logo;
			$result['checkin_count'] = $data['checkins'];
			$result['tips_count'] = $data['tips'];	
			$result['shout_count'] = $data['posts'];
			$result['my_checkin_count'] = (int)$data['my_checkin_count'];		
			$result['coupons_count'] = (int)$data['coupons_count'];
			$result['activity_count'] = $data['activity_count'];
			$result['is_top'] = (isset($data['top']) && $data['top']) ? 'true' : 'false';
			$result['last_checkin_at'] = parent::time($data['last_checkin_at']);
			
			$result['mayor_checkin_count'] = isset($data['mayor_checkin_count']) ? $data['mayor_checkin_count'] : 0;//掌门签到数
			$result['mayor'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$data['major_detail'],
				'userInfo' => &$userInfo,
				));
			$result['creator'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$data['creator_detail'],
				'userInfo' => &$data['userInfo'],
				));
			$result['create_at'] = parent::time($data['create_time']);
			
			if ($data['favorited']) {
				$result['favorited'] = $data['favorited'] ? 'true' : 'false';
			} else {
				$fv = Better_User::getInstance($userInfo['uid'])->poiFavorites();
				$fs = $fv->getFavorites();
				$result['favorited'] = in_array($result['id'], $fs) ? 'true' : 'false';
			}
			
			$result['visitors_count'] = (int)$data['visitors'];
		}

		return $result;
	}
}