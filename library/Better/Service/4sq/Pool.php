<?php

/**
 * 4sq poi池
 * 
 * @package Better.Service.4sq
 * @author yangl
 *
 */
class Better_Service_4sq_Pool
{

  /**
   * 将4sq poi => kai poi
   * better_poi.better_poi_foursquare表记录关联kai poi's id
   * @param unknown_type $vid
   */
	public static function fsq2our($vid)
	{
		$krow = array();
		if ($vid) {
		  $vrow = Better_DAO_Poi_Foursquare::getInstance()->get4sqPoi($vid);
			if (!$vrow['poi_id']) { // 没有关联的kai poi, 则创建并关联
				$krow = self::push2us($vrow);
				$krow['#new'] = true;
			} else {
				$krow = Better_DAO_Poi_Info::getInstance()->getPoi($vrow['poi_id']);
				$krow['#new'] = false;
			  /*
				if ($vrow['name'] != $krow['name']) {
				  // 注意similar_text函数的用法, Better_Service_Aibang_Pool中的程序逻辑有问题
				  // 应该为: 
				  // $similar = 0;
				  // similar_text($vrow['name'], $krow['name'], $similar);
				  // if ($similar < 0.2) ... 
					$similar = similar_text($vrow['name'], $krow['name']);
					if ($similar < 20) { // 重建并关联
						$krow = self::push2us($vrow);
					}
					Better_Log::getInstance()->logInfo('4sq:['.$vrow['name'].'], kai:['.$krow['name'].'], Rank:['.$similar.']', '4sq_exception');
				}
				*/
			}
		}
		return $krow;
	}
	
	protected static function push2us(array $params)
	{
		$kid = 0;
		$lon = $params['lon'];
		$lat = $params['lat'];
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);

		$kpoi = array(
			'city' => $params['city'],
			'category_id' => Better_Service_4sq_Poi::fsqcat2kai($params['category_name']),
			'name' => $params['name'],
			'x' => $x,
			'y' => $y,
			'address' => $params['address'],
			'phone' => $params['phone'],
			'label' => '',
			'link' => '',
			'logo' => '',
			'intro' => '',
			'country' => $params['country'],
			'province' => $params['state'],
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
			'aibang_id' => 0,
			'closed' => 0,
			'level_adjust' => 20,
		);
		$kid = Better_DAO_Poi_Info::getInstance()->insert($kpoi);
		$kpoi['poi_id'] = $kid;
		Better_DAO_Poi_Foursquare::getInstance()->updateByCond(array(
		  'poi_id' => $kid,
      ), array(
			'id' => $params['id'],
		));

		Better_Hook::factory(array(
			'Fulltext'
		  ))->invoke('PoiCreated', array(
				'poi_info' => $kpoi,
				'poi_id' => $kid,
				'uid' => 0,
			));
		return $kpoi;
	}

}