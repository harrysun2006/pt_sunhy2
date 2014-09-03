<?php

/**
 * 机器人通知数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Robot_Notify extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		
		if ($data['im'] && $data['type']) {
			$result['content'] = $data['text'];
		}
		
		return $result;
	}
}