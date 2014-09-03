<?php

/**
 * 用户的POI收藏
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_PoiFavorites extends Better_User_Base
{
	protected static $instance = array();
	protected $favorites = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 获取所有poi收藏的id
	 * 
	 * @return array
	 */
	public function getFavorites()
	{
		if (!count($this->favorites)>0) {
			$this->favorites = Better_DAO_User_PoiFavorites::getInstance($this->uid)->getFavorites();
		}
		
		return $this->favorites;
	}

	/**
	 * 新增POI收藏
	 * 
	 * @param $poiId
	 * @return array
	 */
	public function add($poiId)
	{
		$poiId = (int)$poiId;
		$codes = array(
			'INVALID_POI' => -1,
			'FAILED' => 0,
			'SUCCESS' => 1,
			);
		$result = array(
			'codes' => &$codes,
			'code' => 0
			);
		
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			if ($poi->poi_id) {
				$exists = Better_DAO_User_PoiFavorites::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'poi_id' => $poiId
					));
				
				Better_DAO_User_PoiFavorites::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'poi_id' => $poiId,
					'dateline' => time(),
					));
					
				if (!$exists) {
					$poi->update(array(
						'favorites' => ++$poi->favorites
						));
				}
				
				$code = $codes['SUCCESS'];
			} else {
				$code = $codes['INVALID_POI'];
			}
		} else {
			$code = $codes['INVALID_POI'];
		}
		$result['code'] = $code;
		
		return $result;
	}
	
	/**
	 * 删除POI收藏
	 * 
	 * @param $poiId
	 * @return bool
	 */
	public function delete($poiId)
	{
		$poiId = (int)$poiId;
		$codes = array(
			'INVALID_POI' => -1,
			'FAILED' => 0,
			'SUCCESS' => 1,
			);
		$result = array(
			'codes' => &$codes,
			'code' => 0
			);
			
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			if ($poi->poi_id) {
				$exists = Better_DAO_User_PoiFavorites::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'poi_id' => $poiId
					));

				if ($exists) {
					--$poi->favorites;
					$poi->update(array(
						'favorites' => $poi->favorites
						));
				}
				
				$code = $codes['SUCCESS'];				
			} else {
				$code = $codes['INVALIDE_POI'];
			}	
		} else {
			$code = $codes['INVALID_POI'];
		}
		
		$result['code'] = $code;

		return $result;
	}
	
	/**
	 * 某人对所有POI收藏
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=BETTER_PAGE_SIZE)
	{
		$results = Better_DAO_User_PoiFavorites::getInstance($this->uid)->getFavoritesWithDetail($page, $count);

		return $results;
	}
}