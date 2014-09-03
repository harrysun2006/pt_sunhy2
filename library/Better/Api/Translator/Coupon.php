<?php

/**
 * 优惠数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Coupon extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		$result = array();

		if (isset($data['nid'])) {
			$result['id'] = $data['nid'];
			$result['message'] = Better_Functions::cleanBr($data['content']);
			$result['poi_id'] = $data['poi_id'];
			$result['poi_name'] = $data['name'];
			$result['address'] = $data['address'];

      		$imageUrl = $data['image_url'];
            list($a, $b) = explode('.', $imageUrl);
			if (is_numeric($a) && is_numeric($b)) {
				$attach = Better_Attachment_Parse::getInstance($imageUrl)->result();
				$imageUrl = $attach['url'];
			}
                                                
			$result['image_url'] = $imageUrl;
			
			$result['category'] = $data['is_top'] ? 'activity' : 'coupon';
			$result['action'] = Better_Api_Translator::getInstance('coupon_action')->translate(array(
				'data' => &$data
				));
			$result['content'] = $result['message'];
			
			$badges = array();
			$bids = $this->getBadges($data['nid']);
			foreach ($bids as $bid) {
				$_badge = Better_Badge::getBadge($bid);
				$_uid = Better_Registry::get('user')->getUid(); 
				if (!$_badge || !$_uid) continue ;
				$data = Better_User::getInstance($_uid)->badge()->getBadge($bid);
				$exchange = $data;
				$exchange['note'] = Better_Language::loadDbKey('help_tips', $exchange);				
				
				$badge['badge'] = Better_Api_Translator::getInstance('badge')->translate(array(
																			'data' => $data,
																			'exchange' => $exchange,
																			//'big_badge' => false,
																			'badge_size' => 300,
																			));
				$badges[] = $badge;
			}
			$result['badges'] = $badges;
		}
		
		return $result;
	}
	
	
	public function getBadges($nid)
	{
		$row = Better_DAO_Poi_N2b::getInstance()->get($nid);
		$bids = $row['bids'];
		
		if ($bids) {
			return explode(',', $bids);
		}
		
		return array();
		
	}
}
