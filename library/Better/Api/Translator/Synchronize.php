<?php

/**
 * 游戏同步数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Synchronize extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		
		if (isset($data['session_id'])) {
			$result['session_id'] = $data['session_id'];
			$result['validity'] = $data['valid'] ? 'true' : 'false';
		}
		
		return $result;
	}
}