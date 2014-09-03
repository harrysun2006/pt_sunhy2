<?php

/**
 * 宝物流转历史数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Hunted extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		
		if (isset($data['treasure_id'])) {
			$uid = $data['uid'];
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => Better_User::getInstance($uid)->getUserInfo(),
				'userInfo' => &$userInfo,
				));
			$result['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
				'data' => $data['poi_info'],
				'userInfo' => &$userInfo,
				));
			$result['create_at'] = parent::time($data['dateline']);
		}
		
		return $result;
	}
}