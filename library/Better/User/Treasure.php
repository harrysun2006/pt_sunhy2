<?php

/**
 * 用户宝物
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Treasure extends Better_User_Base
{
	protected static $instance = array();
	
	public $treasures = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 兑换宝物
	 * 
	 * @param integer $id
	 */
	public function exchange($id)
	{
		$result = false;
		
		$userInfo = $this->user->getUserInfo();
		$treasure = Better_Treasure::getInstance($id);
		$treasureInfo = $treasure->getInfo();
		
		if ($treasureInfo['id'] && $treasure->canExchange()) {
			
			$flag = Better_DAO_User_Treasure::getInstance($this->uid)->deleteByCond(array(
				'treasure_id' => $id,
				'uid' => $this->uid,
				));
			if ($flag) {
				$this->user->updateUser(array(
					'treasures' => $userInfo['treasures'] - 1
					));
				Better_DAO_Treasure_Exchange::getInstance()->reduceRemain($id);
				
				Better_DAO_Treasure_ExchangeRequest::getInstance()->insert(array(
					'uid' => $this->uid,
					'treasure_id' => $id,
					'dateline' => time(),
					'status' => 'not_response',
					));
							
				Better_Hook::factory(array(
					'Badge'
				))->invoke('ExchangeTreasure', array(
					'treasure_id' => $id,
					'uid' => $this->uid,
					));					
			}
		}
		
		$result = true;	//	临时设置
		return $result;
	}
	
	/**
	 * 记录用户宝物日志
	 * 
	 * @param array $params
	 * @return null
	 */
	public function log(array $params)
	{
		Better_DAO_User_Treasure_Log::getInstance($this->uid)->insert(array(
			'uid' => $this->uid,
			'treasure_id' => $params['treasure_id'],
			'dateline' => time(),
			'category' => $params['category'],
			'poi_id' => (int)$params['poi_id'],
			'co_uid' => $params['co_uid'],
			));
	}
	
	/**
	 * 宝物流转历史
	 * 
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function logs($tid, $page=1, $count=BETTER_PAGE_SIZE)
	{
		return Better_DAO_User_Treasure_Log::getTreasureLogs($tid, $page, $count);
	}
	
	/**
	 * 我的宝物兑换历史
	 * 
	 * @return array
	 */
	public function getMyExchangeHistory($page=1, $count=BETTER_PAGE_SIZE)
	{
		return Better_DAO_User_Treasure::getMyExchangeHistory($this->uid, $page, $count);
	}
	
	/**
	 * 获取当前用户可供兑换的宝物
	 * 
	 * @return array
	 */
	public function &getCanExchangeTreasures()
	{
		$this->getMyTreasures();
		
		return Better_DAO_User_Treasure::getCanExchangeTreasures(array_keys($this->treasures));
	}
	
	/**
	 * 获取当前用户所有的宝物
	 * 
	 * @return array
	 */
	public function &getMyTreasures($refresh=true)
	{
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		$treasures = array();

		if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {

			if ($refresh==true || count($this->treasures)==0) {

				$tmp = Better_DAO_User_Treasure::getInstance($this->uid)->getAll(array(
					'uid' => $this->uid,
					'order' => 'dateline ASC',
					));
				$this->treasures = array();

				if (count($tmp)>0) {
					$treasures = Better_Treasure::getAllTreasures(array(
						'active' => 1
						));

					foreach ($tmp as $row) {
						$data = $treasures[$row['treasure_id']];
						$data['dateline'] = $row['dateline'];
						$data['poi'] = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
						$data['coplayer'] = $row['coplayer_uid'] ? Better_User::getInstance($row['coplayer_uid'])->getUserInfo() : array();
						$data['name'] = Better_Language::loadDbKey('name', $data, $this->user->getUserLanguage());
						$data['description'] = Better_Language::loadDbKey('description', $data, $this->user->getUserLanguage());
		
						$this->treasures[$row['treasure_id']] = $data;
					}
				}
				
				$treasures = &$this->treasures;
			}
		}

		return $treasures;
	}
	
	/**
	 * 捡起宝物/丢弃宝物
	 * 
	 * @param integer $tid
	 * @param integer $throwTid
	 * @return bool
	 */
	public function pickup(array $params)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'ALREADY_HAVE' => -1,
			'TOO_MANY' => -2,
			);
		$code = $codes['FAILED'];
		
		$tid = (int)$params['treasure_id'];
		$throwTid = (int)$params['throw_tid'];
		$poiId = (int)$params['poi_id'];
		$coUid = (int)$params['co_uid'];
		
		$this->getMyTreasures();
		$userInfo = $this->user->getUserInfo();
		
		if (count($this->treasures)<3) {
			 if ($throwTid>0) {
			 	$tmp = array_diff(array_keys($this->treasures), (array)$throwTid);
		 		if ($this->_throw(array(
		 			'treasure_id' => $throwTid,
		 			'poi_id' => $poiId,
		 			'co_uid' => $coUid
		 			)) && $this->_add(array(
		 			'treasure_id' => $tid,
		 			'poi_id' => $poiId,
		 			))) {
		 				
		 			$this->log(array(
		 				'treasure_id' => $tid,
		 				'category' => 'pickup',
		 				'poi_id' => $poiId,
		 				'co_uid' => $coUid,
		 				));
		 			$code = $codes['SUCCESS'];	
		 		}
			 } else {
			 	if (array_key_exists($tid, $this->treasures)) {
			 		$code = $codes['ALREADY_HAVE'];
			 	} else {
				 	if ($this->_add(array(
				 		'treasure_id' => $tid,
				 		'poi_id' => $poiId,
				 		'co_uid' => $coUid,
				 		))) {
				 			
				 		$this->user->updateUser(array(
				 			'treasures' => $userInfo['treasures']+1,
				 			));
				 		
				 		$this->log(array(
				 			'treasure_id' => $tid,
				 			'category' => 'pickup',
				 			'poi_id' => $poiId,
				 			'co_uid' => $coUid,
				 			));
				 		
				 		$code = $codes['SUCCESS'];
				 	}
			 	}
			 }
		} else {
			$code = $codes['TOO_MANY'];
		}

		return array(
			'codes' => &$codes,
			'code' => $code
			);
	}
	
	/**
	 * 丢掉宝物
	 * 
	 * @param array $params
	 */
	public function chuck(array $params)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'HAVNT' => -1,
			);
		$code = $codes['FAILED'];
		
		$throwTid = (int)$params['treasure_id'];
		$poiId = (int)$params['poi_id'];
		$coUid = (int)$params['co_uid'];
		
		$this->getMyTreasures(false);
		$userInfo = $this->user->getUserInfo();
		
		if (array_key_exists($throwTid, $this->treasures)) {
			if ($this->_throw(array(
				'treasure_id' => $throwTid,
				'poi_id' => $poiId,
				'co_uid' => $coUid
	 			))) {
	 				
				$this->user->updateUser(array(
				 	'treasures' => $userInfo['treasures']-1,
				 	));	 				
				 				
	 			$this->log(array(
	 				'treasure_id' => $throwTid,
	 				'category' => 'throw',
	 				'poi_id' => $poiId,
	 				'co_uid' => $coUid,
	 				));
	 			$code = $codes['SUCCESS'];	
	 		}		
		} else {
			$code = $codes['HAVNT'];
		}

		return array(
			'codes' => &$codes,
			'code' => $code,
			);
	}
	
	/**
	 * 添加宝物
	 * 
	 * @param array $params
	 */
	private function _add(array $params)
	{
		$addResult = Better_DAO_User_Treasure::getInstance($this->uid)->insert(array(
			'uid' => $this->uid,
			'treasure_id' => $params['treasure_id'],
			'dateline' => time(),
			'poi_id' => $params['poi_id'],
			'coplayer_uid' => $params['co_uid'],
			));
			
		Better_Hook::factory(array(
			'Badge'
		))->invoke('PickupTreasure', array(
			'treasure_id' => $params['treasure_id'],
			'poi_id' => $params['poi_id'],
			'uid' => $this->uid,
			));

		return $addResult;
	}
	
	/**
	 * 丢掉宝物
	 * 
	 * @param array $params
	 */
	private function _throw(array $params)
	{
		$tid = (int)$params['treasure_id'];
		$poiId = (int)$params['poi_id'];
		$coUid = (int)$params['co_uid'];
		
		$this->log(array(
			'category' => 'throw',
			'poi_id' => $poiId,
			'co_uid' => $coUid,
			'treasure_id' => $tid,
			));

		return Better_DAO_User_Treasure::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'treasure_id' => $tid,
			));
	}
}