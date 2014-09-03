<?php

/**
 * 个人宝物数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Mytreasure extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];

		if (isset($data['id'])) {
			$result['name'] = $data['name'];
			$result['id'] = $data['id'];
			$result['description'] = $data['description'];
			$result['image_url'] = $data['image_url'];
			$result['create_at'] = parent::time((float)$data['dateline']);
			$result['partner_with'] = array(
				'id' => $data['coplayer']['uid'],
				'name' => $data['coplayer']['nickname'],
				);
			
			$result['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
				'data' => $data['poi'],
				'userInfo' => &$userInfo,
				));
		}		
		
		return $result;
	}
}