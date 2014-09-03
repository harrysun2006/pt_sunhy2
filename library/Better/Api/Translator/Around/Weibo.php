<?php

/**
 * 附近探索  微博
 * 
 * @package Better.Api.Translator.User
 * @author yangl
 *
 */
class Better_Api_Translator_Around_Weibo extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = $params['data'];
		
		$result = array();
		$result['id'] = $data['id'];
		$result['content'] = $data['content'];
		$result['source'] = $data['source'];
		$result['icon'] = $data['icon'];
		$result['img'] = $data['img_url'];
		$result['create_at'] = parent::time($data['dateline']);
		
		$result['action'] = Better_Api_Translator::getInstance('around_action')->translate(array(
								'data'=>$data
							));
		
		return $result;
	}
}