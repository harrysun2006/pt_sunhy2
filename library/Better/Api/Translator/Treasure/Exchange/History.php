<?php

/**
 * 宝物兑换记录
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Treasure_Exchange_History extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];

		if (isset($data['data']['id'])) {
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$data['user'],
				'userInfo' => &$userInfo
				));
			$treasureData = $data['data'];
			$treasureData['id'] = $data['data']['treasure_id'];
			$result['treasure'] = Better_Api_Translator::getInstance('treasure')->translate(array(
				'data' => $treasureData
				));
			$result['exchange_time'] = parent::time($data['data']['dateline']);
		}
		
		return $result;
	}
}