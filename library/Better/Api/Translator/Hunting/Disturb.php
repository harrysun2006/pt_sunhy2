<?php

/**
 * 寻宝免打扰设置
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Hunting_Disturb extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];

		$result['server_time'] = parent::time(time());
		
		if (isset($data['dateline']) && $data['dateline']) {
			$result['start_time'] = parent::time($data['dateline']);
			$result['end_time'] = parent::time($data['expire_time']);
		} else {
			$result['start_time'] = '';
			$result['end_time'] = '';
		}
		
		return $result;
	}
}