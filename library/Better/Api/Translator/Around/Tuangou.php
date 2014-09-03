<?php

/**
 * 附近探索  团购
 * 
 * @package Better.Api.Translator.User
 * @author yangl
 *
 */
class Better_Api_Translator_Around_Tuangou extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = $params['data'];
		
		$result = array();
		$result['id'] = $data['id'];
		$result['content'] = $data['content'];
		$result['s_time'] = parent::time($data['begintm']);
		$result['e_time'] = parent::time($data['endtm']);
		$result['source'] = $data['source'];
		$result['icon'] = $data['icon'];
		$result['img'] = $data['img_url'];
		$result['detail'] = $data['detail_url'];
		
		$result['action'] = Better_Api_Translator::getInstance('around_action')->translate(array(
								'data'=>$data
							));
		
		$result['poi_concise'] = Better_Api_Translator::getInstance('poi_concise')->translate(array(
			'data'=>$data
		));
		
		return $result;
	}
}