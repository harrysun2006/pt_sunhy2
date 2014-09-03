<?php

/**
 * 宝物兑换数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Treasure_Exchange extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		/*
		$result = array(
			'treasure' => '',
			'total_count' => '',
			'exchanged_count' => '',
			'validate_from' => '',
			'validate_to' => '',
			);*/
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		
		if (isset($data['id'])) {
			$result['treasure'] = Better_Api_Translator::getInstance('treasure')->translate(array(
				'data' => &$data,
				'userInfo' => &$userInfo,
				));
			$result['total_count'] = $data['total'];
			$result['exchanged_count'] = $data['total'] - $data['remain'];
			$result['validate_from'] = self::time($data['start_time']);
			$result['validate_to'] = self::time($data['end_time']);
		}
		
		return $result;
	}
}