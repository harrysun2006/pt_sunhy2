<?php

/**
 * 勋章数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Badge extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$badge = &$params['data'];
		$userInfo = &$params['userInfo'];
		$exchange = &$params['exchange'];
		$bigBadge = isset($params['big_badge']) ? true : false;
		$badgeSize = isset($params['badge_size']) ? (int)$params['badge_size'] : '300';

		$result = array();

		if (isset($badge['id'])) {
			
			if ($bigBadge) {
				if ($badgeSize=='300') {
					$key = 'big';
				} else {
					$key = '96';
				}
				
				$badgeUrl = str_replace('images/badges/', 'images/badges/'.$key.'/', $badge['badge_picture']);
			} else {
				$badgeUrl = $badge['badge_picture'];
			}
			
			$result['id'] = $badge['id'];
			$result['name'] = $badge['badge_name'];
			$result['image_url'] = $badgeUrl;
			$result['description'] = trim(str_replace("\n", "", $badge['got_tips']));
			$result['create_at'] = parent::time($badge['get_time']);
			$result['category'] = $badge['category'];
			$result['family_id'] = $badge['family'];
			$result['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
				'data' => $badge['poi'],
				));
			
			if (isset($exchange['total']) && $exchange['total']) {
				$result['exchange'] = array();
				$result['exchange']['expire_at'] = parent::time((float)$exchange['expire_at']);
				$result['exchange']['total'] = (int)$exchange['total'];
				$result['exchange']['remain'] = (int)$exchange['remain'];
				$result['exchange']['code'] = $exchange['code'];
				$result['exchange']['exchanged'] = $exchange['exchanged'] ? 'true' : 'false';
				$result['exchange']['note'] = $exchange['note'];
			}
		}
		
		return $result;
	}
}
