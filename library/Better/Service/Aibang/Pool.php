<?php

/**
 * 爱帮poi池
 * 
 * @package Better.Service.Aibang
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_Aibang_Pool
{
	
	public static function ab2our($abId)
	{
		$poiId = 0;

		if ($abId) {
			$row = Better_DAO_Poi_Info::getInstance()->getPoiByAb($abId);

			if (!$row['poi_id']) {
				$row = Better_DAO_Poi_Aibang::getInstance()->getAbPoi($abId);
				if ($row['bizid']) {
					$poiId = self::pushPoolToUs($row);
				}
			} else {
				$poiId = $row['poi_id'];
							
				$ourName = $row['name'];
				$abRow = Better_DAO_Poi_Aibang::getInstance()->getAbPoi($abId);
				
				if ($abRow['name']!=$row['name']) {
					$similar = similar_text($abRow['name'], $row['name']);
					if ($similar<20) {
						Better_DAO_Poi_Info::getInstance()->updateByCond(array(
							'aibang_id' => '0',
							), array(
								'poi_id' => $poiId
							));
						$poiId= self::pushPoolToUs($abRow);
					}
					Better_Log::getInstance()->logInfo('Ab:['.$abRow['name'].'], Our:['.$row['name'].'], Rank:['.$similar.']', 'ab_exception');
				}
			}
		}
		
		return $poiId;
	}
	
	public static function pushPoolToUs(array $params)
	{
		$poiId = 0;
		
		$x = $params['x'];
		$y = $params['y'];
		
		$closed = preg_match('/共产党/', $params['name']) ? 1 : 0;

		$poiToInsert = array(
			'city' => '',
			'category_id' => Better_Service_Aibang_Category::trans($params['tag']),
			'name' => $params['name'],
			'x' => $x,
			'y' => $y,
			'star' => 0,
			'score' => 0,
			'price' => $params['price'],
			'address' => $params['addr'],
			'phone' => $params['tel'],
			'label' => $params['tag'],
			'link' => '',
			'logo' => '',
			'intro' => $params['desc'],
			'country' => '',
			'province' => '',
			'creator' => 0,
			'major' => 0,
			'major_change_time' => 0,
			'create_time' => time(),
			'checkins' => 0,
			'favorites' => 0,
			'users' => 0,
			'certified' => 1,
			'visitors' => 0,
			'posts' => 0,
			'tips' => 0,
			'aibang_id' => $params['bizid'],
			'closed' => $closed
			);
		$poiId = Better_DAO_Poi_Info::getInstance()->insert($poiToInsert);
		
		if (!$closed) {

			Better_Hook::factory(array(
				'Fulltext'
				))->invoke('PoiCreated', array(
					'poi_info' => $poiToInsert,
					'poi_id' => $poiId,
					'uid' => 0,
				));
								
			$abUserId = (int)Better_Config::getAppConfig()->user->aibang_user_id;
			$review = trim($params['review']);
			if ($abUserId && strlen($review)) {
				if (mb_strlen($review)>=140) {
					$review .= '...';
				}
				
				$bid = Better_Blog::post($abUserId, array(
					'message' => $review,
					'upbid' => 0,
					'attach' => '',
					'source' => 'kai',
					'poi_id' => $poiId,
					'type' => 'tips'
					));	
			}
		} else {
			$poiId = 0;
		}
		
		return $poiId;
	}

}