<?php

/**
 * 市场部中秋活动
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Market_Midautumn extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];

		$result = array();

		if (isset($data['uid'])) {
			$result['targeturl'] = $data['avatar_small'];
			$result['filename'] = $data['avatar_normal'];
			$result['username'] = $data['username'];
			$result['nickname'] = $data['nickname'];
		}
		
		return $result;
	}
}