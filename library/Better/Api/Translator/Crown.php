<?php

/**
 * 皇冠/Major数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Crown extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		$ver = $params['ver']=='2' ? '2' : '1';
		
		if (isset($data['poi_id'])) {
			$result['create_at'] = parent::time($data['major_change_time']);
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$userInfo,
				));
				
			if ($ver=='2') {
				$result['poi_concise'] = Better_Api_Translator::getInstance('poi_concise')->translate(array(
					'data' => &$data,
					'userInfo' => &$userInfo
					));
			} else {
				$result['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
					'data' => &$data,
					'userInfo' => &$userInfo,
					));
			}
		}
		
		return $result;
	}
}