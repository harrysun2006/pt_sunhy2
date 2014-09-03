<?php

/**
 * 机器人消息数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Robot_Msg extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		
		if (isset($data['result'])) {
			$result['result'] = $data['result'];
			$result['error'] = $data['error'];
			$result['requet_time'] = self::time(time());
			$result['command'] = $data['command'] ? $data['common'] : 'unknown';
			$result['bid'] = $data['bid'];
			$result['msg'] = $data['msg'];
		}	
			
		return $result;
	}
}