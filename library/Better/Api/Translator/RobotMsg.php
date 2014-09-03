<?php

/**
 * 
 * 转换机器人消息
 * 
 * @package Better.Api.Translate
 * @author leip
 *
 */
class Better_Api_Translator_RobotMsg extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array(
			'result' => 'false',
			'error' => '',
			'request_time' => Better_Functions::date(Better_Api_Translator::$timeFormat, time()),
			'command' => 'unknown',
			'msg' => '指令执行成功',
			'bid' => '',
			);
		foreach ($result as $key=>$value) {
			if (isset($params[$key])) {
				$result[$key] = $params[$key];
			}
		}
			
		return $result;		
	}
}